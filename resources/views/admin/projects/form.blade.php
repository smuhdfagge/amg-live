@extends('admin.layout', ['title' => $project->exists ? 'Edit programme' : 'Add programme'])

@section('content')
<div class="admin-top"><h1>{{ $project->exists ? 'Edit '.$project->displayName() : 'Add programme' }}</h1><a class="text-link" href="{{ route('admin.projects.index') }}">Back to programmes</a></div>
@if ($errors->any())<div class="notice notice-error" role="alert">Some details need your attention. Check the highlighted fields.</div>@endif
<form method="POST" enctype="multipart/form-data" action="{{ $project->exists ? route('admin.projects.update', $project) : route('admin.projects.store') }}">
    @csrf
    @if ($project->exists) @method('PUT') @endif
    <div class="panel">
        <h2>Programme</h2>
        <div class="form-grid">
            @include('partials.field', ['name' => 'title', 'label' => 'Full name', 'required' => true, 'value' => $project->title, 'class' => 'full'])
            @include('partials.field', ['name' => 'short_title', 'label' => 'Short name or acronym', 'value' => $project->short_title])
            @include('partials.field', ['name' => 'theme', 'label' => 'Focus', 'value' => $project->theme])
            @include('partials.field', ['name' => 'summary', 'label' => 'Summary (one or two sentences)', 'type' => 'textarea', 'required' => true, 'value' => $project->summary, 'class' => 'full'])
            <div class="full">
                @include('partials.field', ['name' => 'body', 'label' => 'Full description', 'type' => 'textarea', 'value' => $project->body])
                <p class="help">Leave a blank line between paragraphs. Start a line with "## " for a sub-heading and with "- " for a bullet point.</p>
            </div>
        </div>
    </div>
    <div class="panel">
        <h2>Details</h2>
        <div class="form-grid">
            @include('partials.field', ['name' => 'partner', 'label' => 'Partner organisation', 'value' => $project->partner])
            @include('partials.field', ['name' => 'partner_role', 'label' => 'Partner role', 'value' => $project->partner_role])
            @include('partials.field', ['name' => 'period', 'label' => 'Period as shown, e.g. "2014 to 2018"', 'value' => $project->period])
            @include('partials.field', ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => \App\Models\Project::STATUSES, 'value' => $project->status, 'required' => true])
            @include('partials.field', ['name' => 'start_year', 'label' => 'Start year', 'type' => 'number', 'value' => $project->start_year])
            @include('partials.field', ['name' => 'end_year', 'label' => 'End year', 'type' => 'number', 'value' => $project->end_year])
            @include('partials.field', ['name' => 'location', 'label' => 'Location', 'value' => $project->location])
            @include('partials.field', ['name' => 'sort_order', 'label' => 'Order on the website', 'type' => 'number', 'value' => $project->sort_order])
            @include('partials.field', ['name' => 'reach_figure', 'label' => 'Headline result, e.g. "12,083"', 'value' => $project->reach_figure])
            @include('partials.field', ['name' => 'reach_label', 'label' => 'What the result counts', 'value' => $project->reach_label])
            @include('partials.field', ['name' => 'slug', 'label' => 'Web address ending', 'value' => $project->slug])
            <div class="field" style="align-self:end"><label class="checkbox"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $project->is_featured))> Feature on the home page</label></div>
        </div>
    </div>
    <div class="panel">
        <h2>Photo</h2>
        @if ($project->coverUrl())
            <img class="current-cover" src="{{ $project->coverUrl() }}" alt="Current photo">
            <div class="field"><label class="checkbox"><input type="checkbox" name="remove_cover" value="1"> Remove this photo</label></div>
        @endif
        <div class="field @error('cover') has-error @enderror">
            <label for="cover">Upload a photo <span class="hint">(JPG or PNG, up to 4 MB)</span></label>
            <input id="cover" type="file" name="cover" accept="image/*">
            @error('cover')<span class="error">{{ $message }}</span>@enderror
        </div>
        <p class="help">Use photos only with the consent of the people shown. Avoid images that identify children by name or HIV status.</p>
    </div>
    <div class="form-actions">
        <button class="btn btn-red" type="submit">{{ $project->exists ? 'Save changes' : 'Save programme' }}</button>
        <a class="btn btn-outline" href="{{ route('admin.projects.index') }}">Cancel</a>
    </div>
</form>
@endsection
