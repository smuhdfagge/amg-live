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
    }

    public static function pages(): array
    {
        return [
            'home' => ['/', 'Support for Women and Teenage Children</h1>'],
            'about' => ['/about', 'How we started'],
            'work' => ['/our-work', 'Thematic areas'],
            'area' => ['/our-work/health', 'Related programmes'],
            'programmes' => ['/programmes', 'Programme record'],
            'programme' => ['/programmes/lopin-3', '12,083'],
            'partners' => ['/partners', 'Chemonics International'],
            'news' => ['/news', 'advanced HIV disease'],
            'involved' => ['/get-involved', 'Volunteer with SWATCH'],
            'support' => ['/support-us', 'Ways to support'],
            'contact' => ['/contact', 'Plot 203, New Court Road'],
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

    public function test_sitemap_lists_programmes(): void
    {
        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml')
            ->assertSee(route('projects.show', Project::first()), false);
    }
}
