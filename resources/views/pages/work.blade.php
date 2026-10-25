@extends('layouts.app', ['title' => 'Our work', 'description' => 'What the Aminu Magashi Garba Foundation works on: sickle cell and blood disorders, mental health, cancer, disability, women, youths, children, the elderly, innovative financing and humanitarian response.'])

@section('content')
@include('partials.page-head', [
    'heading' => 'Our work',
    'intro' => 'We empower the most vulnerable people in society and work to ease the suffering of people living with ill health and disability.',
])

<section class="section">
    <div class="wrap">
        <div class="section-head">
            <div>
                <h2>Focus areas</h2>
                <p>Select an area to read more about it and see related programmes.</p>
            </div>
        </div>
        <div class="areas">
            @foreach ($areas as $area)
                @include('partials.area-item', ['area' => $area])
            @endforeach
        </div>
    </div>
</section>

<section class="section section-mist">
    <div class="wrap">
        <div class="section-head">
            <div>
                <h2>How we work</h2>
                <p>The same principles run through everything we do, whatever the focus area.</p>
            </div>
        </div>
        <div class="methods">
            @foreach (config('amg.methods') as $method)
                <div>
                    <h3>{{ $method['title'] }}</h3>
                    <p>{{ $method['text'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="section">
    <div class="wrap split">
        <div>
            <h2>Our approach</h2>
            <p class="lead">Three principles decide how we design and run a programme.</p>
        </div>
        <div class="stack">
            <div>
                <h3>People first</h3>
                <p class="muted">We start from the needs of the people we serve and treat every person with dignity, whatever their age, health or ability.</p>
            </div>
            <div>
                <h3>Together with others</h3>
                <p class="muted">We work with communities, government and partners, so that each brings what it does best and the results last.</p>
            </div>
            <div>
                <h3>Built to last</h3>
                <p class="muted">We favour long-term answers that build local skills and structures over short-term handouts.</p>
            </div>
        </div>
    </div>
</section>
@endsection
