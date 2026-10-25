@php
    $embed = $resource->embedUrl();
    $play = $resource->playableUrl();
    $external = $resource->externalUrl();
    $thumb = $resource->thumbnailUrl();
@endphp
<article @class(['resource', 'resource-video' => $resource->isVideo()]) id="{{ $resource->anchor() }}">
    @if ($resource->isVideo())
        <div class="resource-media">
            @if ($embed)
                <div class="video-frame" data-embed="{{ $embed }}">
                    <button type="button" class="video-poster" data-play-embed aria-label="Play video: {{ $resource->title }}">
                        @if ($thumb)<img src="{{ $thumb }}" alt="" loading="lazy">@else<span class="motif-mark">@include('partials.motif')</span>@endif
                        <span class="play-badge"><x-icon name="play" :size="30" /></span>
                    </button>
                </div>
            @elseif ($play)
                <video controls preload="none" playsinline @if ($thumb) poster="{{ $thumb }}" @endif @unless ($resource->allow_download) controlslist="nodownload" @endunless>
                    <source src="{{ $play }}">
                    <a href="{{ $play }}">Watch the video</a>
                </video>
            @else
                <a class="video-poster" href="{{ $external }}" target="_blank" rel="noopener">
                    @if ($thumb)<img src="{{ $thumb }}" alt="" loading="lazy">@else<span class="motif-mark">@include('partials.motif')</span>@endif
                    <span class="play-badge"><x-icon name="play" :size="30" /></span>
                    <span class="sr-only">Watch {{ $resource->title }} (opens in a new tab)</span>
                </a>
            @endif
        </div>
    @endif

    <div class="resource-body">
        <div class="resource-meta">
            <span class="resource-type"><x-icon :name="$resource->typeIcon()" :size="16" /> {{ $resource->typeLabel() }}</span>
            <time datetime="{{ $resource->published_at->toDateString() }}">{{ $resource->published_at->format('j M Y') }}</time>
            @if ($resource->language)<span>{{ $resource->language }}</span>@endif
            @if ($resource->duration)<span>{{ $resource->duration }}</span>@endif
        </div>
        <h3>{{ $resource->title }}</h3>
        @if ($resource->description)<p>{{ $resource->description }}</p>@endif

        @unless ($resource->isVideo())
            @if ($play)
                <audio controls preload="none" @unless ($resource->allow_download) controlslist="nodownload" @endunless>
                    <source src="{{ $play }}">
                    <a href="{{ $play }}">Listen</a>
                </audio>
            @elseif ($embed)
                <a class="text-link" href="{{ $resource->media_url }}" target="_blank" rel="noopener">Listen<span class="sr-only"> (opens in a new tab)</span></a>
            @endif
        @endunless

        <div class="resource-actions">
            @if ($external)
                <a class="btn btn-primary btn-sm" href="{{ $external }}" target="_blank" rel="noopener">
                    <x-icon name="external" :size="16" /> {{ $resource->isVideo() ? 'Watch' : 'Listen' }}<span class="sr-only"> (opens in a new tab)</span>
                </a>
            @endif
            @if ($resource->media_file && $resource->allow_download)
                <a class="btn btn-outline btn-sm" href="{{ $resource->fileUrl() }}" download><x-icon name="download" :size="16" /> Download</a>
            @endif
        </div>
    </div>
</article>
