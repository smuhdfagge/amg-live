@extends('admin.layout', ['title' => 'Subscribers'])

@section('content')
<div class="admin-top"><h1>Subscribers</h1>
    @if ($subscribers->total())<a class="btn btn-outline btn-sm" href="{{ route('admin.subscribers.export') }}"><x-icon name="download" :size="18" /> Download CSV</a>@endif
</div>
<div class="panel table-wrap">
    @if ($subscribers->isEmpty())
        <p class="muted mb-0">No subscribers yet. People who sign up in the website footer will appear here.</p>
    @else
    <table class="data">
        <thead><tr><th>Email</th><th>Subscribed</th><th></th></tr></thead>
        <tbody>
        @foreach ($subscribers as $s)
            <tr>
                <td>{{ $s->email }}</td>
                <td>{{ $s->created_at->format('j M Y') }}</td>
                <td><div class="actions"><form method="POST" action="{{ route('admin.subscribers.destroy', $s) }}" data-confirm="Remove {{ $s->email }}?">@csrf @method('DELETE')<button class="icon-btn danger" type="submit"><x-icon name="trash" :size="16" /> Remove</button></form></div></td>
            </tr>
        @endforeach
        </tbody>
    </table>
    {{ $subscribers->links('partials.pagination') }}
    @endif
</div>
@endsection
