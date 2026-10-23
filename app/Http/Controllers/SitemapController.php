<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Project;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function sitemap(): Response
    {
        $content = view('sitemap', [
            'pages' => [route('home'), route('about'), route('services'), route('products.index'), route('projects.index'), route('posts.index'), route('contact')],
            'categories' => ProductCategory::query()->published()->get(['slug', 'updated_at']),
            'products' => Product::query()->published()->get(['slug', 'updated_at']),
            'projects' => Project::query()->published()->get(['slug', 'updated_at']),
            'posts' => Post::query()->published()->get(['slug', 'updated_at']),
        ])->render();

        return response($content, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    public function robots(): Response
    {
        $lines = app()->isProduction()
            ? ['User-agent: *', 'Disallow: /admin', 'Disallow: /lien-he?', '', 'Sitemap: '.route('sitemap')]
            : ['User-agent: *', 'Disallow: /'];

        return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
