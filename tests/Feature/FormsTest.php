<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use App\Models\Subscriber;
use App\Models\VolunteerApplication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FormsTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_message_is_saved(): void
    {
        $this->post('/contact', [
            'type' => 'partnership', 'name' => 'Amina Yusuf', 'email' => 'amina@example.org',
            'message' => 'We would like to discuss a partnership.',
        ])->assertRedirect(route('contact').'#message')->assertSessionHas('status');

        $this->assertDatabaseHas('contact_messages', ['email' => 'amina@example.org', 'type' => 'partnership']);
    }

    public function test_contact_form_requires_name_email_and_message(): void
    {
        $this->from('/contact')->post('/contact', ['type' => 'general'])
            ->assertSessionHasErrors(['name', 'email', 'message']);

        $this->assertSame(0, ContactMessage::count());
    }

    public function test_spam_bots_are_ignored(): void
    {
        $this->post('/contact', [
            'type' => 'general', 'name' => 'Bot', 'email' => 'bot@example.com', 'message' => 'Buy now', 'website' => 'http://spam.test',
        ])->assertRedirect();

        $this->assertSame(0, ContactMessage::count());
    }

    public function test_volunteer_application_is_saved(): void
    {
        $this->post('/get-involved/volunteer', [
            'name' => 'Musa Ibrahim', 'email' => 'musa@example.com', 'phone' => '08030000000', 'interest' => 'data',
        ])->assertRedirect(route('involved').'#volunteer');

        $this->assertSame(1, VolunteerApplication::count());
    }

    public function test_subscribing_twice_keeps_one_record(): void
    {
        $this->post('/subscribe', ['email' => 'Reader@Example.com']);
        $this->post('/subscribe', ['email' => 'reader@example.com']);

        $this->assertSame(1, Subscriber::count());
    }
}
