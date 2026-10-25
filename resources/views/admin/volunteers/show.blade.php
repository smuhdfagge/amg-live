@extends('admin.layout', ['title' => 'Application from '.$volunteer->name])
@php use App\Models\VolunteerApplication as V; @endphp

@section('content')
<div class="admin-top"><h1>{{ $volunteer->name }}</h1><a class="text-link" href="{{ route('admin.volunteers.index') }}">Back to applications</a></div>
<div class="panel">
    <dl class="meta-list">
        <dt>Email</dt><dd><a href="mailto:{{ $volunteer->email }}">{{ $volunteer->email }}</a></dd>
        <dt>Phone</dt><dd><a href="tel:{{ $volunteer->phone }}">{{ $volunteer->phone }}</a></dd>
        @if ($volunteer->location)<dt>Location</dt><dd>{{ $volunteer->location }}</dd>@endif
        <dt>Interest</dt><dd>{{ V::INTERESTS[$volunteer->interest] ?? $volunteer->interest }}</dd>
        @if ($volunteer->availability)<dt>Availability</dt><dd>{{ V::AVAILABILITY[$volunteer->availability] ?? $volunteer->availability }}</dd>@endif
        <dt>Received</dt><dd>{{ $volunteer->created_at->format('j F Y, g:ia') }}</dd>
    </dl>
    @if ($volunteer->skills)<h2>Skills and experience</h2><div class="message-body">{{ $volunteer->skills }}</div>@endif
    @if ($volunteer->message)<h2 class="mt-2">Message</h2><div class="message-body">{{ $volunteer->message }}</div>@endif
    <div class="form-actions">
        <a class="btn btn-indigo" href="tel:{{ $volunteer->phone }}"><x-icon name="phone" :size="18" /> Call</a>
        <a class="btn btn-outline" href="mailto:{{ $volunteer->email }}">Email</a>
        <form method="POST" action="{{ route('admin.volunteers.destroy', $volunteer) }}" data-confirm="Delete this application?">@csrf @method('DELETE')<button class="btn btn-outline" type="submit">Delete</button></form>
    </div>
</div>
@endsection
