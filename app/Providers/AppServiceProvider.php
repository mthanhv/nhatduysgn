<?php

namespace App\Providers;

use App\Filament\Support\HtmlSource;
use App\Models\Brand;
use App\Models\ProductCategory;
use Filament\Forms\Components\RichEditor;
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
        // Every admin rich editor gets an "HTML" button to view and edit the content's source.
        RichEditor::configureUsing(fn (RichEditor $editor) => HtmlSource::apply($editor));

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

            $view->with('navCategories', ProductCategory::publishedTree());

            $view->with('navBrands', Brand::query()
                ->published()
                ->withCount(['products' => fn ($query) => $query->published()])
                ->orderByDesc('is_featured')
                ->ordered()
                ->get(['id', 'name', 'slug', 'logo']));
        });
    }
}
