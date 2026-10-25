@extends('layouts.app', ['title' => 'Our work', 'description' => 'The thematic areas SWATCH works on in Kano: health, orphans and vulnerable children, education, nutrition, inclusion, livelihoods, WASH, rights and governance.'])

@section('content')
@include('partials.page-head', [
    'heading' => 'Our work',
    'intro' => 'We run evidence-based programmes in health, education and community development. Our longest experience is in HIV services and in the care of orphans and vulnerable children.',
])

<section class="section">
    <div class="wrap">
        <div class="section-head">
            <div>
                <h2>Thematic areas</h2>
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
                <p>The same set of methods runs through every programme, whatever the theme.</p>
            </div>
        </div>
        <div class="methods">
            @foreach (config('swatch.methods') as $method)
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
                <h3>Evidence first</h3>
                <p class="muted">We ground our programmes in research and proven practice, and we measure results so we can show what worked and fix what did not.</p>
            </div>
            <div>
                <h3>Led by the community</h3>
                <p class="muted">We work with communities as partners. Ward leaders, caregivers and young people help shape the solutions, and they stay involved after we leave.</p>
            </div>
            <div>
                <h3>Built to last</h3>
                <p class="muted">We favour long-term answers that build local skills and structures over short-term handouts.</p>
            </div>
        </div>
    </div>
</section>
@endsection
