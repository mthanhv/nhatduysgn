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
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/gioi-thieu', AboutController::class)->name('about');
Route::get('/dich-vu', ServiceController::class)->name('services');

Route::get('/san-pham', [ProductController::class, 'index'])->name('products.index');
Route::get('/san-pham/{product:slug}', [ProductController::class, 'show'])->name('products.show');
Route::get('/danh-muc/{category:slug}', [ProductController::class, 'category'])->name('products.category');
Route::get('/thuong-hieu/{brand:slug}', [ProductController::class, 'brand'])->name('products.brand');

Route::get('/du-an', [ProjectController::class, 'index'])->name('projects.index');
Route::get('/du-an/{project:slug}', [ProjectController::class, 'show'])->name('projects.show');

Route::get('/tin-tuc', [PostController::class, 'index'])->name('posts.index');
Route::get('/tin-tuc/{post:slug}', [PostController::class, 'show'])->name('posts.show');

Route::get('/lien-he', [ContactController::class, 'show'])->name('contact');
Route::post('/lien-he', [ContactController::class, 'store'])->middleware('throttle:contact-form')->name('contact.store');

Route::get('/sitemap.xml', [SitemapController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');

// Old CakePHP URLs (/p123-ten-san-pham.html, /products/category:84, /files/..., admin-managed redirects).
Route::fallback(LegacyRedirectController::class);
