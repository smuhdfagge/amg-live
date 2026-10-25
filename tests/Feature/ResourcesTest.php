<?php

namespace Tests\Feature;

use App\Models\Resource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ResourcesTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create(['name' => 'Admin', 'email' => 'admin@amg.test', 'password' => 'secret-password']);
    }

    public function test_resources_page_loads_empty(): void
    {
        $this->get('/resources')->assertOk()->assertSee('will appear here soon');
        $this->get('/resources?type=video')->assertOk()->assertSee('There are no videos yet');
    }

    public function test_each_type_shows_the_right_player(): void
    {
        Resource::create(['type' => 'radio', 'title' => 'Sickle cell awareness on Freedom Radio', 'media_file' => 'resources/radio/show.mp3', 'published_at' => now()->subDay(), 'allow_download' => true]);
        Resource::create(['type' => 'jingle', 'title' => 'Know your genotype jingle', 'media_url' => 'https://example.com/jingle.mp3', 'published_at' => now()->subDay()]);
        Resource::create(['type' => 'video', 'title' => 'Our outreach in Dala', 'media_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'published_at' => now()->subDay()]);
        Resource::create(['type' => 'video', 'title' => 'Facebook live recording', 'media_url' => 'https://facebook.com/amg/videos/1', 'published_at' => now()->subDay()]);

        $this->get('/resources')
            ->assertOk()
            ->assertSee('Sickle cell awareness on Freedom Radio')
            ->assertSee('<audio controls', false)
            ->assertSee('/storage/resources/radio/show.mp3', false)
            ->assertSee('Download')
            ->assertSee('https://example.com/jingle.mp3', false)
            ->assertSee('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ', false)
            ->assertSee('https://i.ytimg.com/vi/dQw4w9WgXcQ/hqdefault.jpg', false)
            ->assertSee('https://facebook.com/amg/videos/1', false);

        $this->get('/resources?type=jingle')
            ->assertSee('Know your genotype jingle')
            ->assertDontSee('Our outreach in Dala');

        $this->get('/')->assertSee('Listen and watch')->assertSee('Our outreach in Dala');
    }

    public function test_drafts_and_scheduled_items_stay_hidden(): void
    {
        Resource::create(['type' => 'radio', 'title' => 'Draft programme', 'media_url' => 'https://example.com/a.mp3']);
        Resource::create(['type' => 'radio', 'title' => 'Next week programme', 'media_url' => 'https://example.com/b.mp3', 'published_at' => now()->addWeek()]);

        $this->get('/resources')->assertDontSee('Draft programme')->assertDontSee('Next week programme');
    }

    public function test_admin_can_upload_a_radio_programme(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin())->post('/admin/resources', [
            'type' => 'radio',
            'title' => 'Mental health phone-in',
            'language' => 'Hausa',
            'duration' => '28:30',
            'media' => UploadedFile::fake()->create('phone-in.mp3', 2048, 'audio/mpeg'),
            'allow_download' => '1',
            'published_at' => now()->subMinute()->format('Y-m-d\TH:i'),
        ])->assertRedirect(route('admin.resources.index'));

        $resource = Resource::firstWhere('title', 'Mental health phone-in');
        $this->assertNotNull($resource);
        Storage::disk('public')->assertExists($resource->media_file);
        $this->assertTrue($resource->allow_download);
        $this->get('/resources')->assertSee('Mental health phone-in')->assertSee('28:30');
    }

    public function test_admin_must_give_a_file_or_a_link_of_the_right_kind(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/admin/resources', ['type' => 'video', 'title' => 'No media'])
            ->assertSessionHasErrors('media');

        $this->actingAs($admin)->post('/admin/resources', [
            'type' => 'jingle', 'title' => 'Wrong file',
            'media' => UploadedFile::fake()->create('clip.mp4', 100, 'video/mp4'),
        ])->assertSessionHasErrors('media');

        $this->assertSame(0, Resource::count());
    }

    public function test_admin_can_edit_filter_and_delete(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('resources/video/clip.mp4', 'x');
        $video = Resource::create(['type' => 'video', 'title' => 'Clip', 'media_file' => 'resources/video/clip.mp4', 'published_at' => now()]);
        Resource::create(['type' => 'radio', 'title' => 'Radio show', 'media_url' => 'https://example.com/r.mp3', 'published_at' => now()]);
        $admin = $this->admin();

        foreach (['/admin/resources', '/admin/resources?type=video', '/admin/resources/create', '/admin/resources/create?type=jingle', "/admin/resources/{$video->id}/edit"] as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
        $this->actingAs($admin)->get('/admin/resources?type=video')->assertSee('Clip')->assertDontSee('Radio show');

        $this->actingAs($admin)->put(route('admin.resources.update', $video), [
            'type' => 'video', 'title' => 'Clip, updated', 'published_at' => now()->format('Y-m-d\TH:i'),
        ])->assertRedirect(route('admin.resources.index'));
        $this->assertSame('Clip, updated', $video->fresh()->title);
        $this->assertFalse($video->fresh()->allow_download);

        $this->actingAs($admin)->delete(route('admin.resources.destroy', $video))->assertRedirect();
        $this->assertNull($video->fresh());
        Storage::disk('public')->assertMissing('resources/video/clip.mp4');
    }

    public function test_resources_are_in_the_sitemap_and_need_sign_in(): void
    {
        $this->get('/sitemap.xml')->assertSee(route('resources.index'), false);
        $this->get('/admin/resources')->assertRedirect('/admin/login');
    }

    public function test_an_oversized_upload_returns_to_the_form_with_a_message(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->from('/admin/resources/create')
            ->call('POST', '/admin/resources', [], [], [], ['CONTENT_LENGTH' => 9999999999, 'HTTP_REFERER' => url('/admin/resources/create')])
            ->assertRedirect(url('/admin/resources/create?too_large=1'));

        $this->actingAs($admin)->get('/admin/resources/create?too_large=1')
            ->assertSee('bigger than this server accepts');
    }
}
