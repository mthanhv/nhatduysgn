<?php

namespace Tests\Feature;

use App\Models\ProductCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductionHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_production_sends_http_and_www_to_the_one_canonical_address(): void
    {
        $this->app['env'] = 'production';
        config(['app.url' => 'https://nhatduysgn.com']);

        $this->get('http://nhatduysgn.com/gioi-thieu?x=1')->assertStatus(301)->assertRedirect('https://nhatduysgn.com/gioi-thieu?x=1');
        $this->get('https://www.nhatduysgn.com/san-pham')->assertStatus(301)->assertRedirect('https://nhatduysgn.com/san-pham');
        $this->get('https://nhatduysgn.com/gioi-thieu')->assertOk();
    }

    public function test_old_product_site_domains_only_redirect_to_the_canonical_site(): void
    {
        $this->app['env'] = 'production';
        config(['app.url' => 'https://nhatduysgn.com']);
        $group = ProductCategory::factory()->create(['slug' => 'thiet-bi-nong-ong', 'legacy_ids' => ['tbn:68']]);

        $this->get('http://thietbinongong.com/products/category:68/dau-nong')->assertStatus(301)->assertRedirect('https://nhatduysgn.com'.route('products.category', $group, false));
        $this->get('https://www.thietbinongong.com/')->assertStatus(301)->assertRedirect('https://nhatduysgn.com/');
        $this->get('http://thietbinongong.com/lien-he')->assertStatus(301)->assertRedirect('https://nhatduysgn.com/');
    }

    public function test_no_redirects_outside_production(): void
    {
        $this->get('http://www.nhatduysgn.com/gioi-thieu')->assertOk();
    }

    public function test_error_pages_render_without_the_database(): void
    {
        foreach ([403, 419, 429, 500, 503] as $code) {
            $html = view("errors.{$code}")->render();

            $this->assertStringContainsString((string) $code, $html);
            $this->assertStringContainsString(config('site.phone'), $html);
        }
    }
}
