<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PostController extends Controller
{
    public function index(Request $request): View
    {
        $term = trim((string) $request->query('q'));

        $posts = Post::query()
            ->published()
            ->when($term !== '', fn (Builder $query) => $query->where(fn (Builder $query) => $query
                ->where('title', 'like', '%'.$term.'%')
                ->orWhere('excerpt', 'like', '%'.$term.'%')))
            ->paginate(9)
            ->withQueryString();

        // The newest post is highlighted on the first page of the unfiltered list.
        $featured = $term === '' && $posts->onFirstPage() ? $posts->first() : null;

        return view('posts.index', [
            'term' => $term,
            'posts' => $posts,
            'featured' => $featured,
            'gridPosts' => $featured ? $posts->getCollection()->slice(1)->values() : $posts->getCollection(),
            'popular' => Post::query()->published()->reorder()->orderByDesc('view_count')->orderByDesc('published_at')->limit(4)->get(),
            'total' => Post::query()->published()->count(),
        ]);
    }

    public function show(Post $post): View
    {
        abort_unless($post->is_published && ($post->published_at === null || $post->published_at->isPast()), 404);

        $post->newQuery()->whereKey($post->id)->increment('view_count');

        return view('posts.show', [
            'post' => $post,
            'related' => Post::query()->published()->whereKeyNot($post->id)->limit(3)->get(),
            'newer' => Post::query()->published()->reorder()->where('published_at', '>', $post->published_at)->orderBy('published_at')->first(),
            'older' => Post::query()->published()->where('published_at', '<', $post->published_at)->first(),
        ]);
    }
}
