<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Product;
use App\Services\ProductTidier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTidierTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_caps_names_get_normal_case_keeping_brands_codes_and_acronyms(): void
    {
        Brand::factory()->create(['name' => 'LOCTITE']);

        $this->assertSame('Keo khóa ren LOCTITE 243', ProductTidier::name('KEO KHÓA REN LOCTITE 243'));
        $this->assertSame('Súng xiết bu-lông tua-bin (rotor turning kit for GE turbine)', ProductTidier::name('SÚNG XIẾT BU-LÔNG TUA-BIN (ROTOR TURNING KIT FOR GE TURBINE)'));
        $this->assertSame('Shim cân chỉnh thiết bị, dầy 0.10mm, dài 1.25m, vật liệu inox 304', ProductTidier::name('SHIM CÂN CHỈNH THIẾT BỊ, DẦY 0.10MM, DÀI 1.25M, VẬT LIỆU INOX 304'));
        $this->assertSame('Đầu Xiết Thủy Lực DRS', ProductTidier::name('Đầu Xiết Thủy Lực DRS'), 'Names that are not shouting are left alone');
    }

    public function test_truncated_series_codes_and_brand_only_codes_are_fixed(): void
    {
        $this->assertSame('DRS', ProductTidier::code('DRS-', 'ITH'));
        $this->assertSame('JCB-110XR5', ProductTidier::code(' JCB-110XR5 ', 'JBE'));
        $this->assertNull(ProductTidier::code('ITH', 'ITH'));
        $this->assertNull(ProductTidier::code('-', null));
    }

    public function test_download_hints_are_removed_only_when_there_is_nothing_to_download(): void
    {
        $html = '<p>Đầu xiết thủy lực.</p><p><strong>Tải tài liệu kỹ thuật chi tiết tại File đính kèm.</strong></p><table><tr><td>CX-2</td></tr></table>';

        $this->assertSame('<p>Đầu xiết thủy lực.</p><table><tr><td>CX-2</td></tr></table>', ProductTidier::description($html, false));
        $this->assertSame($html, ProductTidier::description($html, true));
    }

    public function test_catalog_tidy_command_updates_products_but_keeps_their_urls(): void
    {
        $brand = Brand::factory()->create(['name' => 'ITH']);
        $product = Product::factory()->create(['brand_id' => $brand->id, 'name' => 'DỤNG CỤ THỦY LỰC LOẠI XOAY (TORQUE WRENCH)', 'code' => 'DRS-', 'documents' => null,
            'description' => '<p>Tải tài liệu kỹ thuật chi tiết tại File đính kèm.</p><p>Mô-men lớn.</p>']);
        $slug = $product->slug;

        $this->artisan('catalog:tidy', ['--dry-run' => true])->expectsOutputToContain('Dry run')->assertSuccessful();
        $this->assertSame('DRS-', $product->fresh()->code);

        $this->artisan('catalog:tidy')->assertSuccessful();

        $product->refresh();
        $this->assertSame('Dụng cụ thủy lực loại xoay (torque wrench)', $product->name);
        $this->assertSame('DRS', $product->code);
        $this->assertSame('<p>Mô-men lớn.</p>', $product->description);
        $this->assertSame($slug, $product->slug);
    }
}
