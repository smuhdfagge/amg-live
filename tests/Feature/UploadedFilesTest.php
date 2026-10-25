<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * News photos must show even when the public/storage shortcut is missing
 * or the host does not allow it, and whatever APP_URL is set to.
 */
class UploadedFilesTest extends TestCase
{
    use RefreshDatabase;

    public function test_news_photo_is_served_without_the_storage_link(): void
    {
        Storage::fake('public');
        $admin = User::create(['name' => 'Admin', 'email' => 'admin@amg.test', 'password' => 'secret-password']);

        $this->actingAs($admin)->post('/admin/posts', [
            'title' => 'Outreach in Dala',
            'category' => 'News',
            'body' => 'Story text.',
            'published_at' => now()->subMinute()->format('Y-m-d\TH:i'),
            'cover' => UploadedFile::fake()->image('outreach.jpg', 1200, 800),
        ])->assertRedirect();

        $post = Post::firstWhere('slug', 'outreach-in-dala');
        $src = $post->coverUrl();

        $this->assertStringStartsWith('/storage/news/', $src, 'Photo addresses should not depend on APP_URL.');
        $this->get('/news')->assertSee('src="'.$src.'"', false);
        $this->get('/')->assertSee('src="'.$src.'"', false);

        $response = $this->get($src);
        $response->assertOk();
        $this->assertStringStartsWith('image/', $response->headers->get('Content-Type'));

        // Share image for Facebook/WhatsApp is a full address.
        $this->get('/news/outreach-in-dala')->assertSee('<meta property="og:image" content="'.url($src).'">', false);
    }

    public function test_storage_route_refuses_missing_and_unsafe_paths(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('.secret', 'x');

        $this->get('/storage/news/missing.jpg')->assertNotFound();
        $this->get('/storage/.secret')->assertNotFound();
        $this->get('/storage/../.env')->assertNotFound();
        $this->get('/storage/news/..%2F..%2F.env')->assertNotFound();
    }
}
