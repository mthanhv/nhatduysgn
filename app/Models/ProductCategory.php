<?php

namespace App\Models;

use Database\Factories\ProductCategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

#[Fillable(['parent_id', 'name', 'slug', 'summary', 'description', 'icon', 'image', 'is_featured', 'is_published', 'sort_order', 'meta_title', 'meta_description', 'legacy_ids', 'translations'])]
class ProductCategory extends Model
{
    /** @use HasFactory<ProductCategoryFactory> */
    use HasFactory, HasTranslations, HasUniqueSlug, Publishable;

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
            'legacy_ids' => 'array',
        ];
    }

    /**
     * Fields editors can fill in per language; the first one decides whether a record counts as translated.
     *
     * @return array<int, string>
     */
    public function translatable(): array
    {
        return ['name', 'summary', 'description', 'meta_title', 'meta_description'];
    }

    /**
     * Finds the group an old site category ID was merged into; $site names another old site (legacy.product_sites).
     */
    public static function findByLegacyId(int $legacyId, ?string $site = null): ?self
    {
        $key = $site === null ? (string) $legacyId : "{$site}:{$legacyId}";

        return self::query()->where('legacy_ids', 'like', '%"'.$key.'"%')->first();
    }

    /**
     * Old site category ID => group ID, for every group that absorbed old categories.
     *
     * @return array<int, int>
     */
    public static function legacyMap(): array
    {
        $map = [];

        foreach (self::query()->whereNotNull('legacy_ids')->get(['id', 'legacy_ids']) as $category) {
            foreach ($category->legacy_ids as $legacyId) {
                if (ctype_digit((string) $legacyId)) {
                    $map[(int) $legacyId] = $category->id;
                }
            }
        }

        return $map;
    }

    /**
     * Published top-level groups with their published sub-groups and published product counts; `total_products` is
     * the group's own products plus those filed under its sub-groups.
     *
     * @return EloquentCollection<int, ProductCategory>
     */
    public static function publishedTree(): EloquentCollection
    {
        $published = fn ($query) => $query->published();

        return self::query()
            ->published()
            ->whereNull('parent_id')
            ->withCount(['products' => $published])
            ->with(['children' => fn ($query) => $query->published()->ordered()->withCount(['products' => $published])])
            ->ordered()
            ->get()
            ->each(fn (self $root) => $root->setAttribute('total_products', $root->products_count + $root->children->sum('products_count')));
    }

    /**
     * Renumbers sort_order to follow the tree (each group directly followed by its sub-groups, siblings keeping their
     * relative order), so one flat sort_order list shows and drags the whole tree in the admin.
     */
    public static function renumberAsTree(): void
    {
        $childrenByParent = self::query()->orderBy('sort_order')->orderBy('id')->get(['id', 'parent_id', 'sort_order'])
            ->groupBy(fn (self $category): int => (int) $category->parent_id);
        $position = 0;

        $renumber = function (int $parentId) use (&$renumber, &$position, $childrenByParent): void {
            foreach ($childrenByParent->get($parentId, []) as $category) {
                $position++;

                if ($category->sort_order !== $position) {
                    self::query()->whereKey($category->id)->update(['sort_order' => $position]);
                }

                $renumber($category->id);
            }
        };

        $renumber(0);
    }

    /**
     * @return BelongsTo<ProductCategory, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * @return HasMany<ProductCategory, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * @return HasMany<Product, $this>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * The group's own image, or a photo of one of its products (featured first) until an editor uploads one.
     */
    public function coverImage(): ?string
    {
        return $this->image ?: Product::query()
            ->published()
            ->whereIn('product_category_id', $this->descendantAndSelfIds())
            ->whereNotNull('image')
            ->orderByDesc('is_featured')
            ->ordered()
            ->value('image');
    }

    /**
     * IDs of this category and every descendant, so a parent listing includes products of its sub-groups.
     *
     * @return Collection<int, int>
     */
    public function descendantAndSelfIds(): Collection
    {
        $ids = collect([$this->id]);
        $frontier = [$this->id];

        while ($frontier !== []) {
            $frontier = self::query()->whereIn('parent_id', $frontier)->pluck('id')->all();
            $ids = $ids->merge($frontier);
        }

        return $ids->unique()->values();
    }

    /**
     * @return Collection<int, ProductCategory>
     */
    public function ancestors(): Collection
    {
        $ancestors = collect();
        $current = $this->parent;

        while ($current && ! $ancestors->contains('id', $current->id)) {
            $ancestors->prepend($current);
            $current = $current->parent;
        }

        return $ancestors;
    }
}
