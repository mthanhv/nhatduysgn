<?php

use App\Models\Setting;
use App\Services\Thumbnailer;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

if (! function_exists('thumb_url')) {
    /**
     * Resized WebP version of an uploaded image (at least $width px wide); see App\Services\Thumbnailer.
     */
    function thumb_url(?string $path, int $width, ?string $fallback = null): ?string
    {
        return blank($path) ? $fallback : Thumbnailer::url($path, $width);
    }
}

if (! function_exists('lroute')) {
    /**
     * URL of a public page in the current language: route('products.show') on the Vietnamese site,
     * route('en.products.show') on the English one.
     */
    function lroute(string $name, mixed $parameters = [], bool $absolute = true): string
    {
        $localized = app()->getLocale() === 'en' ? 'en.'.$name : $name;

        return route(Route::has($localized) ? $localized : $name, $parameters, $absolute);
    }
}

if (! function_exists('locale_url')) {
    /**
     * The current public page in another language (same route and parameters, query string kept),
     * or that language's home page when the page has no counterpart.
     */
    function locale_url(string $locale): string
    {
        $route = request()->route();
        $name = Str::after((string) $route?->getName(), 'en.');
        $target = $locale === 'en' ? 'en.'.$name : $name;

        if ($name === '' || ! Route::has($target) || ! Route::has('en.'.$name)) {
            return route($locale === 'en' ? 'en.home' : 'home');
        }

        $query = request()->query();

        return route($target, $route->parameters()).($query ? '?'.http_build_query($query) : '');
    }
}

if (! function_exists('media_url')) {
    /**
     * Public URL for a file on the uploads disk; absolute URLs and site-relative paths pass through.
     */
    function media_url(?string $path, ?string $fallback = null): ?string
    {
        if (blank($path)) {
            return $fallback;
        }

        if (Str::startsWith($path, ['http://', 'https://', '/'])) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }
}

if (! function_exists('setting')) {
    function setting(string $key, mixed $default = null): mixed
    {
        return Setting::get($key, $default);
    }
}

if (! function_exists('tel_href')) {
    /**
     * "028 7300 7797" → "tel:02873007797".
     */
    function tel_href(?string $phone): string
    {
        return 'tel:'.preg_replace('/[^0-9+]/', '', (string) $phone);
    }
}
