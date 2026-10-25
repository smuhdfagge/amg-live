@extends('admin.layout', ['title' => 'Import news'])

@section('content')
<div class="admin-top"><h1>Import news from the old website</h1><a class="text-link" href="{{ route('admin.posts.index') }}">Back to news</a></div>

@if ($job)
    @php
        $c = $job['counts'];
        $handled = array_sum($c);
        $percent = $job['total'] ? min(100, (int) round($handled / $job['total'] * 100)) : 0;
        if ($job['done']) $percent = 100;
    @endphp
    <div class="panel import-progress" @unless ($job['done']) data-import-running @endunless>
        @if ($job['done'])
            <h2>Import finished</h2>
            <p class="lead">{{ $c['created'] }} {{ Str::plural('story', $c['created']) }} added, {{ $c['updated'] }} updated, {{ $c['skipped'] }} already here and skipped@if ($c['failed']), {{ $c['failed'] }} could not be imported@endif.</p>
        @else
            <h2>Importing… {{ $handled }} of {{ $job['total'] }}</h2>
            <p class="muted">Keep this page open. Photos take a little while to copy across. The page moves on by itself.</p>
        @endif

        <div class="bar" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $percent }}" aria-label="Import progress"><span style="width: {{ $percent }}%"></span></div>

        <dl class="import-counts">
            <div><dt>Added</dt><dd>{{ $c['created'] }}</dd></div>
            <div><dt>Updated</dt><dd>{{ $c['updated'] }}</dd></div>
            <div><dt>Skipped</dt><dd>{{ $c['skipped'] }}</dd></div>
            <div><dt>Problems</dt><dd>{{ $c['failed'] }}</dd></div>
        </dl>

        @if (! empty($job['photos_found']))
            <p class="muted small">Using {{ $job['photos_found'] }} photos from your zip.</p>
        @endif
        @if (! empty($job['dead_hosts']))
            <div class="notice notice-info" role="status">Photos could not be copied from {{ implode(', ', $job['dead_hosts']) }} because this server cannot connect to it. The stories are still imported. Add the photos later by running the import again with <strong>Update stories</strong> ticked and a zip of the old photos.</div>
        @endif

        @if ($job['errors'])
            <div class="notice notice-error" role="alert" style="display:block">
                <strong>Some things did not work:</strong>
                <ul class="mb-0">@foreach ($job['errors'] as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        @if ($job['done'])
            <div class="btn-row">
                <a class="btn btn-red" href="{{ route('admin.posts.index') }}">See the imported news</a>
                <form method="POST" action="{{ route('admin.news-import.reset') }}">@csrf<button class="btn btn-outline" type="submit">Start another import</button></form>
            </div>
        @else
            <form method="POST" action="{{ route('admin.news-import.step') }}" data-import-next>
                @csrf
                <noscript><button class="btn btn-red" type="submit">Continue</button></noscript>
            </form>
            <form method="POST" action="{{ route('admin.news-import.reset') }}" class="mt-2">@csrf<button class="text-link" style="background:none;border:0;padding:0;cursor:pointer" type="submit">Stop the import</button></form>
        @endif
    </div>
@else
    @if ($errors->any())<div class="notice notice-error" role="alert">{{ $errors->first() }}</div>@endif
    @if (request('too_large'))<div class="notice notice-error" role="alert">The files were bigger than this server accepts ({{ \App\Http\Controllers\Admin\ResourceController::uploadLimits()['php'] }} MB), so nothing was imported. Zip one year of photos at a time and run the import once for each zip, with <strong>Update stories</strong> ticked after the first.</div>@endif

    <form method="POST" action="{{ route('admin.news-import.start') }}" enctype="multipart/form-data" class="panel" data-import-form>
        @csrf
        <p class="lead" style="margin-top:0">Bring your old news across, with titles, dates, text, categories and photos. Nothing on the old website is changed.</p>

        <fieldset class="choice">
            <legend>Where should the news come from?</legend>
            <label class="choice-card">
                <input type="radio" name="source" value="site" @checked(old('source', 'site') === 'site')>
                <span><strong>The old website's address</strong><br><span class="muted small">Easiest. Works for most WordPress sites.</span></span>
            </label>
            <label class="choice-card">
                <input type="radio" name="source" value="file" @checked(old('source') === 'file')>
                <span><strong>A WordPress export file</strong><br><span class="muted small">Use this if the address option cannot connect. With a zip of the photos it needs no connection at all.</span></span>
            </label>
        </fieldset>

        <div data-show-for="site" style="margin-bottom:1rem">
            @include('partials.field', ['name' => 'site_url', 'label' => 'Old website address', 'value' => 'https://aminumagashigarbafoundation.org', 'required' => true])
            @error('site_url')@else<p class="help">If this fails with a connection problem, the message tells you whether it is your new host or the old one. The export file option below works without any connection.</p>@enderror
        </div>

        <div data-show-for="file">
            <div class="field @error('file') has-error @enderror">
                <label for="file">WordPress export file <span class="hint">(.xml)</span></label>
                <input id="file" type="file" name="file" accept=".xml,text/xml,application/xml">
                @error('file')<span class="error">{{ $message }}</span>@enderror
            </div>
            <p class="help">To get the file: sign in to the old WordPress site, go to <strong>Tools</strong>, then <strong>Export</strong>, choose <strong>Posts</strong> and select <strong>Download Export File</strong>.</p>

            <div class="field @error('photos_zip') has-error @enderror" style="margin-top:1rem">
                <label for="photos_zip">Old photos as a zip <span class="hint">(optional, but needed if this server cannot reach the old site)</span></label>
                <input id="photos_zip" type="file" name="photos_zip" accept=".zip,application/zip">
                @error('photos_zip')<span class="error">{{ $message }}</span>@enderror
            </div>
            <p class="help">To get the zip: in the old site's cPanel, open <strong>File Manager</strong>, go to <strong>public_html/wp-content</strong>, right-click the <strong>uploads</strong> folder and choose <strong>Compress</strong> (Zip), then download it. Without a zip, photos are copied from the old website if this server can reach it. This server accepts uploads up to {{ \App\Http\Controllers\Admin\ResourceController::uploadLimits()['php'] }} MB; if the zip is bigger, zip one year folder (for example uploads/2024) at a time.</p>
        </div>

        <div class="field"><label class="checkbox"><input type="checkbox" name="photos" value="1" @checked(old('photos', true))> Copy the photos too (featured image and photos inside each story)</label></div>
        <div class="field"><label class="checkbox"><input type="checkbox" name="update" value="1" @checked(old('update'))> Update stories that were imported before (otherwise they are skipped)</label></div>

        <div class="form-actions"><button class="btn btn-red" type="submit">Start import</button></div>
    </form>

    <div class="panel">
        <h2>Good to know</h2>
        <ul class="mb-0">
            <li><strong>Run the import while the old website is still online.</strong> Text and photos are copied from it. If this new site will take over the same address, import first, then switch the domain.</li>
            <li>Only published posts are imported. Drafts and pages are left behind.</li>
            <li>You can run the import again at any time. Stories already here are skipped, so nothing is duplicated.</li>
            <li>Links, bold text and embedded videos inside old stories become plain text. Check a few stories after the import.</li>
            <li>Once this site is live on the old address, old links to stories (for example ones shared on Facebook or WhatsApp) open the same story here.</li>
        </ul>
    </div>
@endif
@endsection
