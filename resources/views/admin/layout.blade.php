@php
    $unread = \App\Models\ContactMessage::whereNull('read_at')->count();
    $newVol = \App\Models\VolunteerApplication::whereNull('read_at')->count();
    $items = [
        ['admin.dashboard', 'admin.dashboard', 'home', 'Dashboard', null],
        ['admin.posts.index', 'admin.posts.*', 'news', 'News', null],
        ['admin.projects.index', 'admin.projects.*', 'folder', 'Programmes', null],
        ['admin.messages.index', 'admin.messages.*', 'inbox', 'Messages', $unread],
        ['admin.volunteers.index', 'admin.volunteers.*', 'hand', 'Volunteers', $newVol],
        ['admin.subscribers.index', 'admin.subscribers.*', 'users', 'Subscribers', null],
        ['admin.account', 'admin.account*', 'settings', 'My account', null],
    ];
@endphp
<!DOCTYPE html>
<html lang="en-NG">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $title ?? 'Admin' }} | SWATCH admin</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="stylesheet" href="{{ asset('css/site.css') }}?v={{ filemtime(public_path('css/site.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}?v={{ filemtime(public_path('css/admin.css')) }}">
</head>
<body class="admin">
<div class="admin-mobile-bar">
    <strong>SWATCH admin</strong>
    <button type="button" data-admin-toggle aria-label="Toggle menu"><x-icon name="menu" :size="26" /></button>
</div>
<div class="admin-shell">
    <aside class="admin-side">
        <a href="{{ route('admin.dashboard') }}" class="brand">
            <img src="{{ asset('images/swatch-logo-180.png') }}" alt="" width="44" height="44">
            <span class="brand-name">SWATCH</span>
        </a>
        <ul class="admin-nav">
            @foreach ($items as [$route, $match, $icon, $label, $count])
                <li><a href="{{ route($route) }}" @if (request()->routeIs($match)) aria-current="page" @endif><x-icon :name="$icon" :size="20" /> {{ $label }} @if ($count)<span class="count">{{ $count }}</span>@endif</a></li>
            @endforeach
            <li><hr></li>
            <li><a href="{{ route('home') }}" target="_blank" rel="noopener"><x-icon name="external" :size="20" /> View website</a></li>
            <li>
                <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit"><x-icon name="logout" :size="20" /> Sign out</button></form>
            </li>
        </ul>
    </aside>
    <main class="admin-main" id="main">
        @if (session('status'))
            <div class="notice notice-success" role="status"><x-icon name="check" :size="22" /><span>{{ session('status') }}</span></div>
        @endif
        @yield('content')
    </main>
</div>
<script src="{{ asset('js/site.js') }}?v={{ filemtime(public_path('js/site.js')) }}" defer></script>
</body>
</html>
