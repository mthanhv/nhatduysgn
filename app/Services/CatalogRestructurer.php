<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Redirect;
use App\Models\Setting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Applies the product group structure in config/catalog.php.
 *
 * Old site categories listed under a group are merged into it: their products move there, their old IDs are
 * kept on the group (so old URLs still redirect) and the emptied old categories are deleted. Re-running is
 * safe; groups applied before keep the name, icon, image and texts editors gave them.
 */
class CatalogRestructurer
{
    /** Setting holding the keys of groups already applied, so later runs leave editors' changes alone. */
    public const APPLIED_SETTING = 'catalog_applied_groups';

    /** @var array<int, string> */
    private array $appliedKeys = [];

    /** @var array<int, string> group keys applied before this run */
    private array $previouslyApplied = [];

    /** @var array<int, int> old category ID => group ID */
    private array $groupByLegacyId = [];

    /** @var array<int, int> old product ID => group ID */
    private array $groupByProduct = [];

    /** @var array<int, int> */
    private array $managedIds = [];

    /** @var array{created: int, moved: int, deleted: array<int, string>, kept: array<int, string>} */
    private array $report;

    /**
     * @return array{before: int, after: int, created: int, moved: int, deleted: array<int, string>, kept: array<int, string>}
     */
    public function run(bool $dryRun = false): array
    {
        $this->managedIds = $this->groupByLegacyId = $this->groupByProduct = [];
        $this->report = ['created' => 0, 'moved' => 0, 'deleted' => [], 'kept' => []];
        $before = ProductCategory::count();

        DB::beginTransaction();

        try {
            $this->appliedKeys = $this->previouslyApplied = Setting::query()->where('key', self::APPLIED_SETTING)->value('value') ?? [];
            $existing = ProductCategory::query()->get()->keyBy('id');
            $legacyMap = ProductCategory::legacyMap();

            foreach (config('catalog') as $index => $node) {
                $this->applyNode($node, null, ($index + 1) * 10, $existing, $legacyMap);
            }

            $this->moveProducts($existing);
            $this->removeMergedCategories($existing);
            Setting::putMany([self::APPLIED_SETTING => array_values(array_unique($this->appliedKeys))]);
            $after = ProductCategory::count();

            $dryRun ? DB::rollBack() : DB::commit();
        } catch (\Throwable $exception) {
            DB::rollBack();

            throw $exception;
        }

        return ['before' => $before, 'after' => $after] + $this->report;
    }

    /**
     * Old site product ID => key of the group it is explicitly moved to.
     *
     * @return array<int, string>
     */
    public static function productOverrides(): array
    {
        $overrides = [];

        foreach (self::flatten(config('catalog')) as $node) {
            foreach ($node['products'] ?? [] as $legacyProductId) {
                $overrides[$legacyProductId] = $node['key'];
            }
        }

        return $overrides;
    }

    /**
     * @return array<int, string> every group key in the structure
     */
    public static function keys(): array
    {
        return array_column(self::flatten(config('catalog')), 'key');
    }

    /**
     * Category IDs of other old sites listed on a node, as "site:id" (e.g. "tbn:68").
     *
     * @param  array<string, mixed>  $node
     * @return array<int, string>
     */
    private static function siteIds(array $node): array
    {
        $ids = [];

        foreach (array_keys(config('legacy.product_sites', [])) as $site) {
            foreach ($node[$site] ?? [] as $id) {
                $ids[] = "{$site}:{$id}";
            }
        }

        return $ids;
    }

    /**
     * @param  array<int, array<string, mixed>>  $nodes
     * @return array<int, array<string, mixed>>
     */
    private static function flatten(array $nodes): array
    {
        $flat = [];

        foreach ($nodes as $node) {
            $flat[] = $node;
            array_push($flat, ...self::flatten($node['children'] ?? []));
        }

        return $flat;
    }

    /**
     * @param  array<string, mixed>  $node
     * @param  Collection<int, ProductCategory>  $existing
     * @param  array<int, int>  $legacyMap  old category ID => category ID before restructuring
     */
    private function applyNode(array $node, ?int $parentId, int $sortOrder, Collection $existing, array $legacyMap): void
    {
        $legacyIds = $node['legacy'] ?? [];
        $sources = collect($legacyIds)->map(fn (int $legacyId) => $existing->get($legacyMap[$legacyId] ?? 0))->filter()->unique('id');

        $group = ProductCategory::query()->where('slug', $node['key'])->first() ?? new ProductCategory(['slug' => $node['key']]);
        // An old category that happens to have the group's slug is adopted with the standard name and icon;
        // after the first run editors own them.
        if (! in_array($node['key'], $this->appliedKeys, true)) {
            $group->fill([
                'name' => $node['name'],
                'icon' => $node['icon'] ?? null,
                'is_featured' => $node['featured'] ?? false,
                'is_published' => true,
                'sort_order' => $sortOrder,
            ]);
        }

        if (! $group->exists) {
            $this->report['created']++;
        }

        $sources = $sources->reject(fn (ProductCategory $source) => $source->is($group));

        foreach (['image', 'description', 'summary', 'meta_title', 'meta_description'] as $field) {
            if (blank($group->{$field})) {
                $group->{$field} = $sources->first(fn (ProductCategory $source) => filled($source->{$field}))?->{$field};
            }
        }

        $group->parent_id = $parentId;
        $group->legacy_ids = collect($group->legacy_ids ?? [])->merge($legacyIds)->merge(self::siteIds($node))->map(fn ($id) => (string) $id)->unique()->values()->all() ?: null;
        $group->save();
        $this->managedIds[] = $group->id;
        $this->appliedKeys[] = $node['key'];

        foreach ($legacyIds as $legacyId) {
            $this->groupByLegacyId[$legacyId] = $group->id;
        }

        foreach ($node['products'] ?? [] as $legacyProductId) {
            $this->groupByProduct[$legacyProductId] = $group->id;
        }

        foreach ($node['children'] ?? [] as $index => $child) {
            $this->applyNode($child, $group->id, $sortOrder + $index + 1, $existing, $legacyMap);
        }
    }

    /**
     * Runs once every group exists. Products of an old category go to the group of its first old ID (a category
     * merged earlier may hold several), then explicitly listed products go to their group.
     *
     * @param  Collection<int, ProductCategory>  $existing
     */
    private function moveProducts(Collection $existing): void
    {
        foreach ($existing->except($this->managedIds) as $category) {
            $target = $this->groupByLegacyId[(int) ($category->legacy_ids[0] ?? 0)] ?? null;

            if ($target) {
                $this->report['moved'] += Product::query()->where('product_category_id', $category->id)->update(['product_category_id' => $target]);
            }
        }

        foreach ($this->groupByProduct as $legacyProductId => $groupId) {
            $this->report['moved'] += Product::query()->where('legacy_id', $legacyProductId)
                ->where(fn ($query) => $query->whereNull('product_category_id')->orWhere('product_category_id', '!=', $groupId))
                ->update(['product_category_id' => $groupId]);
        }
    }

    /**
     * Deletes old categories whose IDs now all live on a group and that no longer hold products. Anything
     * else (groups created in the admin, old categories missing from the structure) is kept and reported.
     *
     * @param  Collection<int, ProductCategory>  $existing
     */
    private function removeMergedCategories(Collection $existing): void
    {
        $mapped = ProductCategory::query()->whereIn('id', $this->managedIds)->pluck('legacy_ids')->flatten()->map(fn ($id) => (string) $id)->flip();

        foreach ($existing->except($this->managedIds) as $category) {
            $ownIds = collect($category->legacy_ids ?? [])->map(fn ($id) => (string) $id);
            $merged = $ownIds->isNotEmpty() && $ownIds->every(fn (string $id) => $mapped->has($id));

            if ($merged && ! $category->products()->exists()) {
                $this->redirectRemovedGroup($category, $ownIds->first());
                ProductCategory::query()->where('parent_id', $category->id)->whereNotIn('id', $this->managedIds)->update(['parent_id' => null]);
                $category->delete();
                $this->report['deleted'][] = $category->name;
            } else {
                $this->report['kept'][] = $category->name;
            }
        }
    }

    /**
     * A standard group removed from config/catalog.php was a public page: send its URL to the group that took
     * over its old IDs. (Old site categories imported moments ago never had a page, so they get no redirect.)
     */
    private function redirectRemovedGroup(ProductCategory $category, string $firstLegacyId): void
    {
        if (! in_array($category->slug, $this->previouslyApplied, true)) {
            return;
        }

        $target = ProductCategory::query()->whereIn('id', $this->managedIds)->get()
            ->first(fn (ProductCategory $group) => in_array($firstLegacyId, array_map('strval', $group->legacy_ids ?? []), true));

        if ($target) {
            Redirect::query()->updateOrCreate(
                ['from_path' => '/danh-muc/'.$category->slug],
                ['to_url' => route('products.category', $target, false), 'status_code' => 301, 'is_active' => true, 'note' => "Nhóm “{$category->name}” đã gộp vào “{$target->name}”."],
            );
        }
    }
}
