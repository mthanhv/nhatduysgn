<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Services\LegacyImporter;
use App\Services\Seo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app['env'] = 'production';

        $group = ProductCategory::factory()->create(['name' => 'Dụng cụ xiết bu lông', 'summary' => null, 'description' => null]);
        $brand = Brand::factory()->create(['name' => 'ITH', 'country' => 'Đức']);
        $this->product = Product::factory()->create([
            'product_category_id' => $group->id,
            'brand_id' => $brand->id,
            'name' => 'Đầu xiết thủy lực dòng CX',
            'code' => 'CX-5',
            'origin' => 'Made in Germany',
            'summary' => null,
            'description' => '<p>Đầu xiết&nbsp;thủy lực <strong>mô-men lớn</strong> cho bu lông mặt bích.</p>',
            'image' => 'legacy/files/products/ảnh cx 5.jpg',
        ]);
    }

    public function test_product_page_has_generated_description_structured_data_and_absolute_share_image(): void
    {
        $response = $this->get(route('products.show', $this->product))->assertOk();

        $response->assertSee('<meta name="description" content="Đầu xiết thủy lực dòng CX (mã CX-5, hãng ITH, Made in Germany). Đầu xiết thủy lực mô-men lớn', false)
            ->assertSee('<meta name="robots" content="index, follow', false)
            ->assertSee('<link rel="canonical" href="'.route('products.show', $this->product).'">', false)
            ->assertSee('<meta property="og:type" content="product">', false)
            ->assertSee('content="'.url('/uploads/legacy/files/products/%E1%BA%A3nh%20cx%205.jpg').'"', false)
            ->assertSee('"@type":"Product"', false)
            ->assertSee('"sku":"CX-5"', false)
            ->assertSee('"@type":"BreadcrumbList"', false)
            ->assertSee('"@type":"Organization"', false);
    }

    public function test_descriptions_stay_within_search_snippet_length(): void
    {
        $this->product->update(['description' => '<p>'.str_repeat('Mô tả rất dài của sản phẩm. ', 40).'</p>']);

        $this->assertLessThanOrEqual(Seo::DESCRIPTION_LENGTH, mb_strlen(Seo::productDescription($this->product->fresh())));
        $this->product->update(['meta_description' => 'Mô tả do biên tập viên viết.']);
        $this->assertSame('Mô tả do biên tập viên viết.', Seo::productDescription($this->product->fresh()));
        $this->assertStringStartsWith('Đầu xiết thủy lực dòng CX', Seo::productDescription($this->product->fresh(), generated: true));
    }

    public function test_old_keyword_list_meta_is_replaced_by_a_written_description_and_kept_as_keywords(): void
    {
        $this->product->update(['meta_description' => 'ITH, ith, Đầu Nhân Lực, SÚNG ĐIỆN DÙNG XIẾT BU LÔNG']);
        $this->assertStringStartsWith('Đầu xiết thủy lực dòng CX', Seo::productDescription($this->product->fresh()));

        $this->assertSame(
            ['meta_description' => null, 'keywords' => 'CX-5, ITH, Đầu Nhân Lực, SÚNG ĐIỆN DÙNG XIẾT BU LÔNG'],
            LegacyImporter::splitLegacyMeta('ITH, ith, Đầu Nhân Lực,\\n SÚNG ĐIỆN DÙNG XIẾT BU LÔNG', 'CX-5'),
        );
        $this->assertSame(['meta_description' => null, 'keywords' => 'Lapping machine kemet 36'], LegacyImporter::splitLegacyMeta('Lapping machine kemet 36', null), 'Fragments too short for a snippet become keywords');

        $sentence = 'Máy rà van di động Larslap dùng để phục hồi bề mặt làm kín của van an toàn ngay tại hiện trường.';
        $this->assertSame(['meta_description' => $sentence, 'keywords' => null], LegacyImporter::splitLegacyMeta($sentence, null));
    }

    public function test_category_listing_describes_the_group_and_lists_its_products(): void
    {
        $this->get(route('products.category', $this->product->category))
            ->assertOk()
            ->assertSee('Dụng cụ xiết bu lông: 1 sản phẩm chính hãng từ ITH.')

            ->assertSee('"@type":"CollectionPage"', false)
            ->assertSee('"url":"'.route('products.show', $this->product).'"', false);
    }

    public function test_paginated_pages_are_canonical_to_themselves_and_search_results_are_not_indexed(): void
    {
        Product::factory()->count(30)->create(['product_category_id' => $this->product->product_category_id]);

        $this->get(route('products.category', $this->product->category).'?page=2&utm_source=zalo')
            ->assertSee('<link rel="canonical" href="'.route('products.category', $this->product->category).'?page=2">', false);

        $this->get(route('products.index', ['q' => 'CX']))
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex, follow">', false);
    }

    public function test_robots_txt_welcomes_search_and_ai_crawlers_and_points_to_the_sitemap(): void
    {
        $this->get('/robots.txt')
            ->assertOk()
            ->assertSee('User-agent: GPTBot')
            ->assertSee('User-agent: ClaudeBot')
            ->assertSee('User-agent: PerplexityBot')
            ->assertSee('Disallow: /admin')
            ->assertSee('Sitemap: '.route('sitemap'));

        $this->app['env'] = 'local';
        $this->get('/robots.txt')->assertSee("User-agent: *\nDisallow: /", false);
    }

    public function test_llms_txt_summarises_the_company_and_llms_full_lists_every_product(): void
    {
        $this->get('/llms.txt')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/markdown; charset=UTF-8')
            ->assertSee('# '.config('site.company_name'))
            ->assertSee('[Dụng cụ xiết bu lông]('.route('products.category', $this->product->category).')')
            ->assertSee('[ITH]('.route('products.brand', $this->product->brand).') (Đức)')
            ->assertSee(url('/llms-full.txt'));

        $this->get('/llms-full.txt')
            ->assertOk()
            ->assertSee('- [Đầu xiết thủy lực dòng CX]('.route('products.show', $this->product).') — Mã: CX-5 · Hãng: ITH · Made in Germany');
    }

    public function test_sitemap_lists_brands_and_product_images(): void
    {
        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertSee(route('products.brand', $this->product->brand))
            ->assertSee('<image:loc>'.url('/uploads/legacy/files/products/%E1%BA%A3nh%20cx%205.jpg').'</image:loc>', false);
    }

    public function test_titles_with_ampersands_are_escaped_once(): void
    {
        $group = ProductCategory::factory()->create(['name' => 'Vòng bi & bảo trì vòng bi']);

        $this->get(route('products.category', $group))
            ->assertSee('<title>Vòng bi &amp; bảo trì vòng bi | '.config('site.brand_name').'</title>', false)
            ->assertDontSee('&amp;amp;', false);
    }

    public function test_missing_pages_are_not_indexed(): void
    {
        $this->get('/khong-co-trang-nay')->assertNotFound()->assertSee('<meta name="robots" content="noindex, follow">', false);
    }
}
