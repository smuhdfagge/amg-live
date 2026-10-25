@extends('layouts.app', ['title' => 'About us', 'description' => 'The Aminu Magashi Garba Foundation is a non-profit based in Kano, Nigeria. Read our vision, mission and objectives.'])
@php $org = config('amg'); $c = $org['contact']; @endphp

@section('content')
@include('partials.page-head', [
    'heading' => 'About the Foundation',
    'intro' => 'The Aminu Magashi Garba Foundation is a non-profit organisation based in Kano, Nigeria. We exist to improve the lives of vulnerable people and communities.',
])

<section class="section">
    <div class="wrap split split-wide">
        <div class="prose">
            <h2>Who we are</h2>
            <p>Too many people in our communities carry burdens they cannot carry alone. A child with sickle cell disease whose family cannot afford the next hospital visit. A young person with no skills and no job. An older person living with illness and no income. A person with a disability shut out of school and work.</p>
            <p>The Aminu Magashi Garba Foundation was set up to change that. We focus on women, youths, children, the elderly, adolescents and people with disability, and on people living with health challenges such as sickle cell disease and other blood disorders, mental health problems, cancers and physical disabilities.</p>

            <h2>How we go about it</h2>
            <p>We do not believe any one organisation can solve these problems alone. So we build lasting partnerships with government, development partners, the private sector, community leaders and other civil society groups, and we work across sectors such as health, livelihoods and social protection.</p>
            <p>We also look hard at how support is paid for. Sustainable financial mechanisms and innovative solutions mean that help reaches more people and keeps going after the first round of funding ends.</p>
        </div>
        <aside>
            <div class="purpose">
                <div class="purpose-motif">@include('partials.motif')</div>
                <h3>Our vision</h3>
                <p>{{ $org['vision'] }}</p>
                <h3>Our mission</h3>
                <p class="mission">{{ $org['mission'] }}</p>
            </div>
        </aside>
    </div>
</section>

<section class="section section-mist anchor-offset" id="objectives">
    <div class="wrap split">
        <div>
            <h2>Our objectives</h2>
            <p class="lead">Two objectives guide every decision we make about where to work and what to fund.</p>
        </div>
        <ol class="objectives">
            @foreach ($org['objectives'] as $objective)
                <li>{{ $objective }}</li>
            @endforeach
        </ol>
    </div>
</section>

<section class="section">
    <div class="wrap">
        <div class="section-head">
            <div>
                <h2>Who we stand with</h2>
                <p>The groups named in our mission, and the people within them who are most often left behind.</p>
            </div>
        </div>
        <ul class="pill-list">
            @foreach ($org['groups'] as $group)<li>{{ $group }}</li>@endforeach
            <li>People with sickle cell and blood disorders</li>
            <li>People with mental health problems</li>
            <li>People living with cancer</li>
        </ul>
    </div>
</section>

@if ($timeline->isNotEmpty())
<section class="section section-mist">
    <div class="wrap">
        <div class="section-head">
            <div>
                <h2>Our programmes</h2>
                <p>The work we have done with our partners, most recent first.</p>
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
@endif

<section @class(['section', 'section-mist' => $timeline->isEmpty()])>
    <div class="wrap">
        <h2>Office and registration</h2>
        <p class="muted measure">Partners and donors can request copies of our registration documents and policies.</p>
        <dl class="registration">
            <div><x-icon name="pin" :size="28" /><div><dt>Head office</dt><dd>{{ $c['address_line_1'] }}, {{ $c['address_line_2'] }}</dd></div></div>
            @if (filled($org['registration']['cac']))
                <div><x-icon name="document" :size="28" /><div><dt>Corporate Affairs Commission</dt><dd>{{ $org['registration']['cac'] }}</dd></div></div>
            @endif
            @if (filled($org['registration']['tin']))
                <div><x-icon name="document" :size="28" /><div><dt>Federal Inland Revenue Service TIN</dt><dd>{{ $org['registration']['tin'] }}</dd></div></div>
            @endif
        </dl>
        <p class="mt-2"><a class="btn btn-primary" href="{{ route('contact', ['type' => 'partnership']) }}#message">Request our documents</a></p>
    </div>
</section>
@endsection
