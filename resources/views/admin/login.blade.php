<!DOCTYPE html>
<html lang="en-NG">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Sign in | SWATCH admin</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="stylesheet" href="{{ asset('css/site.css') }}?v={{ filemtime(public_path('css/site.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}?v={{ filemtime(public_path('css/admin.css')) }}">
</head>
<body>
<main class="login-page">
    <div class="login-card">
        <a href="{{ route('home') }}" class="brand">
            <img src="{{ asset('images/swatch-logo-180.png') }}" alt="" width="54" height="54">
            <span><span class="brand-name">SWATCH</span><span class="brand-sub">Website administration</span></span>
        </a>
        <h1 style="font-size:1.6rem">Sign in</h1>
        @if (session('status'))<div class="notice notice-success" role="status">{{ session('status') }}</div>@endif
        <form method="POST" action="{{ route('login.attempt') }}">
            @csrf
            <div class="stack">
                @include('partials.field', ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true, 'autocomplete' => 'username'])
                <div class="field @error('password') has-error @enderror">
                    <label for="password">Password</label>
                    <input id="password" type="password" name="password" required autocomplete="current-password">
                </div>
                <div class="field"><label class="checkbox"><input type="checkbox" name="remember" value="1"> Keep me signed in</label></div>
                <button class="btn btn-indigo" type="submit" style="width:100%">Sign in</button>
            </div>
        </form>
    </div>
</main>
</body>
</html>
