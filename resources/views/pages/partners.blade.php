@extends('layouts.app', ['title' => 'Partners', 'description' => 'How the Aminu Magashi Garba Foundation works with government, development partners, companies and communities, and the organisations we work with.'])

@section('content')
@include('partials.page-head', [
    'heading' => 'Our partners',
    'intro' => 'Lasting, multi-stakeholder partnerships are at the heart of our mission. No single organisation can meet the needs of vulnerable people alone.',
])

<section class="section">
    <div class="wrap">
        @if ($projects->isEmpty())
            <div class="split">
                <div>
                    <h2>Who we work with</h2>
                    <p class="lead">We work with anyone who shares our goal of better lives for vulnerable people.</p>
                </div>
                <ul class="checklist">
                    <li><x-icon name="check" :size="20" /> Federal, state and local government agencies</li>
                    <li><x-icon name="check" :size="20" /> International and national development partners</li>
                    <li><x-icon name="check" :size="20" /> Hospitals, clinics and health professionals</li>
                    <li><x-icon name="check" :size="20" /> Companies and their foundations</li>
                    <li><x-icon name="check" :size="20" /> Community, traditional and religious leaders</li>
                    <li><x-icon name="check" :size="20" /> Other civil society and community-based organisations</li>
                </ul>
            </div>
        @endif
        <div class="partner-list">
            @foreach ($projects->groupBy('partner') as $partner => $items)
                @php
                    preg_match('/\(([^)]+)\)/', $partner, $m);
                    $abbr = $m[1] ?? (str_contains($partner, 'FHI') ? 'FHI 360' : collect(explode(' ', $partner))->map(fn ($w) => $w[0])->take(3)->implode(''));
                    $name = trim(preg_replace('/\s*\([^)]+\)/', '', $partner));
                @endphp
                <article class="partner">
                    <div class="partner-mark" aria-hidden="true">{{ $abbr }}</div>
                    <div>
                        <h2>{{ $name }}</h2>
                        <p class="partner-role">{{ $items->first()->partner_role }}</p>
                        @foreach ($items as $project)
                            <p>
                                <strong>{{ $project->displayName() }}</strong> ({{ $project->period }}).
                                {{ $project->summary }}
                                <a href="{{ route('projects.show', $project) }}">Read more<span class="sr-only"> about {{ $project->displayName() }}</span></a>
                            </p>
                        @endforeach
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>

<section class="section section-mist">
    <div class="wrap split">
        <div>
            <h2>Why partner with us</h2>
            <p class="lead">We bring a clear focus, local roots and a commitment to support that lasts.</p>
            <p><a class="btn btn-primary" href="{{ route('contact', ['type' => 'partnership']) }}#message">Talk to us about a partnership</a></p>
        </div>
        <ul class="checklist">
            <li><x-icon name="check" :size="20" /> Based in Kano and close to the communities we serve</li>
            <li><x-icon name="check" :size="20" /> A clear focus on the most vulnerable: women, youths, children, the elderly, adolescents and people with disability</li>
            <li><x-icon name="check" :size="20" /> Work across health, livelihoods and social protection</li>
            <li><x-icon name="check" :size="20" /> A focus on sustainable financing, so results outlast a single grant</li>
            <li><x-icon name="check" :size="20" /> Open reporting on what we spend and what changes</li>
        </ul>
    </div>
</section>
@endsection
