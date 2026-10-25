<a class="news-card" href="{{ $post->url() }}" @if ($post->isExternal()) target="_blank" rel="noopener" @endif>
    <div class="news-cover">
        @if ($post->coverUrl())
            <img src="{{ $post->coverUrl() }}" alt="" loading="lazy">
        @else
            <span class="laurel-mark" style="width:46%;opacity:.45;display:block">@include('partials.laurel')</span>
        @endif
    </div>
    <div class="news-meta">
        <span class="cat">{{ $post->category }}</span>
        <time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->format('j F Y') }}</time>
    </div>
    <h3>{{ $post->title }}</h3>
    @if ($post->excerpt)<p>{{ $post->excerpt }}</p>@endif
    @if ($post->isExternal())
        <span class="ext">Read on {{ $post->source ?: 'the publisher\'s site' }} <x-icon name="external" :size="16" /><span class="sr-only">(opens in a new tab)</span></span>
    @endif
</a>
