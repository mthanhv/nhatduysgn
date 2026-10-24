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
use App\Services\LegacyUrlResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// A slug that no longer exists (renamed, deleted or merged) goes through the redirect layer before 404,
// so redirects added by editors or by catalog:restructure also work for these URLs.
$redirectOr404 = fn (Request $request) => app(LegacyRedirectController::class)($request, app(LegacyUrlResolver::class));

Route::get('/', HomeController::class)->name('home');
Route::get('/gioi-thieu', AboutController::class)->name('about');
Route::get('/dich-vu', ServiceController::class)->name('services');

Route::get('/san-pham', [ProductController::class, 'index'])->name('products.index');
Route::get('/san-pham/{product:slug}', [ProductController::class, 'show'])->name('products.show')->missing($redirectOr404);
Route::get('/danh-muc/{category:slug}', [ProductController::class, 'category'])->name('products.category')->missing($redirectOr404);
Route::get('/thuong-hieu/{brand:slug}', [ProductController::class, 'brand'])->name('products.brand')->missing($redirectOr404);

Route::get('/du-an', [ProjectController::class, 'index'])->name('projects.index');
Route::get('/du-an/{project:slug}', [ProjectController::class, 'show'])->name('projects.show')->missing($redirectOr404);

Route::get('/tin-tuc', [PostController::class, 'index'])->name('posts.index');
Route::get('/tin-tuc/{post:slug}', [PostController::class, 'show'])->name('posts.show')->missing($redirectOr404);

Route::get('/lien-he', [ContactController::class, 'show'])->name('contact');
Route::post('/lien-he', [ContactController::class, 'store'])->middleware('throttle:contact-form')->name('contact.store');

Route::get('/sitemap.xml', [SitemapController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');
Route::get('/llms.txt', [SitemapController::class, 'llms'])->name('llms');
Route::get('/llms-full.txt', [SitemapController::class, 'llmsFull'])->name('llms.full');

// First request of a resized image; once created it is a static file under public/uploads/_thumbs.
Route::get('/uploads/_thumbs/{width}/{path}', ThumbnailController::class)->where(['width' => '[0-9]+', 'path' => '.+\.webp'])->name('thumbnail');

// Old CakePHP URLs (/p123-ten-san-pham.html, /products/category:84, /files/..., admin-managed redirects).
Route::fallback(LegacyRedirectController::class);
