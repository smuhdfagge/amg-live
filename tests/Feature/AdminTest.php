<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use App\Models\Post;
use App\Models\Project;
use App\Models\User;
use Database\Seeders\ProjectSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create(['name' => 'Admin', 'email' => 'admin@swatch.test', 'password' => 'secret-password']);
    }

    public function test_admin_pages_need_sign_in(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->get('/admin/messages')->assertRedirect('/admin/login');
    }

    public function test_admin_can_sign_in_and_out(): void
    {
        $this->admin();

        $this->post('/admin/login', ['email' => 'admin@swatch.test', 'password' => 'wrong'])->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->post('/admin/login', ['email' => 'admin@swatch.test', 'password' => 'secret-password'])->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticated();

        $this->post('/admin/logout')->assertRedirect('/admin/login');
        $this->assertGuest();
    }

    public function test_every_admin_screen_loads(): void
    {
        $this->seed(ProjectSeeder::class);
        $user = $this->admin();

        foreach (['/admin', '/admin/posts', '/admin/posts/create', '/admin/projects', '/admin/projects/create',
            '/admin/projects/lopin-3/edit', '/admin/messages', '/admin/volunteers', '/admin/subscribers', '/admin/account'] as $url) {
            $this->actingAs($user)->get($url)->assertOk();
        }
    }

    public function test_admin_can_publish_news_with_a_photo(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin())->post('/admin/posts', [
            'title' => 'New outreach in Ungogo',
            'category' => 'Field update',
            'body' => 'Our team reached 200 households.',
            'published_at' => now()->subMinute()->format('Y-m-d\TH:i'),
            'cover' => UploadedFile::fake()->image('outreach.jpg', 1200, 800),
        ])->assertRedirect(route('admin.posts.index'));

        $post = Post::firstWhere('slug', 'new-outreach-in-ungogo');
        $this->assertNotNull($post);
        Storage::disk('public')->assertExists($post->cover_image);
        $this->get('/news/new-outreach-in-ungogo')->assertOk()->assertSee('Our team reached 200 households.');
    }

    public function test_news_needs_a_story_or_a_link(): void
    {
        $this->actingAs($this->admin())->post('/admin/posts', ['title' => 'Empty', 'category' => 'News'])
            ->assertSessionHasErrors('body');
    }

    public function test_admin_can_edit_a_programme(): void
    {
        $this->seed(ProjectSeeder::class);
        $project = Project::firstWhere('slug', 'sidhas');

        $this->actingAs($this->admin())->put(route('admin.projects.update', $project), [
            'title' => $project->title, 'short_title' => 'SIDHAS', 'summary' => 'Updated summary for donors.',
            'status' => 'completed', 'sort_order' => 5,
        ])->assertRedirect(route('admin.projects.index'));

        $this->get('/programmes/sidhas')->assertSee('Updated summary for donors.');
    }

    public function test_opening_a_message_marks_it_read(): void
    {
        $message = ContactMessage::create(['name' => 'Hauwa', 'email' => 'h@example.com', 'message' => 'Hello']);

        $this->actingAs($this->admin())->get(route('admin.messages.show', $message))->assertOk()->assertSee('Hauwa');
        $this->assertNotNull($message->fresh()->read_at);
    }
}
