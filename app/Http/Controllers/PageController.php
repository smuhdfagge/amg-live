<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Project;
use Illuminate\Http\Response;
use Illuminate\View\View;

class PageController extends Controller
{
    public function home(): View
    {
        return view('pages.home', [
            'timeline' => Project::ordered()->get(),
            'featured' => Project::where('is_featured', true)->ordered()->take(3)->get(),
            'posts' => Post::published()->take(3)->get(),
            'areas' => collect(config('swatch.thematic_areas')),
        ]);
    }

    public function about(): View
    {
        return view('pages.about', [
            'timeline' => Project::ordered()->get(),
        ]);
    }

    public function work(): View
    {
        return view('pages.work', [
            'areas' => collect(config('swatch.thematic_areas')),
        ]);
    }

    public function area(string $area): View
    {
        $areas = collect(config('swatch.thematic_areas'));
        $current = $areas->firstWhere('slug', $area);

        abort_unless($current, 404);

        return view('pages.area', [
            'area' => $current,
            'others' => $areas->where('slug', '!=', $area)->take(4),
            'projects' => Project::ordered()->get()->filter(
                fn (Project $p) => $this->matchesArea($p, $current['slug'])
            ),
        ]);
    }

    public function partners(): View
    {
        return view('pages.partners', [
            'projects' => Project::ordered()->whereNotNull('partner')->get(),
        ]);
    }

    public function involved(): View
    {
        return view('pages.involved');
    }

    public function support(): View
    {
        return view('pages.support', [
            'featured' => Project::ordered()->take(3)->get(),
        ]);
    }

    public function contact(): View
    {
        return view('pages.contact');
    }

    public function privacy(): View
    {
        return view('pages.privacy');
    }

    public function sitemap(): Response
    {
        $urls = collect([
            route('home'), route('about'), route('work'), route('projects.index'), route('partners'),
            route('news.index'), route('involved'), route('support'), route('contact'),
        ])
            ->merge(collect(config('swatch.thematic_areas'))->map(fn ($a) => route('work.area', $a['slug'])))
            ->merge(Project::pluck('slug')->map(fn ($s) => route('projects.show', $s)))
            ->merge(Post::published()->whereNotNull('body')->pluck('slug')->map(fn ($s) => route('news.show', $s)));

        return response()->view('sitemap', ['urls' => $urls], 200)->header('Content-Type', 'application/xml');
    }

    private function matchesArea(Project $project, string $slug): bool
    {
        $theme = strtolower((string) $project->theme);

        return match ($slug) {
            'health' => str_contains($theme, 'hiv') || str_contains($theme, 'health'),
            'orphans-and-vulnerable-children' => str_contains($theme, 'orphan'),
            default => str_contains($theme, strtolower(str_replace('-', ' ', $slug))),
        };
    }
}
