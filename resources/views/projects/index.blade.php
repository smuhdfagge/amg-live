@extends('layouts.app', ['title' => 'Programmes', 'description' => 'Programmes run by the Aminu Magashi Garba Foundation, with partners, locations and results.'])

@section('content')
@include('partials.page-head', [
    'heading' => 'Our programmes',
    'intro' => 'Our programmes, most recent first, with the partners we work with and the results we report.',
])

<section class="section">
    <div class="wrap">
        @if ($projects->isEmpty())
            <div class="split">
                <div>
                    <h2>Programmes are on the way</h2>
                    <p class="lead">Details of our programmes will appear here as they are published.</p>
                </div>
                <div>
                    <p>In the meantime, read about the <a href="{{ route('work') }}">areas we focus on</a>, or <a href="{{ route('contact', ['type' => 'partnership']) }}#message">talk to us</a> about working together.</p>
                </div>
            </div>
        @endif
        <ol class="ledger">
            @foreach ($projects as $project)
                <li class="ledger-item">
                    <div class="ledger-years">
                        {{ $project->period }}
                        @if ($project->status !== 'recent')<br><span @class(['status', 'status-ongoing' => $project->status === 'ongoing'])>{{ $project->statusLabel() }}</span>@endif
                    </div>
                    <div>
                        <h2><a href="{{ route('projects.show', $project) }}">{{ $project->displayName() }}</a></h2>
                        @if ($project->short_title)<p class="ledger-full">{{ $project->title }}</p>@endif
                        <div class="ledger-meta">
                            @if ($project->partner)<span><x-icon name="handshake" :size="18" /> {{ $project->partner }}</span>@endif
                            @if ($project->location)<span><x-icon name="pin" :size="18" /> {{ $project->location }}</span>@endif
                        </div>
                        <p>{{ $project->summary }}</p>
                        <a class="text-link" href="{{ route('projects.show', $project) }}">Programme details<span class="sr-only">: {{ $project->displayName() }}</span></a>
                    </div>
                    <div class="ledger-figure">
                        @if ($project->reach_figure)<strong>{{ $project->reach_figure }}</strong><span>{{ $project->reach_label }}</span>@endif
                    </div>
                </li>
            @endforeach
        </ol>
    </div>
</section>
@endsection
