@extends('layouts.app', ['title' => 'Partners', 'description' => 'The organisations SWATCH has delivered programmes with, including Chemonics International, FHI 360, the Society for Family Health, HIFASS and CEDPA.'])

@section('content')
@include('partials.page-head', [
    'heading' => 'Our partners',
    'intro' => 'Our programmes have been delivered with national and international organisations. Each partnership has left behind trained people and stronger community structures in Kano.',
])

<section class="section">
    <div class="wrap">
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
            <h2>Why partner with SWATCH</h2>
            <p class="lead">We know the communities of Kano, and we have the systems to deliver and report on donor-funded work.</p>
        </div>
        <ul class="checklist">
            <li><x-icon name="check" :size="20" /> More than two decades of programme delivery in Kano State</li>
            <li><x-icon name="check" :size="20" /> Experience across HIV prevention, testing, referral and OVC care</li>
            <li><x-icon name="check" :size="20" /> Community structures and more than 80 trained volunteers</li>
            <li><x-icon name="check" :size="20" /> Staff skilled in monitoring and evaluation, financial management and database management</li>
            <li><x-icon name="check" :size="20" /> Federal registration with the CAC and FIRS</li>
            <li><x-icon name="check" :size="20" /> A women-led team with a clear gender and inclusion policy</li>
        </ul>
    </div>
</section>
@endsection
