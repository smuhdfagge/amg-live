<a class="programme" href="{{ route('projects.show', $project) }}">
    @if ($project->coverUrl())
        <img class="programme-cover" src="{{ $project->coverUrl() }}" alt="" loading="lazy">
    @endif
    <div class="programme-top">
        <span class="programme-period">{{ $project->period }}</span>
        @if ($project->status === 'ongoing') <span class="status status-ongoing">Ongoing</span> @endif
        <h3>{{ $project->displayName() }}</h3>
        @if ($project->short_title)<p class="programme-title">{{ $project->title }}</p>@endif
    </div>
    <div class="programme-body">
        <p>{{ $project->summary }}</p>
        <dl class="programme-facts">
            @if ($project->partner)<div><dt>With</dt><dd>{{ $project->partner }}</dd></div>@endif
            @if ($project->location)<div><dt>Where</dt><dd>{{ $project->location }}</dd></div>@endif
        </dl>
    </div>
    @if ($project->reach_figure)
        <div class="programme-reach"><strong>{{ $project->reach_figure }}</strong><span>{{ $project->reach_label }}</span></div>
    @endif
</a>
