<section class="page-head">
    <div class="page-head-laurel">@include('partials.laurel')</div>
    <div class="wrap">
        <nav class="crumbs" aria-label="Breadcrumb">
            <ol>
                <li><a href="{{ route('home') }}">Home</a></li>
                @foreach ($crumbs ?? [] as $label => $link)
                    <li><a href="{{ $link }}">{{ $label }}</a></li>
                @endforeach
                <li aria-current="page">{{ $current ?? $heading }}</li>
            </ol>
        </nav>
        <h1>{{ $heading }}</h1>
        @isset($intro)<p class="lead">{{ $intro }}</p>@endisset
    </div>
</section>
