<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Product;
use Illuminate\View\View;

class PostController extends Controller
{
    public function index(): View
    {
        return view('posts.index', [
            'posts' => Post::query()->published()->paginate(10),
        ]);
    }

    public function show(Post $post): View
    {
        abort_unless($post->is_published && ($post->published_at === null || $post->published_at->isPast()), 404);

        $post->newQuery()->whereKey($post->id)->increment('view_count');

        return view('posts.show', [
            'post' => $post,
            'related' => Post::query()->published()->whereKeyNot($post->id)->limit(2)->get(),
            'featuredProduct' => Product::query()->published()->where('is_featured', true)->inRandomOrder()->first(),
        ]);
    }
}
