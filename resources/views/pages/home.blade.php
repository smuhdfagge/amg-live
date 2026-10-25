@extends('layouts.app')

@section('content')
<section class="hero">
    <div class="hero-laurel">@include('partials.laurel')</div>
    <div class="wrap">
        <div>
            <h1>Support for Women and Teenage Children</h1>
            <p class="lead">SWATCH is a women-led, non-profit organisation. We run health, education and livelihood programmes for the families who need them most, and we have done this work with partners such as FHI 360, Chemonics International and the Society for Family Health.</p>
            <div class="btn-row">
                <a class="btn btn-indigo" href="{{ route('projects.index') }}">See our programme record</a>
                <a class="btn btn-outline" href="{{ route('contact', ['type' => 'partnership']) }}#message">Partner with us</a>
            </div>
            <div class="hero-meta">
                <span><x-icon name="pin" :size="18" /> Headquartered in Kano, Northern Nigeria</span>
                <span><x-icon name="document" :size="18" /> {{ config('swatch.registration.cac') }}</span>
            </div>
        </div>

        <aside class="record" aria-labelledby="record-heading">
            <h2 id="record-heading">Our programme record</h2>
            <p>Major programmes we have delivered, most recent first.</p>
            <ol>
                @foreach ($timeline as $project)
                    <li @class(['is-current' => $loop->first])>
                        <a href="{{ route('projects.show', $project) }}">
                            <span class="record-years">{{ $project->period }}</span>
                            <span class="record-name">{{ $project->displayName() }}</span>
                            @if ($project->partner)<span class="record-partner">with {{ $project->partner }}</span>@endif
                        </a>
                    </li>
                @endforeach
            </ol>
            <div class="record-foot"><a class="text-link" href="{{ route('projects.index') }}">Read about each programme</a></div>
        </aside>
    </div>
</section>

<section class="section-indigo" aria-label="Our reach">
    <div class="wrap">
        <div class="reach">
            <div><strong>28,500+</strong><span>orphans, vulnerable children and caregivers reached through three of our programmes</span></div>
            <div><strong>9</strong><span>local government areas covered by SIDHAS, our longest programme</span></div>
            <div><strong>22</strong><span>health facilities we worked with during the national AIDS survey</span></div>
            <div><strong>80+</strong><span>volunteers working alongside our permanent staff</span></div>
        </div>
    </div>
</section>

<section class="section">
    <div class="wrap">
        <div class="section-head">
            <div>
                <h2>What we work on</h2>
                <p>Health and the care of orphans and vulnerable children are where our experience runs deepest. Around them we work on the wider conditions that keep families poor or unwell.</p>
            </div>
            <a class="text-link" href="{{ route('work') }}">How we work</a>
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
            <h2>Led by women, working for everyone</h2>
            <div class="measure">
                <p>SWATCH was formed by a group of professional women and men who were worried about the toll HIV and AIDS was taking on families in Kano, and about the number of girls who never enrolled in school or dropped out before finishing.</p>
                <p>Today we are headed by women and we work with women, girls, children and the wider community. We are non-governmental, non-partisan, non-religious and not-for-profit, and we are registered with the Corporate Affairs Commission and the Federal Inland Revenue Service.</p>
            </div>
            <ul class="checklist">
                <li><x-icon name="check" :size="20" /> A five-member Board of Trustees and a five-member senior management team</li>
                <li><x-icon name="check" :size="20" /> Nine permanent staff, three temporary staff and more than 80 volunteers</li>
                <li><x-icon name="check" :size="20" /> An equality policy that covers how we recruit, train and develop our own staff</li>
            </ul>
            <a class="btn btn-indigo" href="{{ route('about') }}">More about SWATCH</a>
        </div>
        <div class="purpose">
            <div class="purpose-laurel">@include('partials.laurel')</div>
            <h3>Our vision</h3>
            <p>{{ config('swatch.vision') }}</p>
            <h3>Our mission</h3>
            <p class="mission">{{ config('swatch.mission') }}</p>
        </div>
    </div>
</section>

@if ($featured->isNotEmpty())
<section class="section">
    <div class="wrap">
        <div class="section-head">
            <div>
                <h2>Recent programmes</h2>
                <p>Each programme was delivered with an international or national partner, to agreed targets and reporting standards.</p>
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

<section class="section section-mist">
    <div class="wrap">
        <div class="section-head">
            <div>
                <h2>Ways to help</h2>
                <p>Whether you represent a donor, a company or yourself, there is a place for you in this work.</p>
            </div>
        </div>
        <div class="ways">
            <div class="way">
                <span class="way-icon"><x-icon name="handshake" :size="26" /></span>
                <h3>Partner with us</h3>
                <p>Funding and implementing partners can draw on our community structures, trained volunteers and long experience of donor-funded programmes in Kano.</p>
                <a class="text-link" href="{{ route('contact', ['type' => 'partnership']) }}#message">Start a conversation</a>
            </div>
            <div class="way">
                <span class="way-icon"><x-icon name="hand" :size="26" /></span>
                <h3>Volunteer</h3>
                <p>Give your time and skills in the field, in the office or online. Our volunteers are part of every programme we run.</p>
                <a class="text-link" href="{{ route('involved') }}#volunteer">Apply to volunteer</a>
            </div>
            <div class="way">
                <span class="way-icon"><x-icon name="gift" :size="26" /></span>
                <h3>Give</h3>
                <p>Support a child's schooling, a caregiver's small business or a community health outreach. Every gift is accounted for.</p>
                <a class="text-link" href="{{ route('support') }}">Ways to give</a>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="wrap">
        <div class="section-head">
            <div>
                <h2>News</h2>
                <p>Updates from our programmes, and coverage of the issues we work on.</p>
            </div>
            <a class="text-link" href="{{ route('news.index') }}">All news</a>
        </div>
        @if ($posts->isEmpty())
            <p class="empty">News from our programmes will appear here.</p>
        @else
            <div class="news-grid">
                @foreach ($posts as $post)
                    @include('partials.news-card', ['post' => $post])
                @endforeach
            </div>
        @endif
    </div>
</section>
@endsection
