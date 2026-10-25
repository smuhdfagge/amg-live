@extends('layouts.app', ['title' => $area['title'], 'description' => $area['summary']])

@section('content')
@include('partials.page-head', [
    'heading' => $area['title'],
    'intro' => $area['summary'],
    'crumbs' => ['Our work' => route('work')],
])

<section class="section">
    <div class="wrap split split-wide">
        <div class="prose">
            <p style="font-size:1.15rem">{{ $area['body'] }}</p>

            @if ($projects->isNotEmpty())
                <h2>Related programmes</h2>
                <ol class="ledger">
                    @foreach ($projects as $project)
                        <li class="ledger-item" style="grid-template-columns: 9rem minmax(0,1fr)">
                            <div class="ledger-years" style="font-size:1.1rem">{{ $project->period }}</div>
                            <div>
                                <h3 style="margin-bottom:.25rem"><a href="{{ route('projects.show', $project) }}">{{ $project->displayName() }}</a></h3>
                                <p class="mb-0 muted">{{ $project->summary }}</p>
                            </div>
                        </li>
                    @endforeach
                </ol>
            @else
                <h2>Working in this area</h2>
                <p>We are building our programme portfolio in this area and welcome conversations with partners who share the goal.</p>
                <p><a class="btn btn-indigo" href="{{ route('contact', ['type' => 'partnership']) }}#message">Discuss a partnership</a></p>
            @endif
        </div>
        <aside>
            <div class="facts sticky-aside">
                <h2>Other areas</h2>
                <div class="areas" style="grid-template-columns:1fr;gap:1.25rem">
                    @foreach ($others as $other)
                        @include('partials.area-item', ['area' => $other])
                    @endforeach
                </div>
                <p class="mt-2 mb-0"><a class="text-link" href="{{ route('work') }}">All thematic areas</a></p>
            </div>
        </aside>
    </div>
</section>
@endsection
