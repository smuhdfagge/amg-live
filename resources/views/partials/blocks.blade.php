@foreach ($blocks as $block)
    @if ($block['type'] === 'heading')
        <h2>{{ $block['content'] }}</h2>
    @elseif ($block['type'] === 'list')
        <ul>
            @foreach ($block['content'] as $item)<li>{{ $item }}</li>@endforeach
        </ul>
    @else
        <p>{{ $block['content'] }}</p>
    @endif
@endforeach
