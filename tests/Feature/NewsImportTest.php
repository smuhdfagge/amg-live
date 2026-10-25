<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use App\Support\WordPressImport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NewsImportTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create(['name' => 'Admin', 'email' => 'admin@amg.test', 'password' => 'secret-password']);
    }

    private function jpeg(int $w = 40, int $h = 30): string
    {
        $img = imagecreatetruecolor($w, $h);
        ob_start();
        imagejpeg($img);

        return ob_get_clean();
    }

    private function restPost(int $id, string $slug, string $title, array $extra = []): array
    {
        return array_replace_recursive([
            'id' => $id,
            'slug' => $slug,
            'date_gmt' => '2025-06-0'.($id % 9 + 1).'T10:00:00',
            'link' => "https://aminumagashigarbafoundation.org/{$slug}/",
            'title' => ['rendered' => $title],
            'content' => ['rendered' => "<p>Story text for {$title}.</p>"],
            'excerpt' => ['rendered' => "<p>Story text for {$title}. [&hellip;]</p>"],
            '_embedded' => [
                'wp:featuredmedia' => [['source_url' => "https://aminumagashigarbafoundation.org/wp-content/uploads/{$slug}.jpg"]],
                'wp:term' => [[['taxonomy' => 'category', 'name' => 'News']]],
            ],
        ], $extra);
    }

    private function fakeSite(array $pages): void
    {
        $total = array_sum(array_map('count', $pages));
        Http::fake(function (HttpRequest $request) use ($pages, $total) {
            if (str_contains($request->url(), '/wp-json/wp/v2/posts')) {
                $page = (int) ($request->data()['page'] ?? 1);
                if (! isset($pages[$page - 1])) {
                    return Http::response(['code' => 'rest_post_invalid_page_number'], 400);
                }

                return Http::response($pages[$page - 1], 200, ['X-WP-Total' => $total, 'X-WP-TotalPages' => count($pages)]);
            }
            if (str_contains($request->url(), '/wp-content/uploads/')) {
                return Http::response($this->jpeg(), 200, ['Content-Type' => 'image/jpeg']);
            }

            return Http::response('Not found', 404);
        });
    }

    public function test_html_becomes_the_sites_simple_text_format(): void
    {
        $html = '<!-- wp:paragraph --><p>First <a href="#">para</a>&nbsp;here.</p><h2>Heading</h2>'
            .'<ul><li>One</li><li>Two</li></ul><figure><img src="x.jpg"><figcaption>Caption</figcaption></figure>'
            .'[caption id="1"]<p>Line one<br>Line two</p>[/caption]<script>alert(1)</script><p>Share this:</p>';

        $this->assertSame(
            "First para here.\n\n## Heading\n\n- One\n- Two\n\nLine one\n\nLine two",
            WordPressImport::htmlToText($html)
        );
    }

    public function test_photos_are_found_once_at_their_biggest_size(): void
    {
        $html = '<img src="https://s.org/a-300x200.jpg" srcset="https://s.org/a-300x200.jpg 300w, https://s.org/a-1024x683.jpg 1024w">'
            .'<img src="https://s.org/a.jpg"><img src="https://s.org/b.png"><img src="https://s.org/wp-includes/images/smilies/emoji.png">';

        $this->assertSame(['https://s.org/a-1024x683.jpg', 'https://s.org/b.png'], WordPressImport::imagesIn($html));
    }

    public function test_import_from_the_site_address_in_batches(): void
    {
        Storage::fake('public');
        $page1 = [];
        for ($i = 1; $i <= 10; $i++) {
            $page1[] = $this->restPost($i, "story-{$i}", "Story {$i}");
        }
        $page2 = [$this->restPost(11, 'mental-health-walk', 'Mental health walk', [
            'content' => ['rendered' => '<p>We walked.</p><img src="https://aminumagashigarbafoundation.org/wp-content/uploads/walk-2.jpg">'],
            'excerpt' => ['rendered' => '<p>A hand-written summary.</p>'],
            '_embedded' => ['wp:term' => [[['taxonomy' => 'category', 'name' => 'Press releases']]]],
        ])];
        $this->fakeSite([$page1, $page2]);
        $admin = $this->admin();

        $this->actingAs($admin)->post('/admin/news-import', [
            'source' => 'site', 'site_url' => 'aminumagashigarbafoundation.org/news/', 'photos' => '1',
        ])->assertRedirect(route('admin.news-import.show'));

        $this->actingAs($admin)->get('/admin/news-import')->assertSee('Importing… 0 of 11');
        $this->actingAs($admin)->post('/admin/news-import/step')->assertRedirect();
        $this->assertSame(10, Post::count());
        $this->actingAs($admin)->post('/admin/news-import/step');
        $this->actingAs($admin)->get('/admin/news-import')->assertSee('Import finished')->assertSee('11 stories added');

        $walk = Post::firstWhere('slug', 'mental-health-walk');
        $this->assertSame('We walked.', $walk->body);
        $this->assertSame('A hand-written summary.', $walk->excerpt);
        $this->assertSame('In the press', $walk->category);
        $this->assertSame(11, $walk->legacy_id);
        $this->assertSame('mental-health-walk', $walk->legacy_path);
        Storage::disk('public')->assertExists($walk->cover_image);
        $this->assertCount(1, $walk->gallery);
        $this->assertNull(Post::firstWhere('slug', 'story-1')->excerpt, 'Automatic WordPress summaries are dropped.');

        // Old links open the new pages.
        $this->get('/mental-health-walk/')->assertRedirect(route('news.show', $walk));
        $this->get('/?p=11')->assertRedirect(route('news.show', $walk));
        $this->get('/news/mental-health-walk')->assertOk()->assertSee('We walked.')->assertSee('Photos');

        // Running it again adds nothing.
        $this->actingAs($admin)->post('/admin/news-import/reset');
        $this->actingAs($admin)->post('/admin/news-import', ['source' => 'site', 'site_url' => 'https://aminumagashigarbafoundation.org']);
        $this->actingAs($admin)->post('/admin/news-import/step');
        $this->actingAs($admin)->post('/admin/news-import/step');
        $this->actingAs($admin)->get('/admin/news-import')->assertSee('11 already here and skipped', false);
        $this->assertSame(11, Post::count());
    }

    public function test_import_from_a_wordpress_export_file(): void
    {
        Storage::fake('public');
        Storage::fake('local');
        $this->fakeSite([]);
        $admin = $this->admin();

        $file = new UploadedFile(base_path('tests/fixtures/wordpress-export.xml'), 'export.xml', 'text/xml', null, true);
        $this->actingAs($admin)->post('/admin/news-import', ['source' => 'file', 'file' => $file, 'photos' => '1'])
            ->assertRedirect(route('admin.news-import.show'));
        $this->actingAs($admin)->post('/admin/news-import/step');
        $this->actingAs($admin)->get('/admin/news-import')->assertSee('2 stories added');

        $this->assertSame(2, Post::count(), 'Drafts and pages stay behind.');
        $testing = Post::firstWhere('legacy_id', 101);
        $this->assertSame("The Foundation held a free genotype testing day.\n\n## What happened\n\n- 420 people tested\n- Counselling for couples\n\nThanks to our partners & volunteers.", $testing->body);
        $this->assertSame('Announcement', $testing->category);
        $this->assertSame('2024-03-10 09:00', $testing->published_at->utc()->format('Y-m-d H:i'));
        $this->assertSame('2024/03/free-genotype-testing-in-gwale', $testing->legacy_path);
        Storage::disk('public')->assertExists($testing->cover_image);
        $this->assertCount(1, $testing->gallery);
        Http::assertSent(fn ($r) => str_ends_with($r->url(), '/2024/03/cover.jpg'));
        Http::assertSent(fn ($r) => str_ends_with($r->url(), '/2024/03/day.jpg'), 'Tries the original before the resized copy.');

        $press = Post::firstWhere('legacy_id', 104);
        $this->assertSame('In the press', $press->category);
        $this->assertSame('A national paper covered our mental health work.', $press->excerpt);

        $this->get('/2024/03/free-genotype-testing-in-gwale/')->assertRedirect(route('news.show', $testing));
        $this->assertSame([], Storage::disk('local')->allFiles('imports'), 'The uploaded file is removed afterwards.');
    }

    public function test_clear_messages_when_the_site_cannot_be_read(): void
    {
        Http::fake(['*' => Http::response('Forbidden', 403)]);
        $admin = $this->admin();

        $this->actingAs($admin)->from('/admin/news-import')
            ->post('/admin/news-import', ['source' => 'site', 'site_url' => 'https://blocked.example.org'])
            ->assertRedirect('/admin/news-import')
            ->assertSessionHasErrors('site_url');

        $bad = UploadedFile::fake()->createWithContent('notes.xml', '<notes><a>1</a></notes>');
        $this->actingAs($admin)->from('/admin/news-import')
            ->post('/admin/news-import', ['source' => 'file', 'file' => $bad])
            ->assertSessionHasErrors('file');

        $this->assertSame(0, Post::count());
        $this->actingAs($admin)->get('/admin/news-import')->assertOk()->assertSee('Start import');
    }

    public function test_unknown_old_links_still_show_the_404_page(): void
    {
        $this->get('/2020/01/never-existed/')->assertNotFound();
    }

    public function test_command_line_import(): void
    {
        Storage::fake('public');
        $this->fakeSite([]);

        $this->artisan('news:import', ['--file' => base_path('tests/fixtures/wordpress-export.xml'), '--no-photos' => true])
            ->expectsOutputToContain('2 added')
            ->assertExitCode(0);
        $this->assertNull(Post::firstWhere('legacy_id', 101)->cover_image);
    }

    private int $oldSiteAttempts = 0;

    /** The old site cannot be reached; $internet says whether anything else can. */
    private function fakeUnreachableOldSite(bool $internet): void
    {
        Http::fake(function (HttpRequest $request) use ($internet) {
            $host = parse_url($request->url(), PHP_URL_HOST);
            if (in_array($host, ['old.example.org', 'aminumagashigarbafoundation.org'], true) || ! $internet) {
                $this->oldSiteAttempts++;
                throw new ConnectionException('cURL error 28: Connection timed out after 10013 milliseconds');
            }

            return $host === 'api.ipify.org' ? Http::response('203.0.113.7') : Http::response('', 204);
        });
    }

    public function test_explains_when_the_old_host_blocks_this_server(): void
    {
        $this->fakeUnreachableOldSite(internet: true);

        $this->actingAs($this->admin())->from('/admin/news-import')
            ->post('/admin/news-import', ['source' => 'site', 'site_url' => 'http://aminumagashigarbafoundation.org'])
            ->assertSessionHasErrors(['site_url' => 'This server can reach the internet, but could not connect to aminumagashigarbafoundation.org (even over IPv4). '
                ."The old website's host (or a firewall or security plugin on it) is blocking this server, whose address is 203.0.113.7. "
                .'Ask the old host to allow that address, then try again. Or use the export file option with a zip of the old photos, which needs no connection.']);
    }

    public function test_explains_when_this_server_cannot_get_out(): void
    {
        $this->fakeUnreachableOldSite(internet: false);

        $this->actingAs($this->admin())->from('/admin/news-import')
            ->post('/admin/news-import', ['source' => 'site', 'site_url' => 'aminumagashigarbafoundation.org'])
            ->assertSessionHasErrors('site_url');
        $this->assertStringContainsString('Your new hosting is blocking outgoing connections', session('errors')->first('site_url'));
    }

    public function test_export_file_with_a_photos_zip_needs_no_connection(): void
    {
        Storage::fake('public');
        Storage::fake('local');
        $this->fakeUnreachableOldSite(internet: false);

        $zipPath = tempnam(sys_get_temp_dir(), 'up').'.zip';
        $zip = new \ZipArchive;
        $zip->open($zipPath, \ZipArchive::CREATE);
        $zip->addFromString('uploads/2024/03/cover.jpg', $this->jpeg());
        $zip->addFromString('uploads/2024/03/day.jpg', $this->jpeg());
        $zip->addFromString('uploads/2024/03/notes.php', '<?php echo 1;');   // ignored
        $zip->addFromString('../../evil.jpg', $this->jpeg());                  // ignored
        $zip->close();

        $admin = $this->admin();
        $this->actingAs($admin)->post('/admin/news-import', [
            'source' => 'file',
            'file' => new UploadedFile(base_path('tests/fixtures/wordpress-export.xml'), 'export.xml', 'text/xml', null, true),
            'photos_zip' => new UploadedFile($zipPath, 'uploads.zip', 'application/zip', null, true),
            'photos' => '1',
        ])->assertRedirect(route('admin.news-import.show'));
        $this->actingAs($admin)->post('/admin/news-import/step');

        $this->actingAs($admin)->get('/admin/news-import')->assertSee('2 stories added')->assertSee('Using 2 photos from your zip');
        $post = Post::firstWhere('legacy_id', 101);
        Storage::disk('public')->assertExists($post->cover_image);
        $this->assertCount(1, $post->gallery);
        $this->assertSame(0, $this->oldSiteAttempts, 'Photos came from the zip.');
        $this->assertSame([], Storage::disk('local')->allFiles('imports'), 'Unpacked photos are removed afterwards.');
        $this->assertFileDoesNotExist(dirname(Storage::disk('local')->path('x')).'/evil.jpg');
    }

    public function test_unreachable_photos_do_not_hold_up_the_import(): void
    {
        Storage::fake('public');
        Storage::fake('local');
        $this->fakeUnreachableOldSite(internet: true);
        $admin = $this->admin();

        $this->actingAs($admin)->post('/admin/news-import', [
            'source' => 'file', 'photos' => '1',
            'file' => new UploadedFile(base_path('tests/fixtures/wordpress-export.xml'), 'export.xml', 'text/xml', null, true),
        ]);
        $this->actingAs($admin)->post('/admin/news-import/step');

        $this->assertSame(2, Post::count());
        $this->assertSame(2, $this->oldSiteAttempts, 'One try plus one over IPv4, then it gives up on the old host.');
        $this->actingAs($admin)->get('/admin/news-import')->assertSee('Photos could not be copied from old.example.org');
    }

    public function test_falls_back_to_ipv4_when_the_connection_hangs(): void
    {
        Storage::fake('public');
        $calls = ['v6' => 0, 'v4' => 0];
        Http::fake(function (HttpRequest $request, array $options) use (&$calls) {
            if (($options['force_ip_resolve'] ?? null) !== 'v4') {
                $calls['v6']++;
                throw new ConnectionException('cURL error 28: Connection timed out');
            }
            $calls['v4']++;
            if (str_contains($request->url(), '/wp-content/uploads/')) {
                return Http::response($this->jpeg(), 200);
            }

            return Http::response([$this->restPost(1, 'only-story', 'Only story')], 200, ['X-WP-Total' => 1, 'X-WP-TotalPages' => 1]);
        });
        $admin = $this->admin();

        $this->actingAs($admin)->post('/admin/news-import', ['source' => 'site', 'site_url' => 'aminumagashigarbafoundation.org', 'photos' => '1'])
            ->assertRedirect(route('admin.news-import.show'));
        $this->actingAs($admin)->post('/admin/news-import/step');

        $post = Post::firstWhere('slug', 'only-story');
        $this->assertNotNull($post);
        Storage::disk('public')->assertExists($post->cover_image);
        $this->assertSame(1, $calls['v6'], 'After the first hang, everything goes over IPv4.');
    }
}
