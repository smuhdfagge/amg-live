@php
    use App\Models\Resource;
    $current = $type ? Resource::TYPES[$type][1] : null;
@endphp
@extends('layouts.app', [
    'title' => $current ? $current.' | Resources' : 'Resources',
    'description' => 'Radio programmes, audio jingles and videos from the Aminu Magashi Garba Foundation. Listen, watch and share.',
])

@section('content')
@include('partials.page-head', [
    'heading' => $current ?? 'Resources',
    'intro' => 'Radio programmes, audio jingles and videos from the Foundation. Listen, watch and share them with your community.',
    'crumbs' => $current ? ['Resources' => route('resources.index')] : [],
])

<section class="section">
    <div class="wrap">
        <nav class="filter-tabs" aria-label="Filter resources">
            <a href="{{ route('resources.index') }}" @if (! $type) aria-current="page" @endif>All <span>{{ $counts->sum() }}</span></a>
            @foreach (Resource::TYPES as $slug => [$single, $plural, $icon])
                <a href="{{ route('resources.index', ['type' => $slug]) }}" @if ($type === $slug) aria-current="page" @endif>
                    <x-icon :name="$icon" :size="18" /> {{ $plural }} <span>{{ $counts[$slug] ?? 0 }}</span>
                </a>
            @endforeach
        </nav>

        @if ($resources->isEmpty())
            <p class="empty">
                @if ($type)
                    There are no {{ strtolower($current) }} yet. Check back soon, or <a href="{{ route('resources.index') }}">see all resources</a>.
                @else
                    Our radio programmes, jingles and videos will appear here soon.
                @endif
            </p>
        @else
            <div class="resource-grid">
                @foreach ($resources as $resource)
                    @include('resources.card', ['resource' => $resource])
                @endforeach
            </div>
            {{ $resources->links('partials.pagination') }}
        @endif
    </div>
</section>
@endsection
