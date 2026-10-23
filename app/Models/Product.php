<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[Fillable(['product_category_id', 'brand_id', 'name', 'slug', 'code', 'origin', 'summary', 'description', 'specifications', 'keywords', 'image', 'gallery', 'documents', 'is_featured', 'is_published', 'sort_order', 'meta_title', 'meta_description', 'legacy_id'])]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory, HasUniqueSlug, Publishable;

    protected static function booted(): void
    {
        static::saving(function (Product $product): void {
            $text = self::normalizeForSearch(implode(' ', array_filter([
                $product->name,
                $product->code,
                $product->brand?->name,
                $product->summary,
                $product->keywords,
            ])));

            // Compact code ("CX-5" → "cx5", "Loctite 243" → "loctite243") so users can type codes without separators.
            $compactCode = preg_replace('/[^a-z0-9]/', '', self::normalizeForSearch((string) $product->code));

            $product->search_text = trim($text.' '.$compactCode);
        });
    }

    protected function casts(): array
    {
        return [
            'gallery' => 'array',
            'documents' => 'array',
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<ProductCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    /**
     * @return BelongsTo<Brand, $this>
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * Accent-insensitive search so "keo khoa ren" matches "Keo khóa ren" and codes like "CX-5" match "cx5".
     */
    #[Scope]
    protected function search(Builder $query, ?string $term): void
    {
        $words = collect(preg_split('/\s+/', self::normalizeForSearch((string) $term)))->filter();

        foreach ($words as $word) {
            $query->where(function (Builder $query) use ($word): void {
                $query->where('search_text', 'like', '%'.$word.'%')
                    ->orWhere('search_text', 'like', '%'.str_replace('-', '', $word).'%');
            });
        }
    }

    public static function normalizeForSearch(string $value): string
    {
        return Str::of($value)->ascii('vi')->lower()->replaceMatches('/[^a-z0-9\-\s]/', ' ')->squish()->toString();
    }
}
