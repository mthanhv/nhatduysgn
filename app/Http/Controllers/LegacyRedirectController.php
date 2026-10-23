<?php

namespace App\Http\Controllers;

use App\Services\LegacyUrlResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Catch-all route: sends old site URLs to their new pages with a permanent redirect, otherwise 404.
 */
class LegacyRedirectController extends Controller
{
    public function __invoke(Request $request, LegacyUrlResolver $resolver): RedirectResponse
    {
        $target = $resolver->resolve($request->path(), $request->query());

        abort_if($target === null, 404);

        return redirect()->to($target, 301);
    }
}
