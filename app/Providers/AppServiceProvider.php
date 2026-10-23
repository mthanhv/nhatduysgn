<?php

namespace App\Providers;

use App\Models\ProductCategory;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Contact form: a person rarely sends more than a few messages; bots send hundreds.
        RateLimiter::for('contact-form', fn (Request $request) => [
            Limit::perMinute(3)->by($request->ip()),
            Limit::perDay(20)->by($request->ip()),
        ]);

        View::composer('layouts.site', function ($view): void {
            $view->with('footerCategories', ProductCategory::query()
                ->published()
                ->whereNull('parent_id')
                ->orderByDesc('is_featured')
                ->ordered()
                ->limit(5)
                ->get(['id', 'name', 'slug']));
        });
    }
}
