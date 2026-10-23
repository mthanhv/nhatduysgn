<?php

namespace App\Models;

use Database\Factories\RedirectFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['from_path', 'to_url', 'status_code', 'is_active', 'note'])]
class Redirect extends Model
{
    /** @use HasFactory<RedirectFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::saving(function (Redirect $redirect): void {
            $redirect->from_path = self::normalizePath($redirect->from_path);
        });
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'last_hit_at' => 'datetime',
        ];
    }

    /**
     * Stored paths are lowercase, start with "/" and have no trailing slash, so lookups are exact matches.
     */
    public static function normalizePath(string $path): string
    {
        // Not parse_url(): CakePHP-style segments such as "tag:9" are misread as a port.
        $path = preg_replace('#^https?://[^/]+#i', '', trim($path)) ?? $path;
        $path = strtok($path, '?#') ?: '/';
        $path = '/'.trim(rawurldecode($path), '/');

        return mb_strtolower($path);
    }

    public function recordHit(): void
    {
        $this->newQuery()->whereKey($this->getKey())->increment('hits', 1, ['last_hit_at' => now()]);
    }
}
