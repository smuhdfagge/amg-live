@extends('layouts.app', ['title' => 'About us', 'description' => 'SWATCH is a women-led NGO founded in Kano in 2000. Read our story, vision, mission, governance and registration details.'])
@php $org = config('swatch'); @endphp

@section('content')
@include('partials.page-head', [
    'heading' => 'About SWATCH',
    'intro' => 'Support for Women and Teenage Children is a non-governmental, not-for-profit, non-partisan and non-political organisation, founded in 2000 and headquartered in Kano, Northern Nigeria.',
])

<section class="section">
    <div class="wrap split split-wide">
        <div class="prose">
            <h2>How we started</h2>
            <p>SWATCH was formed by a group of professional women and men who were concerned about what the HIV and AIDS epidemic was doing to families in Kano. They were equally worried about the state of education: falling standards, low enrolment of girls, and too few girls moving on to the next level or finishing school at all.</p>
            <p>The founders were also concerned about extreme poverty among disadvantaged women, the distress of orphans and vulnerable children, drug and substance abuse among young people, and the lack of business skills among the most vulnerable. These concerns still shape the work we take on.</p>

            <h2>Who we are today</h2>
            <p>We are a women-led organisation, headed by women and working for women, girls, children and the general population. We are gender-sensitive, and we mainstream gender and social inclusion in everything we do so that no one is left out of community participation.</p>
            <p>Our primary aim is to raise the living standards of women, children and young people so they become self-reliant and dignified members of their communities. We treat health as complete physical, mental and social wellbeing, and we pursue it through advocacy, community mobilisation, programme management, research, capacity building, outreach and monitoring.</p>
        </div>
        <aside>
            <div class="purpose">
                <div class="purpose-laurel">@include('partials.laurel')</div>
                <h3>Our vision</h3>
                <p>{{ $org['vision'] }}</p>
                <h3>Our mission</h3>
                <p class="mission">{{ $org['mission'] }}</p>
            </div>
        </aside>
    </div>
</section>

<section class="section section-mist anchor-offset" id="governance">
    <div class="wrap">
        <div class="section-head">
            <div>
                <h2>Governance and people</h2>
                <p>A board of trustees sets our direction and holds management to account. An executive director leads a senior management team, staff and a large network of trained volunteers.</p>
            </div>
        </div>
        <div class="people">
            <div><strong>{{ $org['team']['trustees'] }}</strong><span>members of the Board of Trustees</span></div>
            <div><strong>{{ $org['team']['senior_management'] }}</strong><span>members of the senior management team, led by the Executive Director</span></div>
            <div><strong>{{ $org['team']['permanent_staff'] }}</strong><span>permanent skilled staff</span></div>
            <div><strong>{{ $org['team']['temporary_staff'] }}</strong><span>temporary staff</span></div>
            <div><strong>{{ $org['team']['volunteers'] }}+</strong><span>volunteers</span></div>
        </div>

        <div class="split mt-2" style="margin-top:3rem">
            <div>
                <h3>Skills across our team</h3>
                <p class="muted">We draw on experts from different professional backgrounds and on volunteers with the right skills and experience to manage programmes.</p>
                <ul class="pill-list">
                    @foreach ($org['skills'] as $skill)<li>{{ $skill }}</li>@endforeach
                </ul>
            </div>
            <div>
                <h3>Equality policy</h3>
                <p class="muted">Our equality policy commits us to make sure there is no unjustified discrimination in how we recruit, keep, train and develop our staff. The same principle guides who we enrol in our programmes.</p>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="wrap">
        <div class="section-head">
            <div>
                <h2>Our record over the years</h2>
                <p>We have delivered HIV and orphans and vulnerable children programmes in Kano since 2007, working alongside national and international organisations.</p>
            </div>
            <a class="text-link" href="{{ route('projects.index') }}">Full programme details</a>
        </div>
        <ol class="ledger">
            @foreach ($timeline as $project)
                <li class="ledger-item">
                    <div class="ledger-years">{{ $project->period }}</div>
                    <div>
                        <h2 style="font-size:1.35rem"><a href="{{ route('projects.show', $project) }}">{{ $project->displayName() }}</a></h2>
                        <p class="ledger-full">{{ $project->partner ? 'With '.$project->partner : $project->title }}</p>
                        <p class="mb-0">{{ $project->summary }}</p>
                    </div>
                    <div class="ledger-figure">
                        @if ($project->reach_figure)<strong>{{ $project->reach_figure }}</strong><span>{{ $project->reach_label }}</span>@endif
                    </div>
                </li>
            @endforeach
        </ol>
    </div>
</section>

<section class="section section-mist">
    <div class="wrap">
        <h2>Registration</h2>
        <p class="muted measure">SWATCH is registered at federal level. Partners and donors can request copies of our registration documents, audited accounts and policies.</p>
        <dl class="registration">
            <div><x-icon name="document" :size="28" /><div><dt>Corporate Affairs Commission</dt><dd>{{ $org['registration']['cac'] }}</dd></div></div>
            <div><x-icon name="document" :size="28" /><div><dt>Federal Inland Revenue Service TIN</dt><dd>{{ $org['registration']['tin'] }}</dd></div></div>
            <div><x-icon name="pin" :size="28" /><div><dt>Head office</dt><dd>{{ $org['contact']['address_line_1'] }}, {{ $org['contact']['address_line_2'] }}</dd></div></div>
        </dl>
        <p class="mt-2"><a class="btn btn-indigo" href="{{ route('contact', ['type' => 'partnership']) }}#message">Request our documents</a></p>
    </div>
</section>
@endsection
