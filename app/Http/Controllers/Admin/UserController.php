<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Administrators add, edit, switch off and remove the people who can sign in.
 * Safeguards: nobody can lock themselves out, and there is always at least
 * one active administrator.
 */
class UserController extends Controller
{
    public function index(): View
    {
        return view('admin.users.index', [
            'users' => User::orderByDesc('is_active')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.users.form', ['user' => new User(['role' => 'editor', 'is_active' => true])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $user = User::create($data);

        return redirect()->route('admin.users.index')
            ->with('status', $user->name.' can now sign in at '.route('login').' with the password you set. Ask them to change it under My account.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.form', ['user' => $user]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $this->validated($request, $user);
        $isSelf = $request->user()->is($user);

        // Guard against locking yourself, or everyone, out.
        $losingAdmin = $user->isAdmin() && $user->is_active
            && (($data['role'] ?? 'admin') !== 'admin' || ! ($data['is_active'] ?? true));
        if ($isSelf && $losingAdmin) {
            throw ValidationException::withMessages(['role' => 'You cannot remove your own administrator access or switch off your own account. Ask another administrator.']);
        }

        DB::transaction(function () use ($user, $data, $losingAdmin) {
            if ($losingAdmin && User::whereKeyNot($user->id)->where('role', 'admin')->where('is_active', true)->lockForUpdate()->doesntExist()) {
                throw ValidationException::withMessages(['role' => 'This is the only active administrator. Make someone else an administrator first.']);
            }
            if (blank($data['password'] ?? null)) {
                unset($data['password']);
            }
            $user->update($data);
        });

        // A changed password or a switched-off account ends their other sessions.
        if (! $isSelf && (isset($data['password']) || ! $user->is_active)) {
            DB::table('sessions')->where('user_id', $user->id)->delete();
            $user->forceFill(['remember_token' => null])->saveQuietly();
        }

        return redirect()->route('admin.users.index')->with('status', $user->name.'\'s account was updated.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($request->user()->is($user)) {
            return back()->withErrors(['user' => 'You cannot delete your own account.']);
        }
        if ($user->isLastActiveAdmin()) {
            return back()->withErrors(['user' => 'This is the only active administrator, so it cannot be deleted.']);
        }

        DB::table('sessions')->where('user_id', $user->id)->delete();
        $name = $user->name;
        $user->delete();

        return redirect()->route('admin.users.index')->with('status', $name.' was removed and can no longer sign in.');
    }

    private function validated(Request $request, ?User $user = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:160', Rule::unique('users', 'email')->ignore($user?->id)],
            'role' => ['required', Rule::in(array_keys(User::ROLES))],
            'is_active' => ['nullable', 'boolean'],
            'password' => [$user ? 'nullable' : 'required', 'confirmed', Password::min(10)->letters()->numbers()],
        ], [
            'password.required' => 'Set a first password for this person. They can change it after signing in.',
        ]);

        $data['email'] = strtolower(trim($data['email']));
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
