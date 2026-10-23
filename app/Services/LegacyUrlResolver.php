<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\Post;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Maps URLs of the old CakePHP site to their new location.
 *
 * Order: admin-managed redirects first (so editors can override anything), then the old URL patterns.
 * Returns null when nothing matches, so the caller can render a normal 404.
 */
class LegacyUrlResolver
{
    /**
     * @param  array<string, mixed>  $query
     */
    public function resolve(string $path, array $query = []): ?string
    {
        $path = '/'.ltrim(rawurldecode($path), '/');

        if ($redirect = $this->managedRedirect($path)) {
            $redirect->recordHit();

            return $redirect->to_url;
        }

        return $this->productPage($path)
            ?? $this->articlePage($path)
            ?? $this->fixedPage($path)
            ?? $this->productListing($path, $query)
            ?? $this->uploadedFile($path);
    }

    private function managedRedirect(string $path): ?Redirect
    {
        return Redirect::query()
            ->where('is_active', true)
            ->where('from_path', Redirect::normalizePath($path))
            ->first();
    }

    /**
     * /p123-ten-san-pham.html and /products/view/123.
     */
    private function productPage(string $path): ?string
    {
        if (! preg_match('#^/(?:p(\d+)-[^/]*\.html|products/view/(\d+))#i', $path, $matches)) {
            return null;
        }

        $legacyId = (int) ($matches[1] ?: $matches[2]);
        $product = Product::query()->published()->where('legacy_id', $legacyId)->first();

        return $product ? route('products.show', $product) : route('products.index');
    }

    /**
     * /a63-tieu-de.html and /articles/view/63. Some old articles were fixed pages (about, services).
     */
    private function articlePage(string $path): ?string
    {
        if (! preg_match('#^/(?:a(\d+)-[^/]*\.html|articles/view/(\d+))#i', $path, $matches)) {
            return null;
        }

        $legacyId = (int) ($matches[1] ?: $matches[2]);

        if ($fixed = config("legacy.article_pages.{$legacyId}")) {
            return url($fixed);
        }

        $post = Post::query()->where('is_published', true)->where('legacy_id', $legacyId)->first();

        return $post ? route('posts.show', $post) : route('posts.index');
    }

    private function fixedPage(string $path): ?string
    {
        $map = [
            'gioi-thieu.html' => route('about'),
            'dich-vu.html' => route('services'),
            'chung-chi.html' => route('about').'#chung-nhan',
            'lien-he.html' => route('contact'),
            'su-kien' => route('posts.index'),
            'articles' => route('posts.index'),
            'articles/index' => route('posts.index'),
            'articles/events' => route('posts.index'),
            'articles/projects' => route('projects.index'),
            'contacts/contactus' => route('contact'),
            'home' => route('home'),
            'home/index' => route('home'),
        ];

        return $map[strtolower(trim($path, '/'))] ?? null;
    }

    /**
     * /products/category:84/title:..., /products/brand:19/ith, /products/supplier:25, /products/key:TFBT, /products/search.
     *
     * @param  array<string, mixed>  $query
     */
    private function productListing(string $path, array $query): ?string
    {
        if (! preg_match('#^/products(?:/|$)#i', $path)) {
            return null;
        }

        $named = $this->namedParams($path);

        if (isset($named['category'])) {
            $category = ProductCategory::query()->published()->where('legacy_id', (int) $named['category'])->first();

            if ($category) {
                return route('products.category', $category);
            }
        }

        if (isset($named['brand'])) {
            $brand = Brand::findByLegacyKey('brand', (int) $named['brand']);

            if ($brand?->is_published) {
                return route('products.brand', $brand);
            }
        }

        if (isset($named['supplier'])) {
            $brand = Brand::findByLegacyKey('supplier', (int) $named['supplier']);

            if ($brand?->is_published) {
                return route('products.brand', $brand);
            }
        }

        if (isset($named['tag'])) {
            $tagRedirect = Redirect::query()->where('is_active', true)->where('from_path', '/products/tag:'.(int) $named['tag'])->first();

            if ($tagRedirect) {
                return $tagRedirect->to_url;
            }
        }

        $term = $named['key'] ?? null;
        $term = $term !== null ? base64_decode(strtr($term, '-_', '+/'), true) : null;
        $term = $term ?: ($query['keyword'] ?? $query['q'] ?? $query['data']['Product']['keyword'] ?? null);

        if (is_string($term) && trim($term) !== '' && mb_check_encoding($term, 'UTF-8')) {
            return route('products.index', ['q' => Str::limit(trim($term), 100, '')]);
        }

        return route('products.index');
    }

    /**
     * Old upload paths (/files/products/x.jpg, /media/Klueber/catalogue.pdf) now live under /uploads/legacy/.
     */
    private function uploadedFile(string $path): ?string
    {
        if (! preg_match('#^/(files|media)/(.+)$#i', $path, $matches)) {
            return null;
        }

        $extension = strtolower(pathinfo($matches[2], PATHINFO_EXTENSION));

        if (! in_array($extension, config('legacy.allowed_extensions'), true) || str_contains($matches[2], '..')) {
            return null;
        }

        $relative = config('legacy.uploads_prefix').'/'.strtolower($matches[1]).'/'.$matches[2];

        return Storage::disk('public')->exists($relative) ? media_url($relative) : null;
    }

    /**
     * Parses CakePHP named params such as "category:84" or "brand:19" from any path segment.
     *
     * @return array<string, string>
     */
    private function namedParams(string $path): array
    {
        $named = [];

        foreach (explode('/', $path) as $segment) {
            if (preg_match('/^([a-z]+):(.+)$/i', $segment, $matches)) {
                $named[strtolower($matches[1])] ??= $matches[2];
            }
        }

        return $named;
    }
}
