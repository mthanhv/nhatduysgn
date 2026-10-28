<?php

namespace App\Models;

use Database\Factories\ServiceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

#[Fillable(['title', 'slug', 'summary', 'description', 'image', 'gallery', 'equipment', 'is_published', 'sort_order', 'meta_title', 'meta_description', 'translations'])]
class Service extends Model
{
    /** @use HasFactory<ServiceFactory> */
    use HasFactory, HasTranslations, HasUniqueSlug, Publishable;

    protected function casts(): array
    {
        return [
            'gallery' => 'array',
            'equipment' => 'array',
            'is_published' => 'boolean',
        ];
    }

    /**
     * Fields editors can fill in per language; the first one decides whether a record counts as translated.
     *
     * @return array<int, string>
     */
    public function translatable(): array
    {
        return ['title', 'summary', 'description', 'meta_title', 'meta_description'];
    }

    /**
     * The main image followed by the extra ones, without blanks or repeats.
     *
     * @return Collection<int, string>
     */
    public function images(): Collection
    {
        return collect([$this->image])->merge($this->gallery ?? [])->filter()->unique()->values();
    }

    public function slugSource(): string
    {
        return 'title';
    }
}
