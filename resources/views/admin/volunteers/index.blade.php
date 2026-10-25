@extends('admin.layout', ['title' => 'Volunteers'])
@php use App\Models\VolunteerApplication as V; @endphp

@section('content')
<div class="admin-top"><h1>Volunteer applications</h1>
    @if ($volunteers->total())<a class="btn btn-outline btn-sm" href="{{ route('admin.volunteers.export') }}"><x-icon name="download" :size="18" /> Download CSV</a>@endif
</div>
<div class="panel table-wrap">
    @if ($volunteers->isEmpty())
        <p class="muted mb-0">No applications yet. Applications from the Get involved page will appear here.</p>
    @else
    <table class="data">
        <thead><tr><th>Name</th><th>Phone</th><th>Interest</th><th>Location</th><th>Received</th></tr></thead>
        <tbody>
        @foreach ($volunteers as $v)
            <tr @class(['unread' => ! $v->read_at])>
                <td><a href="{{ route('admin.volunteers.show', $v) }}">{{ $v->name }}</a><br><span class="small muted">{{ $v->email }}</span></td>
                <td>{{ $v->phone }}</td>
                <td>{{ V::INTERESTS[$v->interest] ?? $v->interest }}</td>
                <td>{{ $v->location }}</td>
                <td style="white-space:nowrap">{{ $v->created_at->format('j M Y') }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    {{ $volunteers->links('partials.pagination') }}
    @endif
</div>
@endsection
