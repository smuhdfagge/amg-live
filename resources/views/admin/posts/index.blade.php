@extends('admin.layout', ['title' => 'News'])

@section('content')
<div class="admin-top"><h1>News</h1><a class="btn btn-red btn-sm" href="{{ route('admin.posts.create') }}"><x-icon name="plus" :size="18" /> Add news</a></div>
<div class="panel table-wrap">
    @if ($posts->isEmpty())
        <p class="muted mb-0">No news yet. Select Add news to publish your first story.</p>
    @else
    <table class="data">
        <thead><tr><th>Title</th><th>Category</th><th>Published</th><th></th></tr></thead>
        <tbody>
        @foreach ($posts as $post)
            <tr>
                <td><a href="{{ route('admin.posts.edit', $post) }}">{{ $post->title }}</a>@if ($post->isExternal())<br><span class="small muted">Links to {{ $post->source ?: 'another site' }}</span>@endif</td>
                <td>{{ $post->category }}</td>
                <td>{{ $post->published_at ? $post->published_at->format('j M Y') : 'Draft' }}@if ($post->published_at?->isFuture())<br><span class="small muted">Scheduled</span>@endif</td>
                <td><div class="actions">
                    <a class="icon-btn" href="{{ route('admin.posts.edit', $post) }}"><x-icon name="edit" :size="16" /> Edit</a>
                    <form method="POST" action="{{ route('admin.posts.destroy', $post) }}" data-confirm="Delete this post? This cannot be undone.">@csrf @method('DELETE')<button class="icon-btn danger" type="submit"><x-icon name="trash" :size="16" /> Delete</button></form>
                </div></td>
            </tr>
        @endforeach
        </tbody>
    </table>
    {{ $posts->links('partials.pagination') }}
    @endif
</div>
@endsection
