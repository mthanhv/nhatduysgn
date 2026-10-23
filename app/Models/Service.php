<?php

namespace App\Models;

use Database\Factories\ServiceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['title', 'slug', 'summary', 'description', 'image', 'equipment', 'is_published', 'sort_order', 'meta_title', 'meta_description'])]
class Service extends Model
{
    /** @use HasFactory<ServiceFactory> */
    use HasFactory, HasUniqueSlug, Publishable;

    protected function casts(): array
    {
        return [
            'equipment' => 'array',
            'is_published' => 'boolean',
        ];
    }

    public function slugSource(): string
    {
        return 'title';
    }
}
