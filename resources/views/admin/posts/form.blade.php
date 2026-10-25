@extends('admin.layout', ['title' => $post->exists ? 'Edit news' : 'Add news'])

@section('content')
<div class="admin-top"><h1>{{ $post->exists ? 'Edit news' : 'Add news' }}</h1><a class="text-link" href="{{ route('admin.posts.index') }}">Back to news</a></div>
@if ($errors->any())<div class="notice notice-error" role="alert">Some details need your attention. Check the highlighted fields.</div>@endif
<form method="POST" enctype="multipart/form-data" action="{{ $post->exists ? route('admin.posts.update', $post) : route('admin.posts.store') }}">
    @csrf
    @if ($post->exists) @method('PUT') @endif
    <div class="panel">
        <div class="form-grid">
            @include('partials.field', ['name' => 'title', 'label' => 'Title', 'required' => true, 'value' => $post->title, 'class' => 'full'])
            @include('partials.field', ['name' => 'category', 'label' => 'Category', 'type' => 'select', 'options' => array_combine(\App\Models\Post::CATEGORIES, \App\Models\Post::CATEGORIES), 'value' => $post->category, 'required' => true])
            <div class="field @error('published_at') has-error @enderror">
                <label for="published_at">Publish date <span class="hint">(leave empty to keep as a draft)</span></label>
                <input id="published_at" type="datetime-local" name="published_at" value="{{ old('published_at', $post->published_at?->format('Y-m-d\TH:i')) }}">
                @error('published_at')<span class="error">{{ $message }}</span>@enderror
            </div>
            @include('partials.field', ['name' => 'excerpt', 'label' => 'Short summary', 'type' => 'textarea', 'value' => $post->excerpt, 'class' => 'full'])
            <div class="full">
                @include('partials.field', ['name' => 'body', 'label' => 'Story', 'type' => 'textarea', 'value' => $post->body])
                <p class="help">Leave a blank line between paragraphs. Start a line with "## " for a sub-heading and with "- " for a bullet point.</p>
            </div>
        </div>
    </div>
    <div class="panel">
        <h2>Photo</h2>
        @if ($post->coverUrl())
            <img class="current-cover" src="{{ $post->coverUrl() }}" alt="Current photo">
            <div class="field"><label class="checkbox"><input type="checkbox" name="remove_cover" value="1"> Remove this photo</label></div>
        @endif
        <div class="field @error('cover') has-error @enderror">
            <label for="cover">Upload a photo <span class="hint">(JPG or PNG, up to 4 MB)</span></label>
            <input id="cover" type="file" name="cover" accept="image/*">
            @error('cover')<span class="error">{{ $message }}</span>@enderror
        </div>
    </div>
    <div class="panel">
        <h2>Published elsewhere?</h2>
        <p class="muted">If this story was published by a newspaper or partner, add the link. When the Story field is empty, visitors go straight to that link.</p>
        <div class="form-grid">
            @include('partials.field', ['name' => 'external_url', 'label' => 'Link to the original', 'type' => 'url', 'value' => $post->external_url])
            @include('partials.field', ['name' => 'source', 'label' => 'Publisher name', 'value' => $post->source])
            @include('partials.field', ['name' => 'slug', 'label' => 'Web address ending', 'value' => $post->slug])
        </div>
    </div>
    <div class="form-actions">
        <button class="btn btn-red" type="submit">{{ $post->exists ? 'Save changes' : 'Save news' }}</button>
        <a class="btn btn-outline" href="{{ route('admin.posts.index') }}">Cancel</a>
    </div>
</form>
@endsection
