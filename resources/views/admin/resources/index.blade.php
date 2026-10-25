@php use App\Models\Resource; @endphp
@extends('admin.layout', ['title' => 'Resources'])

@section('content')
<div class="admin-top">
    <h1>Resources</h1>
    <a class="btn btn-red btn-sm" href="{{ route('admin.resources.create', array_filter(['type' => $type])) }}"><x-icon name="plus" :size="18" /> Add {{ $type ? strtolower(Resource::TYPES[$type][0]) : 'resource' }}</a>
</div>

<nav class="filter-tabs" aria-label="Filter by type">
    <a href="{{ route('admin.resources.index') }}" @if (! $type) aria-current="page" @endif>All <span>{{ $counts->sum() }}</span></a>
    @foreach (Resource::TYPES as $slug => [$single, $plural, $icon])
        <a href="{{ route('admin.resources.index', ['type' => $slug]) }}" @if ($type === $slug) aria-current="page" @endif><x-icon :name="$icon" :size="18" /> {{ $plural }} <span>{{ $counts[$slug] ?? 0 }}</span></a>
    @endforeach
</nav>

<div class="panel table-wrap">
    @if ($resources->isEmpty())
        <p class="muted mb-0">Nothing here yet. Select Add to upload a radio programme, jingle or video.</p>
    @else
    <table class="data">
        <thead><tr><th>Title</th><th>Type</th><th>Source</th><th>Published</th><th></th></tr></thead>
        <tbody>
        @foreach ($resources as $resource)
            <tr>
                <td><a href="{{ route('admin.resources.edit', $resource) }}">{{ $resource->title }}</a>
                    @if ($resource->language || $resource->duration)<br><span class="small muted">{{ collect([$resource->language, $resource->duration])->filter()->implode(' · ') }}</span>@endif
                </td>
                <td>{{ $resource->typeLabel() }}</td>
                <td class="small">
                    @if ($resource->media_file) Uploaded file
                    @elseif ($resource->youtubeId()) YouTube
                    @elseif ($resource->vimeoId()) Vimeo
                    @else Link @endif
                </td>
                <td>{{ $resource->published_at ? $resource->published_at->format('j M Y') : 'Draft' }}@if ($resource->published_at?->isFuture())<br><span class="small muted">Scheduled</span>@endif</td>
                <td><div class="actions">
                    <a class="icon-btn" href="{{ route('admin.resources.edit', $resource) }}"><x-icon name="edit" :size="16" /> Edit</a>
                    <form method="POST" action="{{ route('admin.resources.destroy', $resource) }}" data-confirm="Delete this resource and its uploaded file? This cannot be undone.">@csrf @method('DELETE')<button class="icon-btn danger" type="submit"><x-icon name="trash" :size="16" /> Delete</button></form>
                </div></td>
            </tr>
        @endforeach
        </tbody>
    </table>
    {{ $resources->links('partials.pagination') }}
    @endif
</div>
@endsection
