@extends('admin.layout', ['title' => 'Message from '.$message->name])

@section('content')
<div class="admin-top"><h1>Message from {{ $message->name }}</h1><a class="text-link" href="{{ route('admin.messages.index') }}">Back to messages</a></div>
<div class="panel">
    <dl class="meta-list">
        <dt>Type</dt><dd>{{ $message->typeLabel() }}</dd>
        <dt>Email</dt><dd><a href="mailto:{{ $message->email }}">{{ $message->email }}</a></dd>
        @if ($message->phone)<dt>Phone</dt><dd><a href="tel:{{ $message->phone }}">{{ $message->phone }}</a></dd>@endif
        @if ($message->organisation)<dt>Organisation</dt><dd>{{ $message->organisation }}</dd>@endif
        @if ($message->subject)<dt>Subject</dt><dd>{{ $message->subject }}</dd>@endif
        <dt>Received</dt><dd>{{ $message->created_at->format('j F Y, g:ia') }}</dd>
    </dl>
    <div class="message-body">{{ $message->message }}</div>
    <div class="form-actions">
        <a class="btn btn-indigo" href="mailto:{{ $message->email }}?subject={{ rawurlencode('Re: '.($message->subject ?: 'Your message to SWATCH')) }}"><x-icon name="mail" :size="18" /> Reply by email</a>
        <form method="POST" action="{{ route('admin.messages.destroy', $message) }}" data-confirm="Delete this message?">@csrf @method('DELETE')<button class="btn btn-outline" type="submit">Delete</button></form>
    </div>
</div>
@endsection
