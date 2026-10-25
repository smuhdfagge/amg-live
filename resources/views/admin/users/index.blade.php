@php use App\Models\User; @endphp
@extends('admin.layout', ['title' => 'Users'])

@section('content')
<div class="admin-top"><h1>Users</h1><a class="btn btn-red btn-sm" href="{{ route('admin.users.create') }}"><x-icon name="plus" :size="18" /> Add user</a></div>
@if ($errors->any())<div class="notice notice-error" role="alert">{{ $errors->first() }}</div>@endif

<div class="panel table-wrap">
    <table class="data">
        <thead><tr><th>Name</th><th>Role</th><th>Status</th><th>Last signed in</th><th></th></tr></thead>
        <tbody>
        @foreach ($users as $user)
            <tr @class(['is-muted' => ! $user->is_active])>
                <td>
                    <a href="{{ route('admin.users.edit', $user) }}">{{ $user->name }}</a>
                    @if (auth()->user()->is($user))<span class="tag">You</span>@endif
                    <br><span class="small muted">{{ $user->email }}</span>
                </td>
                <td>{{ $user->roleLabel() }}</td>
                <td>@if ($user->is_active)<span class="badge badge-on">Active</span>@else<span class="badge badge-off">Switched off</span>@endif</td>
                <td class="small">{{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}</td>
                <td><div class="actions">
                    <a class="icon-btn" href="{{ route('admin.users.edit', $user) }}"><x-icon name="edit" :size="16" /> Edit</a>
                    @unless (auth()->user()->is($user) || $user->isLastActiveAdmin())
                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" data-confirm="Remove {{ $user->name }}? They will no longer be able to sign in. This cannot be undone.">@csrf @method('DELETE')<button class="icon-btn danger" type="submit"><x-icon name="trash" :size="16" /> Remove</button></form>
                    @endunless
                </div></td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

<div class="panel">
    <h2>What each role can do</h2>
    <dl class="role-help">
        @foreach (User::ROLES as $key => $label)
            <div><dt>{{ $label }}</dt><dd>{{ User::ROLE_HELP[$key] }}</dd></div>
        @endforeach
    </dl>
    <p class="muted small mb-0">Switch off an account instead of removing it if the person may come back. Switching off or changing a password signs them out straight away.</p>
</div>
@endsection
