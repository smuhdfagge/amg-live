@extends('layouts.app', ['title' => $post->title, 'description' => $post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags((string) $post->body), 155), 'ogImage' => $post->coverUrl()])

@section('content')
@include('partials.page-head', [
    'heading' => $post->title,
    'crumbs' => ['News' => route('news.index')],
    'current' => \Illuminate\Support\Str::limit($post->title, 40),
])

<section class="section">
    <div class="wrap split split-wide">
        <article>
            <p class="news-meta" style="font-size:.95rem"><span class="cat">{{ $post->category }}</span> <time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->format('j F Y') }}</time></p>
            @if ($post->coverUrl())
                <div class="article-cover"><img src="{{ $post->coverUrl() }}" alt=""></div>
            @endif
            <div class="prose">
                @if ($post->excerpt)<p class="lead" style="color:var(--ink)">{{ $post->excerpt }}</p>@endif
                @include('partials.blocks', ['blocks' => $post->bodyBlocks()])
                @if ($post->external_url)
                    <p><a class="text-link" href="{{ $post->external_url }}" target="_blank" rel="noopener">Read the original{{ $post->source ? ' on '.$post->source : '' }} <x-icon name="external" :size="16" /></a></p>
                @endif
            </div>
        </article>
        <aside>
            @if ($more->isNotEmpty())
                <div class="facts sticky-aside">
                    <h2>More news</h2>
                    <div class="stack">
                        @foreach ($more as $item)
                            <p class="mb-0"><a href="{{ $item->url() }}" @if ($item->isExternal()) target="_blank" rel="noopener" @endif>{{ $item->title }}</a><br><span class="small muted">{{ $item->published_at->format('j F Y') }}</span></p>
                        @endforeach
                    </div>
                </div>
            @endif
        </aside>
    </div>
</section>
@endsection
