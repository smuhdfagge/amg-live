@php use App\Models\Resource; $r = $resource; @endphp
@extends('admin.layout', ['title' => $r->exists ? 'Edit resource' : 'Add resource'])

@section('content')
<div class="admin-top"><h1>{{ $r->exists ? 'Edit resource' : 'Add resource' }}</h1><a class="text-link" href="{{ route('admin.resources.index') }}">Back to resources</a></div>
@if ($errors->any())<div class="notice notice-error" role="alert">Some details need your attention. Check the highlighted fields.</div>@endif
@if (request('too_large'))<div class="notice notice-error" role="alert">That file was bigger than this server accepts ({{ $limits['php'] }} MB), so nothing was saved. Use a smaller file, or upload it to YouTube and paste the link.</div>@endif

<form method="POST" enctype="multipart/form-data" action="{{ $r->exists ? route('admin.resources.update', $r) : route('admin.resources.store') }}" data-resource-form>
    @csrf
    @if ($r->exists) @method('PUT') @endif

    <div class="panel">
        <div class="form-grid">
            @include('partials.field', ['name' => 'type', 'label' => 'Type', 'type' => 'select', 'options' => Resource::typeOptions(), 'value' => $r->type, 'required' => true])
            <div class="field @error('published_at') has-error @enderror">
                <label for="published_at">Publish date <span class="hint">(leave empty to keep as a draft)</span></label>
                <input id="published_at" type="datetime-local" name="published_at" value="{{ old('published_at', $r->published_at?->format('Y-m-d\TH:i')) }}">
                @error('published_at')<span class="error">{{ $message }}</span>@enderror
            </div>
            @include('partials.field', ['name' => 'title', 'label' => 'Title', 'required' => true, 'value' => $r->title, 'class' => 'full'])
            @include('partials.field', ['name' => 'description', 'label' => 'Short description', 'type' => 'textarea', 'value' => $r->description, 'class' => 'full'])
            @include('partials.field', ['name' => 'language', 'label' => 'Language', 'type' => 'select', 'options' => array_combine(Resource::LANGUAGES, Resource::LANGUAGES), 'value' => $r->language, 'placeholder' => 'Choose one'])
            <div class="field @error('duration') has-error @enderror">
                <label for="duration">Length <span class="hint">(optional, e.g. 28:30 or 60 sec)</span></label>
                <input id="duration" type="text" name="duration" value="{{ old('duration', $r->duration) }}" maxlength="20">
                @error('duration')<span class="error">{{ $message }}</span>@enderror
            </div>
        </div>
    </div>

    <div class="panel">
        <h2>The recording</h2>
        <p class="muted">Upload the file, or paste a link to where it already lives online. You can do both: the uploaded file plays on the site, and the link is kept for reference.</p>

        @if ($r->media_file)
            <div class="current-media">
                <strong>Current file:</strong> <a href="{{ $r->fileUrl() }}" target="_blank" rel="noopener">{{ basename($r->media_file) }}</a>
                @if ($r->isVideo())
                    <video controls preload="metadata" src="{{ $r->fileUrl() }}"></video>
                @else
                    <audio controls preload="metadata" src="{{ $r->fileUrl() }}"></audio>
                @endif
                <label class="checkbox"><input type="checkbox" name="remove_media" value="1"> Remove this file</label>
            </div>
        @endif

        @if ($limits['php'] && $limits['php'] < config('amg.uploads.video_mb'))
            <div class="notice notice-info small">This server currently accepts uploads up to {{ $limits['php'] }} MB. To upload bigger files, raise <code>upload_max_filesize</code> and <code>post_max_size</code> in cPanel's MultiPHP INI Editor (or ask your host). Until then, put long videos on YouTube and paste the link.</div>
        @endif
        <div class="field @error('media') has-error @enderror">
            <label for="media">{{ $r->media_file ? 'Replace with a new file' : 'Upload a file' }} <span class="hint" data-media-hint
                data-audio="(MP3, M4A, WAV or OGG, up to {{ $limits['audio'] }} MB)"
                data-video="(MP4, WEBM or MOV, up to {{ $limits['video'] }} MB)">{{ $r->isVideo() ? '(MP4, WEBM or MOV, up to '.$limits['video'].' MB)' : '(MP3, M4A, WAV or OGG, up to '.$limits['audio'].' MB)' }}</span></label>
            <input id="media" type="file" name="media" accept="{{ $r->isVideo() ? 'video/*' : 'audio/*' }}" data-media-input>
            @error('media')<span class="error">{{ $message }}</span>@enderror
        </div>

        @include('partials.field', ['name' => 'media_url', 'label' => 'Or paste a link', 'type' => 'url', 'value' => $r->media_url])
        <p class="help">YouTube and Vimeo videos play right on the site. Links to an MP3 or MP4 file play too. Any other link (SoundCloud, Facebook, a radio station's site) shows a Listen or Watch button. For long videos, YouTube is the best choice: it is free and saves space on your hosting.</p>

        <div class="field"><label class="checkbox"><input type="checkbox" name="allow_download" value="1" @checked(old('allow_download', $r->allow_download))> Let visitors download the uploaded file</label></div>
    </div>

    <div class="panel">
        <h2>Cover picture <span class="hint" style="font-weight:400;font-size:.9rem">(optional)</span></h2>
        <p class="muted">Shown before a video starts. YouTube videos use their own picture if you leave this empty.</p>
        @if ($r->thumbnail)
            <img class="current-cover" src="{{ $r->thumbnailUrl() }}" alt="Current cover picture">
            <div class="field"><label class="checkbox"><input type="checkbox" name="remove_thumb" value="1"> Remove this picture</label></div>
        @endif
        <div class="field @error('thumb') has-error @enderror">
            <label for="thumb">Upload a picture <span class="hint">(JPG or PNG, up to 4 MB)</span></label>
            <input id="thumb" type="file" name="thumb" accept="image/*">
            @error('thumb')<span class="error">{{ $message }}</span>@enderror
        </div>
    </div>

    <div class="form-actions">
        <button class="btn btn-red" type="submit">{{ $r->exists ? 'Save changes' : 'Save resource' }}</button>
        <a class="btn btn-outline" href="{{ route('admin.resources.index') }}">Cancel</a>
    </div>
</form>
@endsection
