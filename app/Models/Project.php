<?php

namespace App\Models;

use Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

#[Fillable(['title', 'slug', 'industry', 'client', 'year', 'summary', 'content', 'cover', 'gallery', 'tags', 'is_published', 'sort_order', 'meta_title', 'meta_description', 'translations'])]
class Project extends Model
{
    /** @use HasFactory<ProjectFactory> */
    use HasFactory, HasTranslations, HasUniqueSlug, Publishable;

    protected function casts(): array
    {
        return [
            'gallery' => 'array',
            'tags' => 'array',
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
        return ['title', 'summary', 'content', 'industry', 'client', 'meta_title', 'meta_description'];
    }

    /**
     * The cover followed by the extra images, without blanks or repeats.
     *
     * @return Collection<int, string>
     */
    public function images(): Collection
    {
        return collect([$this->cover])->merge($this->gallery ?? [])->filter()->unique()->values();
    }

    public function slugSource(): string
    {
        return 'title';
    }
}
