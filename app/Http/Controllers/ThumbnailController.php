<?php

namespace App\Http\Controllers;

use App\Services\Thumbnailer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Only reached the first time a thumbnail is requested; afterwards the saved file is served by the web server.
 */
class ThumbnailController extends Controller
{
    public function __invoke(Thumbnailer $thumbnailer, int $width, string $path): BinaryFileResponse|RedirectResponse
    {
        $source = Str::beforeLast($path, '.webp');
        $file = $thumbnailer->make($width, $source);

        if ($file === null) {
            abort_unless(is_file(public_path('uploads/'.$source)) && ! str_contains($source, '..'), 404);

            return redirect()->to(media_url($source));
        }

        return response()->file($file, ['Content-Type' => 'image/webp', 'Cache-Control' => 'public, max-age=31536000, immutable']);
    }
}
