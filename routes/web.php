<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LegacyRedirectController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\ThumbnailController;
use App\Http\Middleware\SetLocale;
use App\Services\LegacyUrlResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// A slug that no longer exists (renamed, deleted or merged) goes through the redirect layer before 404,
// so redirects added by editors or by catalog:restructure also work for these URLs.
$redirectOr404 = fn (Request $request) => app(LegacyRedirectController::class)($request, app(LegacyUrlResolver::class));

// Public pages exist once per language: Vietnamese at the current URLs, English under /en with English path words
// and route names prefixed "en." (see lroute()). Slugs are shared by both languages.
$publicPages = function (array $path) use ($redirectOr404): void {
    Route::get('/', HomeController::class)->name('home');
    Route::get($path['about'], AboutController::class)->name('about');
    Route::get($path['services'], ServiceController::class)->name('services');

    Route::get($path['products'], [ProductController::class, 'index'])->name('products.index');
    Route::get($path['products'].'/{product:slug}', [ProductController::class, 'show'])->name('products.show')->missing($redirectOr404);
    Route::get($path['category'].'/{category:slug}', [ProductController::class, 'category'])->name('products.category')->missing($redirectOr404);
    Route::get($path['brand'].'/{brand:slug}', [ProductController::class, 'brand'])->name('products.brand')->missing($redirectOr404);

    Route::get($path['projects'], [ProjectController::class, 'index'])->name('projects.index');
    Route::get($path['projects'].'/{project:slug}', [ProjectController::class, 'show'])->name('projects.show')->missing($redirectOr404);

    Route::get($path['posts'], [PostController::class, 'index'])->name('posts.index');
    Route::get($path['posts'].'/{post:slug}', [PostController::class, 'show'])->name('posts.show')->missing($redirectOr404);

    Route::get($path['contact'], [ContactController::class, 'show'])->name('contact');
    Route::post($path['contact'], [ContactController::class, 'store'])->middleware('throttle:contact-form')->name('contact.store');
};

Route::middleware(SetLocale::class.':vi')->group(fn () => $publicPages([
    'about' => '/gioi-thieu', 'services' => '/dich-vu', 'products' => '/san-pham', 'category' => '/danh-muc',
    'brand' => '/thuong-hieu', 'projects' => '/du-an', 'posts' => '/tin-tuc', 'contact' => '/lien-he',
]));

Route::prefix('en')->name('en.')->middleware(SetLocale::class.':en')->group(fn () => $publicPages([
    'about' => '/about', 'services' => '/services', 'products' => '/products', 'category' => '/category',
    'brand' => '/brand', 'projects' => '/projects', 'posts' => '/news', 'contact' => '/contact',
]));

Route::get('/sitemap.xml', [SitemapController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');
Route::get('/llms.txt', [SitemapController::class, 'llms'])->name('llms');
Route::get('/llms-full.txt', [SitemapController::class, 'llmsFull'])->name('llms.full');

// First request of a resized image; once created it is a static file under public/uploads/_thumbs.
Route::get('/uploads/_thumbs/{width}/{path}', ThumbnailController::class)->where(['width' => '[0-9]+', 'path' => '.+\.webp'])->name('thumbnail');

// Old CakePHP URLs (/p123-ten-san-pham.html, /products/category:84, /files/..., admin-managed redirects).
Route::fallback(LegacyRedirectController::class);
