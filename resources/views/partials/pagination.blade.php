@if ($paginator->hasPages())
<nav aria-label="Pages">
    <ul class="pagination">
        @if ($paginator->onFirstPage())
            <li class="disabled"><span aria-hidden="true"><x-icon name="arrow-left" :size="18" /></span></li>
        @else
            <li><a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Previous page"><x-icon name="arrow-left" :size="18" /></a></li>
        @endif
        @foreach ($elements as $element)
            @if (is_string($element))<li class="disabled"><span>{{ $element }}</span></li>@endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <li class="active"><span aria-current="page">{{ $page }}</span></li>
                    @else
                        <li><a href="{{ $url }}">{{ $page }}</a></li>
                    @endif
                @endforeach
            @endif
        @endforeach
        @if ($paginator->hasMorePages())
            <li><a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Next page"><x-icon name="arrow-right" :size="18" /></a></li>
        @else
            <li class="disabled"><span aria-hidden="true"><x-icon name="arrow-right" :size="18" /></span></li>
        @endif
    </ul>
</nav>
@endif
