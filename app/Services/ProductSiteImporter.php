<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use InvalidArgumentException;

/**
 * Imports only the products of another old company site with the same CakePHP structure (config
 * legacy.product_sites), e.g. thietbinongong.com.
 *
 * Old IDs are stored as "site:id" (product legacy_key, brand legacy_keys) so they never clash with
 * nhatduysgn.com. Products go straight into the groups that list the site's category IDs in
 * config/catalog.php. A product whose code (or name) already exists among nhatduysgn.com's products is
 * not duplicated: the existing product is linked to the old ID instead, so its old URL still redirects.
 */
class ProductSiteImporter extends LegacyImporter
{
    public const STEPS = ['files', 'brands', 'products'];

    /** @var array<int, array{old: string, existing: int, name: string}> */
    private array $duplicates = [];

    public function __construct(
        private string $site,
        LegacyHtml $html,
        CatalogRestructurer $catalog,
        string $connection,
        ?string $webroot,
    ) {
        parent::__construct($html, $catalog, $connection, $webroot);
        $this->uploadsPrefix = config("legacy.product_sites.{$site}.uploads_prefix");
    }

    public static function forSite(string $site): self
    {
        $config = config("legacy.product_sites.{$site}") ?? throw new InvalidArgumentException("Unknown product site [{$site}].");

        return new self(
            $site,
            new LegacyHtml('/uploads/'.$config['uploads_prefix'], $config['hosts']),
            app(CatalogRestructurer::class),
            $config['connection'],
            $config['webroot'],
        );
    }

    /**
     * @return array<int, array{old: string, existing: int, name: string}> old products not imported because they already exist
     */
    public function duplicates(): array
    {
        return $this->duplicates;
    }

    public function importProducts(): int
    {
        $this->ensureMaps();
        $this->catalog->run();
        $count = 0;
        $existingByCode = $this->ownProducts();

        $this->db()->table('products')->orderBy('id')->chunk(100, function ($rows) use (&$count, $existingByCode): void {
            foreach ($rows as $row) {
                $key = "{$this->site}:{$row->id}";
                $linked = Product::query()->where('legacy_key', $key)->first();
                $duplicate = $linked?->legacy_id !== null ? $linked : $existingByCode->get(self::matchKey($row->code, $row->title));

                if ($duplicate) {
                    $this->linkDuplicate($duplicate, $key, $row);

                    continue;
                }

                if ($linked && $this->skipExisting) {
                    continue;
                }

                Product::withoutTimestamps(function () use ($row, $linked, $key): void {
                    $product = $linked ?? new Product(['legacy_key' => $key]);
                    $product->fill($this->productAttributes($row) + [
                        'product_category_id' => ProductCategory::findByLegacyId((int) $row->product_category_id, $this->site)?->id,
                    ]);
                    $this->stampFromLegacy($product, $row)->save();
                });

                $count++;
            }
        });

        $this->summarizeCategories();

        return $count;
    }

    public function uploadFolders(): array
    {
        return ['files/products', 'files/suppliers', 'media'];
    }

    /**
     * Only brands and suppliers that the site's products use.
     */
    protected function brandRows(string $table): Builder
    {
        $column = $table === 'brands' ? 'brand_id' : 'supplier_id';

        return $this->db()->table($table)->whereIn('id', $this->db()->table('products')->whereNotNull($column)->select($column));
    }

    protected function brandKey(string $type, int $id): string
    {
        return "{$this->site}:{$type}:{$id}";
    }

    /**
     * Products imported from nhatduysgn.com, by normalised code (or name when there is no code).
     *
     * @return Collection<string, Product>
     */
    private function ownProducts(): Collection
    {
        return Product::query()->whereNotNull('legacy_id')->with('category.parent')->get(['id', 'name', 'code', 'legacy_key', 'product_category_id'])
            ->keyBy(fn (Product $product) => self::matchKey($product->code, $product->name));
    }

    private function linkDuplicate(Product $product, string $key, object $row): void
    {
        if ($product->legacy_key === null) {
            $product->forceFill(['legacy_key' => $key])->saveQuietly();
        }

        // This site's categories are finer (e.g. expanders for boilers vs heat exchangers): adopt its sub-group,
        // but never move a product to a different top-level group.
        $target = ProductCategory::findByLegacyId((int) $row->product_category_id, $this->site);
        $current = $product->category;

        if ($target && $current && $target->id !== $current->id && self::rootOf($target) === self::rootOf($current)) {
            $product->forceFill(['product_category_id' => $target->id])->saveQuietly();
        }

        $this->duplicates[] = ['old' => $key, 'existing' => $product->id, 'name' => $this->cleanTitle($row->title)];
    }

    private static function rootOf(ProductCategory $category): int
    {
        return $category->ancestors()->first()?->id ?? $category->id;
    }

    /**
     * "JCB-163XR5" and "jcb 163xr5" match; products without a code match on their name.
     */
    public static function matchKey(?string $code, ?string $name): string
    {
        $code = mb_strtoupper(preg_replace('/[\s\-_.]+/u', '', (string) $code));

        return $code !== '' ? 'code:'.$code : 'name:'.mb_strtolower(trim(preg_replace('/\s+/u', ' ', (string) $name)));
    }
}
