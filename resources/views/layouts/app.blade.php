@php
    $org = config('amg');
    $pageTitle = isset($title) ? $title.' | AMG Foundation' : 'Aminu Magashi Garba Foundation | Kano, Nigeria';
    $pageDescription = $description ?? 'The Aminu Magashi Garba Foundation works to improve the lives of vulnerable people and communities in Kano through lasting partnerships, sustainable financing and innovative solutions.';
    $c = $org['contact'];
    $nav = [
        ['route' => 'about', 'label' => 'About us', 'match' => 'about'],
        ['route' => 'work', 'label' => 'Our work', 'match' => 'work*'],
        ['route' => 'projects.index', 'label' => 'Programmes', 'match' => 'projects.*'],
        ['route' => 'news.index', 'label' => 'News', 'match' => 'news.*'],
        ['route' => 'involved', 'label' => 'Get involved', 'match' => 'involved'],
        ['route' => 'contact', 'label' => 'Contact', 'match' => 'contact'],
    ];
@endphp
<!DOCTYPE html>
<html lang="en-NG">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $pageDescription }}">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta name="theme-color" content="#00733a">

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Aminu Magashi Garba Foundation">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDescription }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ $ogImage ?? asset('images/og-image.png') }}">
    <meta property="og:locale" content="en_NG">
    <meta name="twitter:card" content="summary_large_image">

    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">

    <link rel="preload" href="{{ asset('fonts/archivo-latin-variable.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="{{ asset('fonts/figtree-latin-400-normal.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="{{ asset('css/site.css') }}?v={{ filemtime(public_path('css/site.css')) }}">

    <script type="application/ld+json">
    {!! json_encode(array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'NGO',
        'name' => $org['name'],
        'alternateName' => 'AMG Foundation',
        'url' => url('/'),
        'logo' => asset('images/amg-logo.png'),
        'foundingDate' => filled($org['founded']) ? (string) $org['founded'] : null,
        'email' => $c['email'] ?: null,
        'telephone' => $c['phone_link'] ?: null,
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => $c['address_line_1'],
            'addressLocality' => 'Nassarawa GRA, Kano',
            'addressRegion' => 'Kano State',
            'addressCountry' => 'NG',
        ],
    ]), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
    </script>
    @stack('head')
</head>
<body>
<a class="skip-link" href="#main">Skip to main content</a>

@if (filled($c['phone']) || filled($c['email']) || filled($org['registration']['cac']))
<div class="topbar">
    <div class="wrap">
        <span>@if (filled($org['registration']['cac']))Registered NGO, {{ $org['registration']['cac'] }}@else{{ $c['address_line_2'] }}@endif</span>
        <div class="topbar-contacts">
            @if (filled($c['phone']))<span><x-icon name="phone" :size="16" /><a href="tel:{{ $c['phone_link'] }}">{{ $c['phone'] }}</a></span>@endif
            @if (filled($c['email']))<span><x-icon name="mail" :size="16" /><a href="mailto:{{ $c['email'] }}">{{ $c['email'] }}</a></span>@endif
        </div>
    </div>
</div>
@endif

<header class="site-header">
    <div class="wrap">
        <a href="{{ route('home') }}" class="brand" aria-label="Aminu Magashi Garba Foundation home">
            <img src="{{ asset('images/amg-logo.svg') }}" alt="" width="170" height="83">
        </a>

        <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="main-nav" data-nav-toggle>
            <span class="sr-only">Open menu</span>
            <x-icon name="menu" :size="28" data-icon-open />
            <x-icon name="close" :size="28" data-icon-close hidden />
        </button>

        <nav class="main-nav" id="main-nav" aria-label="Main">
            <ul>
                @foreach ($nav as $item)
                    <li><a href="{{ route($item['route']) }}" @if (request()->routeIs($item['match'])) aria-current="page" @endif>{{ $item['label'] }}</a></li>
                @endforeach
                <li class="header-cta"><a class="btn btn-red btn-sm" href="{{ route('support') }}">Support our work</a></li>
            </ul>
        </nav>
    </div>
</header>

<main id="main">
    @yield('content')
</main>

@unless (isset($hideCta) && $hideCta)
<section class="cta-band" aria-labelledby="cta-heading">
    <div class="wrap">
        <div>
            <h2 id="cta-heading">Partner with us</h2>
            <p>We welcome government agencies, development partners, companies, foundations and volunteers who want to make a lasting difference for vulnerable people.</p>
        </div>
        <div class="btn-row">
            <a class="btn btn-white" href="{{ route('contact', ['type' => 'partnership']) }}#message">Talk to us about a partnership</a>
            <a class="btn btn-ghost-light" href="{{ route('involved') }}">Volunteer with us</a>
        </div>
    </div>
</section>
@endunless

<footer class="site-footer">
    <div class="footer-motif">@include('partials.motif')</div>
    <div class="wrap footer-main">
        <div>
            <div class="footer-brand">
                <img src="{{ asset('images/amg-logo-white.svg') }}" alt="Aminu Magashi Garba Foundation" width="190" height="93" loading="lazy">
            </div>
            <p>A non-profit foundation improving the lives of vulnerable people and communities through partnerships, sustainable financing and innovative solutions.</p>
            @if (filled($org['registration']['cac']) || filled($org['registration']['tin']))
                <p class="small">
                    @if (filled($org['registration']['cac']))CAC registration: {{ $org['registration']['cac'] }}<br>@endif
                    @if (filled($org['registration']['tin']))FIRS TIN: {{ $org['registration']['tin'] }}@endif
                </p>
            @endif
            @php $socials = array_filter($org['social']); @endphp
            @if ($socials)
                <div class="social">
                    @foreach ($socials as $network => $link)
                        <a href="{{ $link }}" target="_blank" rel="noopener" aria-label="AMG Foundation on {{ $network === 'x' ? 'X' : ucfirst($network) }}"><x-icon :name="$network" :size="20" /></a>
                    @endforeach
                </div>
            @endif
        </div>
        <div>
            <h2>Organisation</h2>
            <ul class="footer-links">
                <li><a href="{{ route('about') }}">About us</a></li>
                <li><a href="{{ route('about') }}#objectives">Our objectives</a></li>
                <li><a href="{{ route('partners') }}">Partners</a></li>
                <li><a href="{{ route('news.index') }}">News</a></li>
                <li><a href="{{ route('privacy') }}">Privacy notice</a></li>
            </ul>
        </div>
        <div>
            <h2>Our work</h2>
            <ul class="footer-links">
                <li><a href="{{ route('work') }}">Focus areas</a></li>
                <li><a href="{{ route('projects.index') }}">Programmes</a></li>
                <li><a href="{{ route('involved') }}">Volunteer</a></li>
                <li><a href="{{ route('support') }}">Support our work</a></li>
                <li><a href="{{ route('contact') }}">Contact us</a></li>
            </ul>
        </div>
        <div>
            <h2>Visit us</h2>
            <ul class="footer-contact">
                <li><x-icon name="pin" :size="18" /><span>{{ $c['address_line_1'] }},<br>{{ $c['address_line_2'] }}, {{ $c['country'] }}</span></li>
                @if (filled($c['phone']))<li><x-icon name="phone" :size="18" /><a href="tel:{{ $c['phone_link'] }}">{{ $c['phone'] }}</a></li>@endif
                @if (filled($c['email']))<li><x-icon name="mail" :size="18" /><a href="mailto:{{ $c['email'] }}">{{ $c['email'] }}</a></li>@endif
            </ul>
            <h2 class="mt-2" id="subscribe-heading">Get our updates</h2>
            <form class="subscribe" method="POST" action="{{ route('subscribe') }}" aria-labelledby="subscribe-heading">
                @csrf
                <div class="hp" aria-hidden="true"><label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
                <label for="subscribe-email" class="sr-only">Email address</label>
                <input id="subscribe-email" type="email" name="email" placeholder="Your email address" required autocomplete="email">
                <button class="btn btn-red btn-sm" type="submit">Subscribe</button>
            </form>
            @if (session('subscribe_status'))
                <p class="footer-msg" role="status">{{ session('subscribe_status') }}</p>
            @endif
            @if ($errors->subscribe->any())
                <p class="footer-msg err" role="alert">{{ $errors->subscribe->first('email') }}</p>
            @endif
        </div>
    </div>
    <div class="footer-bottom">
        <div class="wrap">
            <span>&copy; {{ date('Y') }} Aminu Magashi Garba Foundation. All rights reserved.</span>
            <span>{{ $c['address_line_1'] }}, {{ $c['address_line_2'] }}</span>
        </div>
    </div>
</footer>

<script src="{{ asset('js/site.js') }}?v={{ filemtime(public_path('js/site.js')) }}" defer></script>
</body>
</html>
