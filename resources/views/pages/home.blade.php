@extends('layouts.app')
@php $org = config('amg'); @endphp

@section('content')
<section class="hero">
    <div class="hero-motif">@include('partials.motif')</div>
    <div class="wrap">
        <div>
            <p class="eyebrow">Aminu Magashi Garba Foundation</p>
            <h1>Better lives for vulnerable people and communities</h1>
            <p class="lead">We build lasting partnerships across sectors, and find sustainable financing and new solutions, to meet the needs of women, youths, children, the elderly, adolescents and people with disability.</p>
            <div class="btn-row">
                <a class="btn btn-primary" href="{{ route('work') }}">See what we do</a>
                <a class="btn btn-outline" href="{{ route('contact', ['type' => 'partnership']) }}#message">Partner with us</a>
            </div>
            <div class="hero-meta">
                <span><x-icon name="pin" :size="18" /> Nassarawa GRA, Kano, Nigeria</span>
                @if (filled($org['registration']['cac']))<span><x-icon name="document" :size="18" /> {{ $org['registration']['cac'] }}</span>@endif
            </div>
        </div>

        <aside class="record" aria-labelledby="serve-heading">
            <h2 id="serve-heading">Who we stand with</h2>
            <p>The people our mission puts first.</p>
            <ul class="serve-list">
                @foreach ($org['groups'] as $group)
                    <li><x-icon name="check" :size="20" /> {{ $group }}</li>
                @endforeach
            </ul>
            <div class="record-foot"><a class="text-link" href="{{ route('about') }}#objectives">Read our objectives</a></div>
        </aside>
    </div>
</section>

<section class="section-green" aria-label="Our approach in brief">
    <div class="wrap">
        <div class="reach reach-words">
            <div><strong>Partnerships</strong><span>lasting, multi-stakeholder and multi-sectoral</span></div>
            <div><strong>Financing</strong><span>sustainable mechanisms that keep support going</span></div>
            <div><strong>Innovation</strong><span>new solutions to old and stubborn problems</span></div>
            <div><strong>Response</strong><span>adequate and timely, in development and in emergencies</span></div>
        </div>
    </div>
</section>

<section class="section">
    <div class="wrap">
        <div class="section-head">
            <div>
                <h2>What we work on</h2>
                <p>Our objectives point us to two kinds of work: empowering the most vulnerable people, and easing the suffering of people living with ill health and disability.</p>
            </div>
            <a class="text-link" href="{{ route('work') }}">All focus areas</a>
        </div>
        <div class="areas">
            @foreach ($areas as $area)
                @include('partials.area-item', ['area' => $area])
            @endforeach
        </div>
    </div>
</section>

<section class="section section-mist">
    <div class="wrap split split-wide">
        <div>
            <h2>Our objectives</h2>
            <ol class="objectives">
                @foreach ($org['objectives'] as $objective)
                    <li>{{ $objective }}</li>
                @endforeach
            </ol>
            <a class="btn btn-primary" href="{{ route('about') }}">More about the Foundation</a>
        </div>
        <div class="purpose">
            <div class="purpose-motif">@include('partials.motif')</div>
            <h3>Our vision</h3>
            <p>{{ $org['vision'] }}</p>
            <h3>Our mission</h3>
            <p class="mission">{{ $org['mission'] }}</p>
        </div>
    </div>
</section>

@if ($featured->isNotEmpty())
<section class="section">
    <div class="wrap">
        <div class="section-head">
            <div>
                <h2>Our programmes</h2>
                <p>Some of the work we are doing with our partners.</p>
            </div>
            <a class="text-link" href="{{ route('projects.index') }}">All programmes</a>
        </div>
        <div class="programmes">
            @foreach ($featured as $project)
                @include('partials.programme-card', ['project' => $project])
            @endforeach
        </div>
    </div>
</section>
@endif

<section @class(['section', 'section-mist' => $featured->isNotEmpty()])>
    <div class="wrap">
        <div class="section-head">
            <div>
                <h2>Ways to help</h2>
                <p>Whether you represent a government agency, a donor, a company or yourself, there is a place for you in this work.</p>
            </div>
        </div>
        <div class="ways">
            <div class="way">
                <span class="way-icon"><x-icon name="handshake" :size="26" /></span>
                <h3>Partner with us</h3>
                <p>Partnership is at the heart of our mission. Talk to us about designing, funding or delivering work together.</p>
                <a class="text-link" href="{{ route('contact', ['type' => 'partnership']) }}#message">Start a conversation</a>
            </div>
            <div class="way">
                <span class="way-icon"><x-icon name="hand" :size="26" /></span>
                <h3>Volunteer</h3>
                <p>Give your time and skills in the community, in the office or online.</p>
                <a class="text-link" href="{{ route('involved') }}#volunteer">Apply to volunteer</a>
            </div>
            <div class="way">
                <span class="way-icon"><x-icon name="gift" :size="26" /></span>
                <h3>Give</h3>
                <p>Help a patient get treatment, a young person learn a skill or a family through a crisis. Every gift is accounted for.</p>
                <a class="text-link" href="{{ route('support') }}">Ways to give</a>
            </div>
        </div>
    </div>
</section>

@if ($resources->isNotEmpty())
<section @class(['section', 'section-mist' => $featured->isEmpty()])>
    <div class="wrap">
        <div class="section-head">
            <div>
                <h2>Listen and watch</h2>
                <p>Our latest radio programmes, jingles and videos.</p>
            </div>
            <a class="text-link" href="{{ route('resources.index') }}">All resources</a>
        </div>
        <div class="resource-grid">
            @foreach ($resources as $resource)
                @include('resources.card', ['resource' => $resource])
            @endforeach
        </div>
    </div>
</section>
@endif

@if ($posts->isNotEmpty())
<section class="section">
    <div class="wrap">
        <div class="section-head">
            <div>
                <h2>News</h2>
                <p>Updates from the Foundation and its partners.</p>
            </div>
            <a class="text-link" href="{{ route('news.index') }}">All news</a>
        </div>
        <div class="news-grid">
            @foreach ($posts as $post)
                @include('partials.news-card', ['post' => $post])
            @endforeach
        </div>
    </div>
</section>
@endif
@endsection
