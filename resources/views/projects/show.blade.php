@extends('layouts.app', ['title' => $project->displayName(), 'description' => $project->summary, 'ogImage' => $project->coverUrl()])

@section('content')
@include('partials.page-head', [
    'heading' => $project->displayName(),
    'intro' => $project->short_title ? $project->title : null,
    'crumbs' => ['Programmes' => route('projects.index')],
])

<section class="section">
    <div class="wrap split split-wide">
        <div>
            @if ($project->coverUrl())
                <div class="article-cover"><img src="{{ $project->coverUrl() }}" alt=""></div>
            @endif
            <div class="prose">
                <p class="lead" style="color:var(--ink)">{{ $project->summary }}</p>
                @include('partials.blocks', ['blocks' => $project->bodyBlocks()])
            </div>

            <nav class="pager" aria-label="More programmes">
                <div>
                    @if ($previous)
                        <a href="{{ route('projects.show', $previous) }}"><span><x-icon name="arrow-left" :size="16" /> Newer programme</span><strong>{{ $previous->displayName() }}</strong></a>
                    @endif
                </div>
                <div class="next">
                    @if ($next)
                        <a href="{{ route('projects.show', $next) }}"><span>Earlier programme <x-icon name="arrow-right" :size="16" /></span><strong>{{ $next->displayName() }}</strong></a>
                    @endif
                </div>
            </nav>
        </div>

        <aside>
            <div class="facts sticky-aside">
                <h2>At a glance</h2>
                @if ($project->reach_figure)
                    <p style="margin-bottom:1.25rem"><span class="facts-figure">{{ $project->reach_figure }}</span><span class="muted">{{ $project->reach_label }}</span></p>
                @endif
                <dl>
                    <dt>Period</dt><dd>{{ $project->period }}</dd>
                    <dt>Status</dt><dd>{{ $project->statusLabel() }}</dd>
                    @if ($project->partner)<dt>{{ $project->partner_role ?: 'Partner' }}</dt><dd>{{ $project->partner }}</dd>@elseif ($project->partner_role)<dt>Our role</dt><dd>{{ $project->partner_role }}</dd>@endif
                    @if ($project->location)<dt>Location</dt><dd>{{ $project->location }}</dd>@endif
                    @if ($project->theme)<dt>Focus</dt><dd>{{ $project->theme }}</dd>@endif
                </dl>
                <p class="mt-2 mb-0"><a class="btn btn-indigo btn-sm" href="{{ route('contact', ['type' => 'partnership']) }}#message">Talk to us about similar work</a></p>
            </div>
        </aside>
    </div>
</section>
@endsection
