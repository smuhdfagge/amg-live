@php
    $org = config('swatch');
    $pageTitle = isset($title) ? $title.' | SWATCH' : 'SWATCH | Support for Women and Teenage Children, Kano';
    $pageDescription = $description ?? 'SWATCH is a women-led, non-profit organisation in Kano, Nigeria. Since 2000 we have run health, education and livelihood programmes for women, girls and children.';
    $nav = [
        ['route' => 'about', 'label' => 'About us', 'match' => 'about'],
        ['route' => 'work', 'label' => 'Our work', 'match' => 'work*'],
        ['route' => 'projects.index', 'label' => 'Programmes', 'match' => 'projects.*'],
        ['route' => 'partners', 'label' => 'Partners', 'match' => 'partners'],
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
    <meta name="theme-color" content="#2b245a">

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="SWATCH">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDescription }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ $ogImage ?? asset('images/og-image.png') }}">
    <meta property="og:locale" content="en_NG">
    <meta name="twitter:card" content="summary_large_image">

    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">

    <link rel="preload" href="{{ asset('fonts/young-serif-latin-400-normal.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="{{ asset('fonts/figtree-latin-400-normal.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="{{ asset('css/site.css') }}?v={{ filemtime(public_path('css/site.css')) }}">

    <script type="application/ld+json">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'NGO',
        'name' => $org['name'],
        'alternateName' => 'SWATCH',
        'url' => url('/'),
        'logo' => asset('images/swatch-logo.png'),
        'foundingDate' => (string) $org['founded'],
        'email' => $org['contact']['email'],
        'telephone' => $org['contact']['phone_link'],
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => $org['contact']['address_line_1'],
            'addressLocality' => 'Kano',
            'addressRegion' => 'Kano State',
            'addressCountry' => 'NG',
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
    </script>
    @stack('head')
</head>
<body>
<a class="skip-link" href="#main">Skip to main content</a>

<div class="topbar">
    <div class="wrap">
        <span>Registered NGO, {{ $org['registration']['cac'] }}</span>
        <div class="topbar-contacts">
            <span><x-icon name="phone" :size="16" /><a href="tel:{{ $org['contact']['phone_link'] }}">{{ $org['contact']['phone'] }}</a></span>
            <span><x-icon name="mail" :size="16" /><a href="mailto:{{ $org['contact']['email'] }}">{{ $org['contact']['email'] }}</a></span>
        </div>
    </div>
</div>

<header class="site-header">
    <div class="wrap">
        <a href="{{ route('home') }}" class="brand" aria-label="SWATCH home">
            <img src="{{ asset('images/swatch-logo-180.png') }}" alt="" width="54" height="54">
            <span>
                <span class="brand-name">SWATCH</span>
                <span class="brand-sub">Support for Women and Teenage Children</span>
            </span>
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
            <h2 id="cta-heading">Work with us in Kano</h2>
            <p>We are open to funding partners, implementing partners and volunteers who share our goals for women, girls and children.</p>
        </div>
        <div class="btn-row">
            <a class="btn btn-white" href="{{ route('contact', ['type' => 'partnership']) }}#message">Talk to us about a partnership</a>
            <a class="btn btn-ghost-light" href="{{ route('involved') }}">Volunteer with us</a>
        </div>
    </div>
</section>
@endunless

<footer class="site-footer">
    <div class="footer-laurel">@include('partials.laurel')</div>
    <div class="wrap footer-main">
        <div>
            <div class="footer-brand">
                <img src="{{ asset('images/swatch-logo-180.png') }}" alt="" width="60" height="60" loading="lazy">
                <div>
                    <strong>SWATCH</strong>
                    <span>Support for Women and Teenage Children</span>
                </div>
            </div>
            <p>A women-led, non-governmental, non-partisan and not-for-profit organisation, working in Kano since {{ $org['founded'] }}.</p>
            <p class="small">CAC registration: {{ $org['registration']['cac'] }}<br>FIRS TIN: {{ $org['registration']['tin'] }}</p>
            @php $socials = array_filter($org['social']); @endphp
            @if ($socials)
                <div class="social">
                    @foreach ($socials as $network => $link)
                        <a href="{{ $link }}" target="_blank" rel="noopener" aria-label="SWATCH on {{ $network === 'x' ? 'X' : ucfirst($network) }}"><x-icon :name="$network" :size="20" /></a>
                    @endforeach
                </div>
            @endif
        </div>
        <div>
            <h2>Organisation</h2>
            <ul class="footer-links">
                <li><a href="{{ route('about') }}">About us</a></li>
                <li><a href="{{ route('about') }}#governance">Governance</a></li>
                <li><a href="{{ route('partners') }}">Partners</a></li>
                <li><a href="{{ route('news.index') }}">News</a></li>
                <li><a href="{{ route('privacy') }}">Privacy notice</a></li>
            </ul>
        </div>
        <div>
            <h2>Our work</h2>
            <ul class="footer-links">
                <li><a href="{{ route('work') }}">Thematic areas</a></li>
                <li><a href="{{ route('projects.index') }}">Programme record</a></li>
                <li><a href="{{ route('involved') }}">Volunteer</a></li>
                <li><a href="{{ route('support') }}">Support our work</a></li>
                <li><a href="{{ route('contact') }}">Contact us</a></li>
            </ul>
        </div>
        <div>
            <h2>Visit or call</h2>
            <ul class="footer-contact">
                <li><x-icon name="pin" :size="18" /><span>{{ $org['contact']['address_line_1'] }},<br>{{ $org['contact']['address_line_2'] }}, {{ $org['contact']['country'] }}</span></li>
                <li><x-icon name="phone" :size="18" /><a href="tel:{{ $org['contact']['phone_link'] }}">{{ $org['contact']['phone'] }}</a></li>
                <li><x-icon name="mail" :size="18" /><a href="mailto:{{ $org['contact']['email'] }}">{{ $org['contact']['email'] }}</a></li>
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
            <span>&copy; {{ date('Y') }} Support for Women and Teenage Children (SWATCH). All rights reserved.</span>
            <span>Plot 203, New Court Road, Gyadi-Gyadi, Kano</span>
        </div>
    </div>
</footer>

<script src="{{ asset('js/site.js') }}?v={{ filemtime(public_path('js/site.js')) }}" defer></script>
</body>
</html>
