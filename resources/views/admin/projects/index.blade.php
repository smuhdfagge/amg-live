@extends('admin.layout', ['title' => 'Programmes'])

@section('content')
<div class="admin-top"><h1>Programmes</h1><a class="btn btn-red btn-sm" href="{{ route('admin.projects.create') }}"><x-icon name="plus" :size="18" /> Add programme</a></div>
<div class="panel table-wrap">
    <table class="data">
        <thead><tr><th>Order</th><th>Programme</th><th>Period</th><th>Status</th><th>Home page</th><th></th></tr></thead>
        <tbody>
        @forelse ($projects as $project)
            <tr>
                <td>{{ $project->sort_order }}</td>
                <td><a href="{{ route('admin.projects.edit', $project) }}">{{ $project->displayName() }}</a><br><span class="small muted">{{ $project->partner }}</span></td>
                <td>{{ $project->period }}</td>
                <td>{{ $project->statusLabel() }}</td>
                <td>{{ $project->is_featured ? 'Featured' : '' }}</td>
                <td><div class="actions">
                    <a class="icon-btn" href="{{ route('projects.show', $project) }}" target="_blank" rel="noopener"><x-icon name="eye" :size="16" /> View</a>
                    <a class="icon-btn" href="{{ route('admin.projects.edit', $project) }}"><x-icon name="edit" :size="16" /> Edit</a>
                    <form method="POST" action="{{ route('admin.projects.destroy', $project) }}" data-confirm="Delete this programme? This cannot be undone.">@csrf @method('DELETE')<button class="icon-btn danger" type="submit"><x-icon name="trash" :size="16" /> Delete</button></form>
                </div></td>
            </tr>
        @empty
            <tr><td colspan="6" class="muted">No programmes yet.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<p class="muted small">Programmes are listed on the website from the lowest order number to the highest.</p>
@endsection
