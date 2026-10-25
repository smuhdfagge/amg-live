@extends('admin.layout', ['title' => 'Dashboard'])

@section('content')
<div class="admin-top"><h1>Welcome, {{ auth()->user()->name }}</h1>
    <div class="btn-row"><a class="btn btn-red btn-sm" href="{{ route('admin.posts.create') }}"><x-icon name="plus" :size="18" /> Add news</a></div>
</div>
<div class="stats">
    <a class="stat" href="{{ route('admin.messages.index') }}"><strong>{{ $counts['unreadMessages'] }}</strong><span>unread messages</span></a>
    <a class="stat" href="{{ route('admin.volunteers.index') }}"><strong>{{ $counts['newVolunteers'] }}</strong><span>new volunteer applications</span></a>
    <a class="stat" href="{{ route('admin.subscribers.index') }}"><strong>{{ $counts['subscribers'] }}</strong><span>subscribers</span></a>
    <a class="stat" href="{{ route('admin.posts.index') }}"><strong>{{ $counts['posts'] }}</strong><span>news posts</span></a>
    <a class="stat" href="{{ route('admin.projects.index') }}"><strong>{{ $counts['projects'] }}</strong><span>programmes</span></a>
</div>
<div class="split">
    <div class="panel">
        <h2>Latest messages</h2>
        @forelse ($messages as $m)
            <p class="mb-0" style="padding:.6rem 0;border-bottom:1px solid var(--line)"><a href="{{ route('admin.messages.show', $m) }}" style="font-weight:{{ $m->read_at ? 500 : 700 }}">{{ $m->name }}</a> <span class="small muted">{{ $m->typeLabel() }}, {{ $m->created_at->diffForHumans() }}</span></p>
        @empty
            <p class="muted mb-0">No messages yet. Messages sent from the contact page will appear here.</p>
        @endforelse
    </div>
    <div class="panel">
        <h2>Latest volunteer applications</h2>
        @forelse ($volunteers as $v)
            <p class="mb-0" style="padding:.6rem 0;border-bottom:1px solid var(--line)"><a href="{{ route('admin.volunteers.show', $v) }}" style="font-weight:{{ $v->read_at ? 500 : 700 }}">{{ $v->name }}</a> <span class="small muted">{{ \App\Models\VolunteerApplication::INTERESTS[$v->interest] ?? $v->interest }}, {{ $v->created_at->diffForHumans() }}</span></p>
        @empty
            <p class="muted mb-0">No applications yet. Applications from the Get involved page will appear here.</p>
        @endforelse
    </div>
</div>
@endsection
