<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Project;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        Project::create([
            'slug' => 'sickle-cell-support', 'title' => 'Sickle cell support programme', 'partner' => 'Kano State Ministry of Health',
            'period' => '2026', 'start_year' => 2026, 'status' => 'ongoing', 'theme' => 'Sickle cell and blood disorders',
            'summary' => 'Helping patients reach care.', 'reach_figure' => '1,250', 'reach_label' => 'patients supported',
        ]);
        Post::create([
            'slug' => 'press-coverage', 'title' => 'Foundation featured in the press', 'category' => 'In the press',
            'external_url' => 'https://example.com/story', 'source' => 'Example News', 'published_at' => now()->subDay(),
        ]);
    }

    public static function pages(): array
    {
        return [
            'home' => ['/', 'Better lives for vulnerable people and communities</h1>'],
            'about' => ['/about', 'Our objectives'],
            'work' => ['/our-work', 'Focus areas'],
            'area' => ['/our-work/sickle-cell-and-blood-disorders', 'Related programmes'],
            'programmes' => ['/programmes', 'Our programmes'],
            'programme' => ['/programmes/sickle-cell-support', '1,250'],
            'partners' => ['/partners', 'Kano State Ministry of Health'],
            'news' => ['/news', 'Foundation featured in the press'],
            'involved' => ['/get-involved', 'Volunteer with the Foundation'],
            'support' => ['/support-us', 'Ways to support'],
            'contact' => ['/contact', 'No. 9 Kura Road, off Lamido Crescent'],
            'privacy' => ['/privacy', 'Nigeria Data Protection Act'],
        ];
    }

    #[DataProvider('pages')]
    public function test_public_pages_load(string $url, string $expected): void
    {
        $this->get($url)->assertOk()->assertSee($expected, false);
    }

    public function test_unknown_pages_return_a_helpful_404(): void
    {
        $this->get('/does-not-exist')->assertNotFound()->assertSee('We could not find that page');
        $this->get('/our-work/not-an-area')->assertNotFound();
        $this->get('/programmes/not-a-programme')->assertNotFound();
    }

    public function test_old_wordpress_addresses_redirect(): void
    {
        $this->get('/about-us')->assertRedirect('/about');
        $this->get('/projects')->assertRedirect('/programmes');
    }

    public function test_external_news_items_redirect_to_the_publisher(): void
    {
        $post = Post::first();
        $this->get(route('news.show', $post))->assertRedirect($post->external_url);
    }

    public function test_draft_and_scheduled_news_stay_hidden(): void
    {
        $draft = Post::create(['slug' => 'draft', 'title' => 'Draft story', 'body' => 'Text', 'published_at' => null]);
        $later = Post::create(['slug' => 'later', 'title' => 'Later story', 'body' => 'Text', 'published_at' => now()->addDay()]);

        $this->get('/news')->assertDontSee('Draft story')->assertDontSee('Later story');
        $this->get(route('news.show', $draft))->assertNotFound();
        $this->get(route('news.show', $later))->assertNotFound();
    }

    public function test_news_body_renders_headings_and_lists(): void
    {
        $post = Post::create([
            'slug' => 'training', 'title' => 'Volunteer training', 'published_at' => now()->subHour(),
            'body' => "First paragraph.\n\n## What they learned\n\n- Referral tracking\n- Safeguarding",
        ]);

        $this->get(route('news.show', $post))
            ->assertOk()
            ->assertSee('<h2>What they learned</h2>', false)
            ->assertSee('<li>Safeguarding</li>', false);
    }

    public function test_pages_work_before_any_content_is_added(): void
    {
        Project::query()->delete();
        Post::query()->delete();

        foreach (['/', '/about', '/programmes', '/partners', '/news', '/support-us'] as $url) {
            $this->get($url)->assertOk();
        }
        $this->get('/programmes')->assertSee('Programmes are on the way');
    }

    public function test_sitemap_lists_programmes(): void
    {
        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml')
            ->assertSee(route('projects.show', Project::first()), false);
    }
}
