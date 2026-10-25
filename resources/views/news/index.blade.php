@extends('layouts.app', ['title' => 'News', 'description' => 'News and updates from SWATCH programmes in Kano, and press coverage of the issues we work on.'])

@section('content')
@include('partials.page-head', [
    'heading' => 'News',
    'intro' => 'Updates from our programmes, announcements, and press coverage of the issues we work on.',
])

<section class="section">
    <div class="wrap">
        @if ($posts->isEmpty())
            <p class="empty">There is no news yet. Subscribe at the bottom of this page to hear from us when there is.</p>
        @else
            <div class="news-grid">
                @foreach ($posts as $post)
                    @include('partials.news-card', ['post' => $post])
                @endforeach
            </div>
            {{ $posts->links('partials.pagination') }}
        @endif
    </div>
</section>
@endsection
