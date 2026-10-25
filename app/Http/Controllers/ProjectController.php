<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(): View
    {
        return view('projects.index', [
            'projects' => Project::ordered()->get(),
        ]);
    }

    public function show(Project $project): View
    {
        $all = Project::ordered()->get();
        $index = $all->search(fn ($p) => $p->id === $project->id);

        return view('projects.show', [
            'project' => $project,
            'previous' => $index > 0 ? $all[$index - 1] : null,
            'next' => $all[$index + 1] ?? null,
        ]);
    }
}
