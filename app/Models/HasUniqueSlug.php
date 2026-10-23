<?php

namespace App\Models;

use Illuminate\Support\Str;

/**
 * Fills the `slug` column from a source attribute (Vietnamese-aware) and keeps it unique per table.
 */
trait HasUniqueSlug
{
    public static function bootHasUniqueSlug(): void
    {
        static::saving(function (self $model): void {
            $source = $model->getAttribute($model->slugSource());

            if (blank($model->slug) && filled($source)) {
                $model->slug = static::uniqueSlug($source, $model->getKey());
            } elseif ($model->isDirty('slug') && filled($model->slug)) {
                $model->slug = static::uniqueSlug($model->slug, $model->getKey());
            }
        });
    }

    public function slugSource(): string
    {
        return 'name';
    }

    public static function uniqueSlug(string $value, int|string|null $ignoreId = null): string
    {
        $base = Str::limit(Str::slug($value, '-', 'vi'), 180, '') ?: 'item';
        $slug = $base;
        $suffix = 2;

        while (static::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
