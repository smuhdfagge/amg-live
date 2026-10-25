<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UsersTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role = 'admin', array $extra = []): User
    {
        static $n = 0;
        $n++;

        return User::create(['name' => ucfirst($role)." {$n}", 'email' => "{$role}{$n}@amg.test", 'password' => 'Secret-pass-123', 'role' => $role] + $extra);
    }

    public function test_admin_can_add_a_user_who_can_then_sign_in(): void
    {
        $admin = $this->user();

        $this->actingAs($admin)->get('/admin/users')->assertOk()->assertSee($admin->email)->assertSee('You');
        $this->actingAs($admin)->get('/admin/users/create')->assertOk();
        $this->actingAs($admin)->post('/admin/users', [
            'name' => 'Hauwa Sani', 'email' => 'Hauwa@Example.org', 'role' => 'editor', 'is_active' => '1',
            'password' => 'Kano-news-2026', 'password_confirmation' => 'Kano-news-2026',
        ])->assertRedirect(route('admin.users.index'));

        $hauwa = User::firstWhere('email', 'hauwa@example.org');
        $this->assertSame('editor', $hauwa->role);
        $this->assertTrue(Hash::check('Kano-news-2026', $hauwa->password));

        $this->post('/admin/logout');
        $this->post('/admin/login', ['email' => 'hauwa@example.org', 'password' => 'Kano-news-2026'])->assertRedirect(route('admin.dashboard'));
        $this->assertNotNull($hauwa->fresh()->last_login_at);
    }

    public function test_weak_passwords_and_duplicate_emails_are_refused(): void
    {
        $admin = $this->user();

        $this->actingAs($admin)->from('/admin/users/create')->post('/admin/users', [
            'name' => 'X', 'email' => $admin->email, 'role' => 'editor', 'password' => 'short', 'password_confirmation' => 'short',
        ])->assertSessionHasErrors(['email', 'password']);

        $this->actingAs($admin)->from('/admin/users/create')->post('/admin/users', [
            'name' => 'X', 'email' => 'x@amg.test', 'role' => 'editor', 'password' => 'onlyletterspassword', 'password_confirmation' => 'onlyletterspassword',
        ])->assertSessionHasErrors('password');

        $this->actingAs($admin)->from('/admin/users/create')->post('/admin/users', [
            'name' => 'X', 'email' => 'x@amg.test', 'role' => 'superuser', 'password' => 'Good-pass-123', 'password_confirmation' => 'Good-pass-123',
        ])->assertSessionHasErrors('role');

        $this->assertSame(1, User::count());
    }

    public function test_editors_cannot_see_personal_data_or_manage_users(): void
    {
        $editor = $this->user('editor');

        foreach (['/admin', '/admin/posts', '/admin/projects', '/admin/resources', '/admin/news-import', '/admin/account'] as $url) {
            $this->actingAs($editor)->get($url)->assertOk();
        }
        foreach (['/admin/users', '/admin/users/create', '/admin/messages', '/admin/volunteers', '/admin/volunteers/export', '/admin/subscribers', '/admin/subscribers/export'] as $url) {
            $this->actingAs($editor)->get($url)->assertForbidden();
        }
        $this->actingAs($editor)->post('/admin/users', ['name' => 'Me', 'email' => 'me2@amg.test', 'role' => 'admin', 'password' => 'Good-pass-123', 'password_confirmation' => 'Good-pass-123'])->assertForbidden();
        $this->actingAs($editor)->put('/admin/users/'.$editor->id, ['name' => 'Me', 'email' => $editor->email, 'role' => 'admin'])->assertForbidden();
        $this->assertSame('editor', $editor->fresh()->role);

        $this->actingAs($editor)->get('/admin')->assertDontSee('Latest messages')->assertDontSee('href="'.route('admin.users.index').'"', false);
    }

    public function test_switched_off_users_cannot_sign_in_and_are_signed_out(): void
    {
        $admin = $this->user();
        $editor = $this->user('editor');

        $this->actingAs($editor)->get('/admin')->assertOk();
        $this->actingAs($admin)->put('/admin/users/'.$editor->id, [
            'name' => $editor->name, 'email' => $editor->email, 'role' => 'editor',   // is_active not ticked
        ])->assertRedirect(route('admin.users.index'));
        $this->assertFalse($editor->fresh()->is_active);

        // Already signed in: signed out on the next page.
        $this->actingAs($editor->fresh())->get('/admin')->assertRedirect(route('login'));

        // Signing in again: refused, with the usual message.
        $this->post('/admin/logout');
        $this->post('/admin/login', ['email' => $editor->email, 'password' => 'Secret-pass-123'])
            ->assertSessionHasErrors(['email' => 'The email or password is not correct.']);
        $this->assertGuest();
    }

    public function test_nobody_can_lock_themselves_or_everyone_out(): void
    {
        $admin = $this->user();
        $other = $this->user();

        // Not yourself: no demoting, no switching off, no deleting.
        $this->actingAs($admin)->from('/admin/users/'.$admin->id.'/edit')->put('/admin/users/'.$admin->id, [
            'name' => $admin->name, 'email' => $admin->email, 'role' => 'editor', 'is_active' => '1',
        ])->assertSessionHasErrors('role');
        $this->actingAs($admin)->from('/admin/users/'.$admin->id.'/edit')->put('/admin/users/'.$admin->id, [
            'name' => $admin->name, 'email' => $admin->email, 'role' => 'admin',
        ])->assertSessionHasErrors('role');
        $this->actingAs($admin)->from('/admin/users')->delete('/admin/users/'.$admin->id)->assertSessionHasErrors('user');
        $this->assertTrue($admin->fresh()->is_active && $admin->fresh()->isAdmin());

        // Another admin can be demoted while one admin remains.
        $this->actingAs($admin)->put('/admin/users/'.$other->id, ['name' => $other->name, 'email' => $other->email, 'role' => 'editor', 'is_active' => '1'])
            ->assertSessionHasNoErrors();
        $this->assertSame('editor', $other->fresh()->role);

        // The last active admin can never be deleted.
        $this->assertTrue($admin->fresh()->isLastActiveAdmin());
        $this->actingAs($other->fresh())->delete('/admin/users/'.$admin->id)->assertForbidden();   // editors cannot anyway
        $this->assertNotNull($admin->fresh());
    }

    public function test_admin_can_reset_a_password_and_remove_a_user(): void
    {
        $admin = $this->user();
        $editor = $this->user('editor');

        $this->actingAs($admin)->put('/admin/users/'.$editor->id, [
            'name' => 'Renamed Editor', 'email' => $editor->email, 'role' => 'editor', 'is_active' => '1',
            'password' => 'New-pass-2026', 'password_confirmation' => 'New-pass-2026',
        ])->assertRedirect(route('admin.users.index'));
        $this->assertTrue(Hash::check('New-pass-2026', $editor->fresh()->password));

        // Leaving the password empty keeps it.
        $this->actingAs($admin)->put('/admin/users/'.$editor->id, ['name' => 'Again', 'email' => $editor->email, 'role' => 'editor', 'is_active' => '1']);
        $this->assertTrue(Hash::check('New-pass-2026', $editor->fresh()->password));

        $this->actingAs($admin)->delete('/admin/users/'.$editor->id)->assertRedirect(route('admin.users.index'));
        $this->assertNull($editor->fresh());
    }

    public function test_user_names_are_shown_safely(): void
    {
        $admin = $this->user();
        $this->actingAs($admin)->post('/admin/users', [
            'name' => '<script>alert(1)</script><img src=x onerror=alert(2)>', 'email' => 'x@amg.test', 'role' => 'editor', 'is_active' => '1',
            'password' => 'Good-pass-123', 'password_confirmation' => 'Good-pass-123',
        ]);
        $user = User::firstWhere('email', 'x@amg.test');

        foreach (['/admin/users', '/admin/users/'.$user->id.'/edit'] as $url) {
            $html = $this->actingAs($admin)->get($url)->getContent();
            $this->assertStringNotContainsString('<script>alert(1)', $html);
            $this->assertStringNotContainsString('<img src=x', $html);
        }
        $this->actingAs($user)->get('/admin')->assertOk()->assertDontSee('<script>alert(1)', false);
    }
}
