<?php

namespace App\Models;

use Database\Factories\BrandFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug', 'logo', 'country', 'website', 'description', 'is_featured', 'is_published', 'sort_order', 'legacy_keys'])]
class Brand extends Model
{
    /** @use HasFactory<BrandFactory> */
    use HasFactory, HasUniqueSlug, Publishable;

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
            'legacy_keys' => 'array',
        ];
    }

    /**
     * Finds the brand an old site brand/supplier ID was merged into ("brand" or "supplier" + legacy ID).
     */
    public static function findByLegacyKey(string $type, int $legacyId): ?self
    {
        return self::query()->where('legacy_keys', 'like', '%"'.$type.':'.$legacyId.'"%')->first();
    }

    /**
     * @return HasMany<Product, $this>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
