@php use App\Models\User; $isSelf = $user->exists && auth()->user()->is($user); @endphp
@extends('admin.layout', ['title' => $user->exists ? 'Edit user' : 'Add user'])

@section('content')
<div class="admin-top"><h1>{{ $user->exists ? 'Edit '.$user->name : 'Add user' }}</h1><a class="text-link" href="{{ route('admin.users.index') }}">Back to users</a></div>
@if ($errors->any())<div class="notice notice-error" role="alert">Some details need your attention. Check the highlighted fields.</div>@endif

<form method="POST" action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}" class="panel" style="max-width:760px" autocomplete="off">
    @csrf
    @if ($user->exists) @method('PUT') @endif
    <div class="form-grid">
        @include('partials.field', ['name' => 'name', 'label' => 'Full name', 'required' => true, 'value' => $user->name])
        @include('partials.field', ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true, 'value' => $user->email])

        <fieldset class="field full @error('role') has-error @enderror" style="border:0;padding:0;margin:0">
            <legend style="font-weight:600;margin-bottom:.5rem">Role</legend>
            <div class="choice" style="margin:0">
                @foreach (User::ROLES as $key => $label)
                    <label class="choice-card">
                        <input type="radio" name="role" value="{{ $key }}" @checked(old('role', $user->role) === $key) @disabled($isSelf && $key !== 'admin' && $user->isAdmin())>
                        <span><strong>{{ $label }}</strong><br><span class="muted small">{{ User::ROLE_HELP[$key] }}</span></span>
                    </label>
                @endforeach
            </div>
            @if ($isSelf)<input type="hidden" name="role" value="{{ $user->role }}">@endif
            @error('role')<span class="error">{{ $message }}</span>@enderror
        </fieldset>

        <div class="field full">
            @if ($isSelf)
                <input type="hidden" name="is_active" value="1">
                <p class="help mt-0">This is your own account, so you cannot switch it off or remove your administrator access here.</p>
            @else
                <label class="checkbox"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $user->is_active))> Account is active (can sign in)</label>
            @endif
        </div>

        <div class="field full"><p class="help mt-0">{{ $user->exists ? 'To set a new password for this person, enter it twice. Leave both empty to keep their current password.' : 'Set a first password. Tell the person privately, and ask them to change it under My account.' }}</p></div>
        <div class="field @error('password') has-error @enderror">
            <label for="password">{{ $user->exists ? 'New password' : 'Password' }} <span class="hint">(10+ characters, letters and numbers)</span></label>
            <input id="password" type="password" name="password" autocomplete="new-password" @unless ($user->exists) required @endunless>
            @error('password')<span class="error">{{ $message }}</span>@enderror
        </div>
        <div class="field">
            <label for="password_confirmation">Repeat password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" @unless ($user->exists) required @endunless>
        </div>
    </div>
    <div class="form-actions">
        <button class="btn btn-red" type="submit">{{ $user->exists ? 'Save changes' : 'Add user' }}</button>
        <a class="btn btn-outline" href="{{ route('admin.users.index') }}">Cancel</a>
    </div>
</form>
@endsection
