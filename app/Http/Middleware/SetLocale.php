<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sets the public site's language from the route group (Vietnamese at the root, English under /en).
 */
class SetLocale
{
    public const LOCALES = ['vi', 'en'];

    public function handle(Request $request, Closure $next, string $locale = 'vi'): Response
    {
        app()->setLocale(in_array($locale, self::LOCALES, true) ? $locale : 'vi');

        return $next($request);
    }
}
