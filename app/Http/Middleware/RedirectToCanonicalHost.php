<?php

namespace App\Http\Middleware;

use App\Services\LegacyUrlResolver;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * In production every page has one address: http:// and www. (or any other domain pointed at the server)
 * get a 301 to APP_URL, so search engines don't split ranking between duplicates.
 *
 * Domains of the company's other old sites (config legacy.product_sites.*.hosts, e.g. thietbinongong.com)
 * never show pages themselves: their old URLs go to the matching new page on APP_URL, anything else to the
 * home page, so the new site is not indexed twice under two domains.
 */
class RedirectToCanonicalHost
{
    public function __construct(private LegacyUrlResolver $resolver) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! app()->isProduction() || $request->is('up')) {
            return $next($request);
        }

        $canonical = parse_url((string) config('app.url'));
        $scheme = $canonical['scheme'] ?? 'https';
        $host = $canonical['host'] ?? null;
        $legacyHosts = collect(config('legacy.product_sites', []))->pluck('hosts')->flatten()->all();

        if ($host === null) {
            return $next($request);
        }

        $root = $scheme.'://'.$host;

        if (in_array($request->getHost(), $legacyHosts, true)) {
            $target = $this->resolver->resolve($request->path(), $request->query(), $request->getHost());
            $parts = $target !== null ? parse_url($target) : [];

            return redirect()->to($root.($parts['path'] ?? '/').(isset($parts['query']) ? '?'.$parts['query'] : '').(isset($parts['fragment']) ? '#'.$parts['fragment'] : ''), 301);
        }

        if ($request->getHost() !== $host || $request->getScheme() !== $scheme) {
            return redirect()->to($root.$request->getRequestUri(), 301);
        }

        return $next($request);
    }
}
