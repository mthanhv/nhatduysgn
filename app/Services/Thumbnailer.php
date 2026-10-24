<?php

namespace App\Services;

use GdImage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Resized WebP copies of uploaded images, so lists don't download multi-megabyte originals (some old product
 * photos are 7 MB). A thumbnail is created on its first request and saved under uploads/_thumbs/{width}/,
 * where the web server then serves it directly like any other file.
 */
class Thumbnailer
{
    public const WIDTHS = [160, 320, 640, 960, 1280, 1600];

    public const FOLDER = '_thumbs';

    private const SOURCE_TYPES = ['jpg', 'jpeg', 'png', 'webp'];

    /** Images larger than this many pixels are served as-is rather than decoded (memory on shared hosting). */
    private const MAX_PIXELS = 40_000_000;

    /**
     * URL of the thumbnail at least $width pixels wide, or the original URL for files that can't be resized
     * (SVG, GIF, external URLs, theme assets).
     */
    public static function url(?string $path, int $width): ?string
    {
        if (blank($path) || Str::startsWith($path, ['http://', 'https://', '/']) || ! in_array(Str::lower(pathinfo($path, PATHINFO_EXTENSION)), self::SOURCE_TYPES, true)) {
            return media_url($path);
        }

        $size = collect(self::WIDTHS)->first(fn (int $allowed) => $allowed >= $width) ?? max(self::WIDTHS);

        return Storage::disk('public')->url(self::FOLDER."/{$size}/{$path}.webp");
    }

    /**
     * Creates (once) the thumbnail for an uploads-disk path and returns its file path, or null when the
     * request is not for a resizable upload.
     */
    public function make(int $width, string $path): ?string
    {
        $disk = Storage::disk('public');

        if (! in_array($width, self::WIDTHS, true) || str_contains($path, '..') || str_starts_with($path, self::FOLDER.'/')
            || ! in_array(Str::lower(pathinfo($path, PATHINFO_EXTENSION)), self::SOURCE_TYPES, true) || ! $disk->exists($path)) {
            return null;
        }

        $target = $disk->path(self::FOLDER."/{$width}/{$path}.webp");

        if (is_file($target)) {
            return $target;
        }

        $source = $disk->path($path);
        $info = @getimagesize($source);

        if ($info === false || $info[0] * $info[1] > self::MAX_PIXELS) {
            return null;
        }

        $image = match ($info[2]) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($source),
            IMAGETYPE_PNG => @imagecreatefrompng($source),
            IMAGETYPE_WEBP => @imagecreatefromwebp($source),
            default => false,
        };

        if (! $image instanceof GdImage) {
            return null;
        }

        if (! imageistruecolor($image)) {
            imagepalettetotruecolor($image);
        }

        if ($info[0] > $width) {
            $image = imagescale($image, $width, -1, IMG_BICUBIC) ?: $image;
        }

        imagealphablending($image, false);
        imagesavealpha($image, true);
        File::ensureDirectoryExists(dirname($target));
        $saved = imagewebp($image, $target, 80);
        imagedestroy($image);

        return $saved ? $target : null;
    }
}
