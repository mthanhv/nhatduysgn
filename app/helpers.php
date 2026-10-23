<?php

use App\Models\Setting;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
