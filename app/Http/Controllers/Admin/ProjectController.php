<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(): View
    {
        return view('admin.projects.index', ['projects' => Project::ordered()->get()]);
    }

    public function create(): View
    {
        return view('admin.projects.form', ['project' => new Project(['status' => 'ongoing', 'sort_order' => Project::max('sort_order') + 1])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $project = Project::create($this->validated($request));

        return redirect()->route('admin.projects.index')->with('status', '"'.$project->displayName().'" was saved.');
    }

    public function edit(Project $project): View
    {
        return view('admin.projects.form', ['project' => $project]);
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        $project->update($this->validated($request, $project));

        return redirect()->route('admin.projects.index')->with('status', '"'.$project->displayName().'" was updated.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        if ($project->cover_image) {
            Storage::disk('public')->delete($project->cover_image);
        }
        $project->delete();

        return redirect()->route('admin.projects.index')->with('status', 'The programme was deleted.');
    }

    private function validated(Request $request, ?Project $project = null): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'short_title' => ['nullable', 'string', 'max:80'],
            'slug' => ['nullable', 'alpha_dash', 'max:200', Rule::unique('projects', 'slug')->ignore($project?->id)],
            'partner' => ['nullable', 'string', 'max:200'],
            'partner_role' => ['nullable', 'string', 'max:80'],
            'period' => ['nullable', 'string', 'max:60'],
            'start_year' => ['nullable', 'integer', 'between:1990,2100'],
            'end_year' => ['nullable', 'integer', 'between:1990,2100'],
            'status' => ['required', Rule::in(array_keys(Project::STATUSES))],
            'location' => ['nullable', 'string', 'max:160'],
            'theme' => ['nullable', 'string', 'max:120'],
            'summary' => ['required', 'string', 'max:600'],
            'body' => ['nullable', 'string'],
            'reach_figure' => ['nullable', 'string', 'max:30'],
            'reach_label' => ['nullable', 'string', 'max:120'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'cover' => ['nullable', 'image', 'max:4096'],
            'remove_cover' => ['nullable', 'boolean'],
        ]);

        $data['is_featured'] = $request->boolean('is_featured');
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['slug'] = ($data['slug'] ?? null) ?: $this->uniqueSlug(($data['short_title'] ?? null) ?: $data['title'], $project?->id);

        if ($request->boolean('remove_cover') && $project?->cover_image) {
            Storage::disk('public')->delete($project->cover_image);
            $data['cover_image'] = null;
        }

        if ($request->hasFile('cover')) {
            if ($project?->cover_image) {
                Storage::disk('public')->delete($project->cover_image);
            }
            $data['cover_image'] = $request->file('cover')->store('programmes', 'public');
        }

        unset($data['cover'], $data['remove_cover']);

        return $data;
    }

    private function uniqueSlug(string $title, ?int $ignoreId): string
    {
        $base = Str::slug($title) ?: 'programme';
        $slug = $base;
        $i = 2;
        while (Project::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
