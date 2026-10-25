<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NewsController extends Controller
{
    public function index(): View
    {
        return view('news.index', [
            'posts' => Post::published()->paginate(9),
        ]);
    }

    public function show(Post $post): View|RedirectResponse
    {
        abort_if(is_null($post->published_at) || $post->published_at->isFuture(), 404);

        if ($post->isExternal()) {
            return redirect()->away($post->external_url);
        }

        return view('news.show', [
            'post' => $post,
            'more' => Post::published()->where('id', '!=', $post->id)->take(3)->get(),
        ]);
    }
}
