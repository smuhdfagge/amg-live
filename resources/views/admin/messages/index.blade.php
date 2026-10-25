@extends('admin.layout', ['title' => 'Messages'])

@section('content')
<div class="admin-top"><h1>Messages</h1></div>
<div class="panel table-wrap">
    @if ($messages->isEmpty())
        <p class="muted mb-0">No messages yet. Messages sent from the contact page will appear here.</p>
    @else
    <table class="data">
        <thead><tr><th>From</th><th>Type</th><th>Subject</th><th>Received</th></tr></thead>
        <tbody>
        @foreach ($messages as $m)
            <tr @class(['unread' => ! $m->read_at])>
                <td><a href="{{ route('admin.messages.show', $m) }}">{{ $m->name }}</a><br><span class="small muted">{{ $m->organisation ?: $m->email }}</span></td>
                <td>{{ $m->typeLabel() }}</td>
                <td>{{ \Illuminate\Support\Str::limit($m->subject ?: $m->message, 70) }}</td>
                <td style="white-space:nowrap">{{ $m->created_at->format('j M Y, g:ia') }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    {{ $messages->links('partials.pagination') }}
    @endif
</div>
@endsection
