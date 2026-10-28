<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Post;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Project;
use App\Models\Service;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Response;

/**
 * Machine-readable entry points: sitemap.xml for search engines, robots.txt, and llms.txt / llms-full.txt
 * (the plain-text summary AI assistants and agents read instead of crawling HTML).
 */
class SitemapController extends Controller
{
    /**
     * Crawlers of AI assistants and AI search, allowed explicitly so the company and its products can be cited.
     */
    private const AI_CRAWLERS = [
        'GPTBot', 'OAI-SearchBot', 'ChatGPT-User', 'ClaudeBot', 'Claude-SearchBot', 'Claude-User', 'PerplexityBot',
        'Perplexity-User', 'Google-Extended', 'Applebot-Extended', 'Bingbot', 'DuckAssistBot', 'meta-externalagent', 'CCBot',
    ];

    public function sitemap(): Response
    {
        $pages = ['home', 'about', 'services', 'products.index', 'projects.index', 'posts.index', 'contact'];
        $categories = ProductCategory::query()->published()->get(['slug', 'translations', 'updated_at']);
        $brands = Brand::query()->published()->whereHas('products', fn ($query) => $query->published())->get(['slug', 'translations', 'updated_at']);
        $products = Product::query()->published()->get(['slug', 'name', 'image', 'translations', 'updated_at']);
        $projects = Project::query()->published()->get(['slug', 'title', 'cover', 'translations', 'updated_at']);
        $posts = Post::query()->published()->get(['slug', 'title', 'cover', 'translations', 'updated_at']);

        // English pages: the fixed pages, plus records an editor has translated (untranslated ones are noindex in English).
        $english = collect([
            'products.category' => $categories, 'products.brand' => $brands, 'products.show' => $products,
            'projects.show' => $projects, 'posts.show' => $posts,
        ])->flatMap(fn ($records, string $route) => $records
            ->filter(fn ($record) => $record->isTranslatedInto('en'))
            ->map(fn ($record) => ['loc' => route("en.{$route}", $record), 'lastmod' => $record->updated_at]));

        $content = view('sitemap', [
            'pages' => [...array_map(fn (string $name) => route($name), $pages), ...array_map(fn (string $name) => route("en.{$name}"), $pages)],
            'categories' => $categories,
            'brands' => $brands,
            'products' => $products,
            'projects' => $projects,
            'posts' => $posts,
            'english' => $english,
        ])->render();

        return response($content, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    public function robots(): Response
    {
        if (! app()->isProduction()) {
            return $this->text("User-agent: *\nDisallow: /\n");
        }

        $rules = ['Allow: /', 'Disallow: /admin', 'Disallow: /livewire', 'Disallow: /*?q=', 'Disallow: /*&q='];
        $lines = ['# Website '.setting('company_name').'. AI assistants: '.url('/llms.txt'), '', 'User-agent: *', ...$rules, ''];

        foreach (self::AI_CRAWLERS as $agent) {
            $lines[] = "User-agent: {$agent}";
        }

        array_push($lines, ...$rules);
        $lines[] = '';
        $lines[] = 'Sitemap: '.route('sitemap');

        return $this->text(implode("\n", $lines)."\n");
    }

    /**
     * Short Markdown guide to the company and the site (https://llmstxt.org).
     */
    public function llms(): Response
    {
        return $this->text(view('llms', [
            'categories' => $this->categoryTree(),
            'brands' => Brand::query()->published()->ordered()->get(),
            'services' => Service::query()->published()->ordered()->get(),
        ])->render(), 'text/markdown');
    }

    /**
     * The full product catalogue as Markdown, so an assistant can answer "who sells X in Vietnam?" accurately.
     */
    public function llmsFull(): Response
    {
        return $this->text(view('llms-full', [
            'categories' => $this->categoryTree(),
            'products' => Product::query()->published()->with('brand')->ordered()->get()->groupBy('product_category_id'),
        ])->render(), 'text/markdown');
    }

    /**
     * @return Collection<int, ProductCategory>
     */
    private function categoryTree(): Collection
    {
        return ProductCategory::query()
            ->published()
            ->whereNull('parent_id')
            ->with(['children' => fn ($query) => $query->published()->ordered()])
            ->ordered()
            ->get();
    }

    private function text(string $content, string $type = 'text/plain'): Response
    {
        return response($content, 200, ['Content-Type' => "{$type}; charset=UTF-8", 'X-Robots-Tag' => 'noindex']);
    }
}
