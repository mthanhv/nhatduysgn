<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogRestructurerTest extends TestCase
{
    use RefreshDatabase;

    private ProductCategory $oldParent;

    private ProductCategory $oldChild;

    protected function setUp(): void
    {
        parent::setUp();

        config(['catalog' => [
            [
                'key' => 'dung-cu-xiet-bu-long', 'name' => 'Dụng cụ xiết bu lông', 'icon' => 'wrench', 'featured' => true,
                'legacy' => [85],
                'children' => [
                    ['key' => 'dau-xiet-thuy-luc', 'name' => 'Đầu xiết thủy lực & bơm', 'legacy' => [114, 117]],
                    ['key' => 'sung-xiet-bu-long', 'name' => 'Súng xiết bu lông', 'products' => [703]],
                ],
            ],
        ]]);

        $this->oldParent = ProductCategory::factory()->create(['name' => 'Dụng Cụ ITH', 'legacy_ids' => ['85'], 'image' => 'categories/ith.jpg']);
        $this->oldChild = ProductCategory::factory()->create(['name' => 'Loại xoay', 'parent_id' => $this->oldParent->id, 'legacy_ids' => ['114']]);
        ProductCategory::factory()->create(['name' => 'Bơm', 'parent_id' => $this->oldParent->id, 'legacy_ids' => ['117']]);
    }

    public function test_old_categories_are_merged_into_the_standard_groups(): void
    {
        $product = Product::factory()->create(['product_category_id' => $this->oldChild->id]);
        $misfiled = Product::factory()->create(['product_category_id' => $this->oldParent->id, 'legacy_id' => 703]);
        $adminGroup = ProductCategory::factory()->create(['name' => 'Nhóm tạo trong admin']);

        $this->artisan('catalog:restructure')->expectsOutputToContain('Nhóm tạo trong admin')->assertSuccessful();

        $root = ProductCategory::where('slug', 'dung-cu-xiet-bu-long')->sole();
        $hydraulic = ProductCategory::where('slug', 'dau-xiet-thuy-luc')->sole();
        $this->assertSame(['85'], $root->legacy_ids);
        $this->assertSame(['114', '117'], $hydraulic->legacy_ids);
        $this->assertSame($root->id, $hydraulic->parent_id);
        $this->assertTrue($root->is_featured);
        $this->assertSame('categories/ith.jpg', $root->image, 'The old category image carries over');

        $this->assertSame($hydraulic->id, $product->fresh()->product_category_id);
        $this->assertSame('sung-xiet-bu-long', $misfiled->fresh()->category->slug, 'Listed products move to their group');

        $this->assertModelMissing($this->oldParent);
        $this->assertModelMissing($this->oldChild);
        $this->assertModelExists($adminGroup);
        $this->assertSame(4, ProductCategory::count());
    }

    public function test_rerunning_keeps_editor_changes(): void
    {
        $this->artisan('catalog:restructure')->assertSuccessful();
        ProductCategory::where('slug', 'dau-xiet-thuy-luc')->update(['name' => 'Tên đã sửa', 'icon' => 'gun']);

        $this->artisan('catalog:restructure')->assertSuccessful();

        $hydraulic = ProductCategory::where('slug', 'dau-xiet-thuy-luc')->sole();
        $this->assertSame('Tên đã sửa', $hydraulic->name);
        $this->assertSame('gun', $hydraulic->icon);
        $this->assertSame(3, ProductCategory::count());
    }

    public function test_old_category_with_a_group_slug_is_adopted_and_standardized(): void
    {
        $this->oldChild->update(['slug' => 'dau-xiet-thuy-luc', 'name' => 'NHÓM DỤNG CỤ LOẠI XOAY']);

        $this->artisan('catalog:restructure')->assertSuccessful();

        $hydraulic = $this->oldChild->fresh();
        $this->assertSame('Đầu xiết thủy lực & bơm', $hydraulic->name);
        $this->assertSame(['114', '117'], $hydraulic->legacy_ids);
        $this->assertSame(3, ProductCategory::count());
    }

    public function test_products_of_a_category_holding_several_old_ids_follow_its_first_id(): void
    {
        $earlierMerge = ProductCategory::factory()->create(['legacy_ids' => ['117', '85']]);
        $product = Product::factory()->create(['product_category_id' => $earlierMerge->id]);

        $this->artisan('catalog:restructure')->assertSuccessful();

        $this->assertSame('dau-xiet-thuy-luc', $product->fresh()->category->slug);
        $this->assertModelMissing($earlierMerge);
    }

    public function test_dry_run_saves_nothing(): void
    {
        $this->artisan('catalog:restructure', ['--dry-run' => true])->expectsOutputToContain('Dry run')->assertSuccessful();

        $this->assertSame(3, ProductCategory::count());
        $this->assertModelExists($this->oldParent);
    }
}
