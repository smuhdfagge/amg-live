<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PostController extends Controller
{
    public function index(): View
    {
        return view('admin.posts.index', [
            'posts' => Post::orderByDesc('published_at')->orderByDesc('created_at')->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.posts.form', ['post' => new Post(['category' => 'News', 'published_at' => now()])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $post = Post::create($this->validated($request));

        return redirect()->route('admin.posts.index')->with('status', '"'.$post->title.'" was saved.');
    }

    public function edit(Post $post): View
    {
        return view('admin.posts.form', ['post' => $post]);
    }

    public function update(Request $request, Post $post): RedirectResponse
    {
        $post->update($this->validated($request, $post));

        return redirect()->route('admin.posts.index')->with('status', '"'.$post->title.'" was updated.');
    }

    public function destroy(Post $post): RedirectResponse
    {
        if ($post->cover_image) {
            Storage::disk('public')->delete($post->cover_image);
        }
        $post->delete();

        return redirect()->route('admin.posts.index')->with('status', 'The post was deleted.');
    }

    private function validated(Request $request, ?Post $post = null): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'slug' => ['nullable', 'alpha_dash', 'max:200', Rule::unique('posts', 'slug')->ignore($post?->id)],
            'category' => ['required', Rule::in(Post::CATEGORIES)],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'body' => ['nullable', 'required_without:external_url', 'string'],
            'external_url' => ['nullable', 'url', 'max:500'],
            'source' => ['nullable', 'string', 'max:120'],
            'published_at' => ['nullable', 'date'],
            'cover' => ['nullable', 'image', 'max:4096'],
            'remove_cover' => ['nullable', 'boolean'],
        ], [
            'body.required_without' => 'Write the story, or add a link to where it was published.',
        ]);

        $data['slug'] = ($data['slug'] ?? null) ?: $this->uniqueSlug($data['title'], $post?->id);

        if ($request->boolean('remove_cover') && $post?->cover_image) {
            Storage::disk('public')->delete($post->cover_image);
            $data['cover_image'] = null;
        }

        if ($request->hasFile('cover')) {
            if ($post?->cover_image) {
                Storage::disk('public')->delete($post->cover_image);
            }
            $data['cover_image'] = $request->file('cover')->store('news', 'public');
        }

        unset($data['cover'], $data['remove_cover']);

        return $data;
    }

    private function uniqueSlug(string $title, ?int $ignoreId): string
    {
        $base = Str::slug($title) ?: 'post';
        $slug = $base;
        $i = 2;
        while (Post::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
