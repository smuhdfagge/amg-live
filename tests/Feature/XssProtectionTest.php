<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use App\Models\Post;
use App\Models\Project;
use App\Models\Resource;
use App\Models\Subscriber;
use App\Models\User;
use App\Models\VolunteerApplication;
use App\Support\WordPressImport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * Cross-site scripting (XSS) checks. Harmful markup is typed into every place
 * a visitor or an admin can enter text, and then every page that shows that
 * text is checked: the markup must come out as harmless visible text.
 */
class XssProtectionTest extends TestCase
{
    use RefreshDatabase;

    private const SCRIPT = '<script>alert("xss")</script>';

    private const IMG = '"><img src=x onerror=alert(1)>';

    private const SVG = "<svg onload=alert('x')>";

    private function payload(string $label): string
    {
        return $label.' '.self::SCRIPT.self::IMG.self::SVG;
    }

    /** The page must not contain any of the payloads in working form. */
    private function assertSafe(TestResponse $response): void
    {
        $html = $response->getContent();
        $this->assertStringNotContainsString('<script>alert', $html);
        $this->assertStringNotContainsString('<img src=x onerror', $html);
        $this->assertStringNotContainsString('<svg onload', $html);
        $this->assertStringNotContainsString('href="javascript:', strtolower($html));
        $this->assertStringNotContainsString('src="javascript:', strtolower($html));
    }

    private function admin(): User
    {
        return User::create(['name' => 'Admin', 'email' => 'admin@amg.test', 'password' => 'secret-password-1', 'role' => 'admin']);
    }

    public function test_public_form_submissions_are_shown_safely_to_admins(): void
    {
        $this->post('/contact', [
            'type' => 'general', 'name' => $this->payload('Name'), 'email' => 'visitor@example.org',
            'phone' => self::IMG, 'organisation' => $this->payload('Org'), 'subject' => $this->payload('Subject'),
            'message' => $this->payload('Message'),
        ])->assertSessionHas('status');

        $this->post('/get-involved/volunteer', [
            'name' => $this->payload('Vol'), 'email' => 'vol@example.org', 'phone' => '0803 000 0000',
            'location' => $this->payload('LGA'), 'interest' => 'field', 'skills' => $this->payload('Skills'),
            'message' => $this->payload('Note'),
        ])->assertSessionHas('volunteer_status');

        $this->assertSame(1, ContactMessage::count());
        $this->assertSame(1, VolunteerApplication::count());
        $admin = $this->admin();

        foreach (['/admin', '/admin/messages', '/admin/messages/'.ContactMessage::first()->id,
            '/admin/volunteers', '/admin/volunteers/'.VolunteerApplication::first()->id] as $url) {
            $response = $this->actingAs($admin)->get($url)->assertOk();
            $this->assertSafe($response);
        }

        // It is shown as text, not lost.
        $this->actingAs($admin)->get('/admin/messages/'.ContactMessage::first()->id)
            ->assertSee('Message '.self::SCRIPT, true);
    }

    public function test_admin_content_is_shown_safely_on_public_pages(): void
    {
        Storage::fake('public');
        $admin = $this->admin();

        $this->actingAs($admin)->post('/admin/posts', [
            'title' => $this->payload('Title'), 'category' => 'News', 'excerpt' => $this->payload('Excerpt'),
            'body' => $this->payload('Para')."\n\n## ".self::SCRIPT."\n\n- ".self::IMG,
            'source' => $this->payload('Source'), 'published_at' => now()->subMinute()->format('Y-m-d\TH:i'),
        ])->assertRedirect();

        $this->actingAs($admin)->post('/admin/projects', [
            'title' => $this->payload('Programme'), 'short_title' => self::SVG, 'summary' => $this->payload('Summary'),
            'body' => $this->payload('Body'), 'partner' => $this->payload('Partner'), 'location' => self::IMG,
            'status' => 'ongoing', 'sort_order' => 1, 'period' => '2026', 'reach_figure' => self::SCRIPT, 'reach_label' => self::IMG,
        ]);

        Resource::create(['type' => 'video', 'title' => $this->payload('Video'), 'description' => $this->payload('Desc'),
            'language' => 'Hausa', 'duration' => self::IMG, 'media_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'published_at' => now()]);

        $post = Post::first();
        $this->assertNotNull($post);
        $urls = ['/', '/news', '/news/'.$post->slug, '/resources', '/programmes', '/partners', '/sitemap.xml',
            '/admin/posts', '/admin/posts/'.$post->slug.'/edit', '/admin/resources', '/admin/projects'];
        $project = Project::first();
        $this->assertNotNull($project, 'The programme with the payload was saved.');
        $urls[] = '/programmes/'.$project->slug;
        $urls[] = '/admin/projects/'.$project->slug.'/edit';
        foreach ($urls as $url) {
            $response = $this->actingAs($admin)->get($url);
            $this->assertContains($response->status(), [200], $url);
            $this->assertSafe($response);
        }
        $this->get('/news/'.$post->slug)->assertSee(self::SCRIPT, true);   // visible as text
    }

    public function test_javascript_links_are_refused_and_never_rendered(): void
    {
        Storage::fake('public');
        $admin = $this->admin();
        $bad = ['javascript:alert(1)', 'JaVaScRiPt:alert(1)', " javascript:alert(1)", 'data:text/html,<script>alert(1)</script>', 'vbscript:x', "java\tscript:alert(1)"];

        foreach ($bad as $url) {
            $this->actingAs($admin)->from('/admin/posts/create')->post('/admin/posts', [
                'title' => 'Linked story', 'category' => 'News', 'external_url' => $url,
            ])->assertSessionHasErrors('external_url');

            $this->actingAs($admin)->from('/admin/resources/create')->post('/admin/resources', [
                'type' => 'radio', 'title' => 'Show', 'media_url' => $url,
            ])->assertSessionHasErrors('media_url');
        }
        $this->assertSame(0, Post::count() + Resource::count());

        // Even if a bad link got into the database some other way, it is not used.
        $post = Post::create(['slug' => 'sneaky', 'title' => 'Sneaky', 'category' => 'News', 'external_url' => 'javascript:alert(1)', 'published_at' => now()]);
        Resource::create(['type' => 'radio', 'title' => 'Sneaky show', 'media_url' => 'javascript:alert(1)', 'published_at' => now()]);
        $this->assertSafe($this->get('/news'));
        $this->assertSafe($this->get('/'));
        $this->assertSafe($this->get('/resources'));
    }

    public function test_page_data_for_search_engines_cannot_break_out_of_its_script_tag(): void
    {
        config(['amg.name' => 'AMG </script><script>alert(1)</script>']);

        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringNotContainsString('</script><script>alert(1)', $html);
        $this->assertStringContainsString('</script>', $html);
    }

    public function test_addresses_and_filters_are_not_echoed_back_raw(): void
    {
        $this->assertSafe($this->get('/resources?type='.urlencode(self::SCRIPT)));
        $this->assertSafe($this->get('/'.urlencode(self::SCRIPT))->assertNotFound());
        $this->assertSafe($this->get('/news?page='.urlencode(self::IMG)));
        $this->assertSafe($this->get('/contact?type='.urlencode(self::IMG)));
    }

    public function test_imported_news_cannot_carry_scripts(): void
    {
        $text = WordPressImport::htmlToText('<p>Hello</p><script>alert(1)</script><p onclick="alert(1)">Click <a href="javascript:alert(1)">me</a></p><iframe src="javascript:alert(1)"></iframe>');

        $this->assertSame("Hello\n\nClick me", $text);
    }

    public function test_every_page_sends_a_strict_content_security_policy(): void
    {
        foreach (['/', '/news', '/contact', '/admin/login'] as $url) {
            $response = $this->get($url);
            $csp = (string) $response->headers->get('Content-Security-Policy');
            $this->assertStringContainsString("script-src 'self'", $csp, $url);
            $this->assertStringNotContainsString('unsafe-inline\' ', str_replace("style-src 'self' 'unsafe-inline'", '', $csp), $url);
            $this->assertStringNotContainsString('unsafe-eval', $csp, $url);
            $this->assertStringContainsString("object-src 'none'", $csp);
            $this->assertStringContainsString("frame-ancestors 'self'", $csp);
            $response->assertHeader('X-Content-Type-Options', 'nosniff');
            $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
            $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        }
    }

    public function test_pages_have_no_inline_scripts_so_the_policy_holds(): void
    {
        Post::create(['slug' => 'a', 'title' => 'A', 'category' => 'News', 'body' => 'Text', 'published_at' => now()]);
        foreach (['/', '/about', '/our-work', '/news', '/news/a', '/resources', '/get-involved', '/support-us', '/contact', '/admin/login'] as $url) {
            $html = $this->get($url)->getContent();
            preg_match_all('/<script\b([^>]*)>(.*?)<\/script>/is', $html, $m, PREG_SET_ORDER);
            foreach ($m as [$all, $attrs, $body]) {
                $isData = str_contains($attrs, 'application/ld+json');
                $this->assertTrue($isData || (str_contains($attrs, 'src=') && trim($body) === ''), "Inline script on {$url}: ".substr($all, 0, 80));
            }
            $this->assertDoesNotMatchRegularExpression('/\son[a-z]+\s*=\s*["\']/i', $html, "Inline event handler on {$url}");
        }
    }

    public function test_a_web_page_disguised_as_audio_is_refused(): void
    {
        Storage::fake('public');
        $admin = $this->admin();

        foreach (['<html><body><script>alert(1)</script></body></html>', "<?php system('id'); ?>", '<svg xmlns="http://www.w3.org/2000/svg" onload="alert(1)"/>'] as $content) {
            $this->actingAs($admin)->from('/admin/resources/create')->post('/admin/resources', [
                'type' => 'radio', 'title' => 'Fake',
                'media' => UploadedFile::fake()->createWithContent('song.mp3', $content),
            ])->assertSessionHasErrors('media');
        }

        $this->actingAs($admin)->post('/admin/posts', [
            'title' => 'Svg cover', 'category' => 'News', 'body' => 'x',
            'cover' => UploadedFile::fake()->createWithContent('cover.svg', '<svg xmlns="http://www.w3.org/2000/svg" onload="alert(1)"/>'),
        ])->assertSessionHasErrors('cover');

        $this->assertSame(0, Resource::count());
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_uploaded_files_are_served_so_they_cannot_run(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('resources/radio/old.mp3', '<html><script>alert(1)</script></html>');
        Storage::disk('public')->put('news/page.html', '<script>alert(1)</script>');
        Storage::disk('public')->put('news/photo.jpg', 'jpg');

        $mp3 = $this->get('/storage/resources/radio/old.mp3')->assertOk();
        $this->assertSame('audio/mpeg', $mp3->headers->get('Content-Type'));
        $this->assertStringContainsString('sandbox', $mp3->headers->get('Content-Security-Policy'));
        $mp3->assertHeader('X-Content-Type-Options', 'nosniff');

        $page = $this->get('/storage/news/page.html')->assertOk();
        $this->assertSame('application/octet-stream', $page->headers->get('Content-Type'));
        $this->assertStringStartsWith('attachment', $page->headers->get('Content-Disposition'));

        $this->assertSame('image/jpeg', $this->get('/storage/news/photo.jpg')->headers->get('Content-Type'));
    }

    public function test_the_uploads_folder_blocks_scripts_when_apache_serves_it(): void
    {
        $rules = file_get_contents(storage_path('app/public/.htaccess'));
        $this->assertStringContainsString('Require all denied', $rules);
        $this->assertMatchesRegularExpression('/php.*html.*svg.*js/s', $rules);
        $this->assertStringContainsString('sandbox', $rules);
    }

    public function test_spreadsheet_downloads_cannot_run_formulas(): void
    {
        VolunteerApplication::create(['name' => '=HYPERLINK("http://evil.example","Click")', 'email' => 'v@example.org', 'phone' => '+2348030000000',
            'interest' => 'field', 'skills' => '@SUM(1+1)', 'message' => '-2+3']);
        Subscriber::create(['email' => 'a@example.org']);

        $csv = $this->actingAs($this->admin())->get('/admin/volunteers/export')->streamedContent();
        $this->assertStringContainsString("\"'=HYPERLINK(", $csv);
        $this->assertStringContainsString("'@SUM(1+1)", $csv);
        $this->assertStringContainsString("'-2+3", $csv);
        $this->assertStringContainsString("'+2348030000000", $csv);
        $this->assertStringNotContainsString(',=HYPERLINK', $csv);
    }

    public function test_session_cookie_cannot_be_read_by_scripts(): void
    {
        $this->assertTrue(config('session.http_only'));
        $this->assertSame('lax', config('session.same_site'));
    }

    public function test_staging_copies_are_hidden_from_search_engines(): void
    {
        $this->app['env'] = 'staging';
        $this->get('/')->assertHeader('X-Robots-Tag', 'noindex, nofollow');

        $this->app['env'] = 'production';
        $this->get('/')->assertHeaderMissing('X-Robots-Tag');
    }
}
