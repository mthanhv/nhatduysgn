<?php

namespace Tests\Feature;

use App\Filament\Resources\Products\Pages\EditProduct;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LocalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_public_page_has_an_english_version_under_en(): void
    {
        foreach (['/en', '/en/about', '/en/services', '/en/products', '/en/projects', '/en/news', '/en/contact'] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertSee('<html lang="en">', false)
                ->assertSee('>Products', false)
                ->assertDontSee('Bỏ qua đến nội dung chính');
        }

        $this->get('/')->assertOk()->assertSee('<html lang="vi">', false)->assertSee('Bỏ qua đến nội dung chính');
    }

    public function test_links_and_language_switch_stay_on_the_same_page(): void
    {
        $category = ProductCategory::factory()->create(['slug' => 'keo-cong-nghiep', 'translations' => ['en' => ['name' => 'Industrial adhesives']]]);

        $this->get('/danh-muc/keo-cong-nghiep')
            ->assertOk()
            ->assertSee('<link rel="alternate" hreflang="en" href="'.url('/en/category/keo-cong-nghiep').'">', false)
            ->assertSee('href="'.url('/en/category/keo-cong-nghiep').'" hreflang="en"', false);

        $this->get('/en/category/keo-cong-nghiep')
            ->assertOk()
            ->assertSee('Industrial adhesives')
            ->assertSee('href="'.url('/en/products').'"', false)
            ->assertSee('href="'.url('/danh-muc/keo-cong-nghiep').'" hreflang="vi"', false);

        $this->assertSame('Industrial adhesives', $category->fresh()->translation('name', 'en'));
    }

    public function test_content_shows_its_english_version_and_falls_back_to_vietnamese(): void
    {
        $translated = Product::factory()->create([
            'name' => 'Keo khóa ren Loctite 243',
            'summary' => 'Keo khóa ren độ bền trung bình.',
            'translations' => ['en' => ['name' => 'Loctite 243 threadlocker']],
        ]);
        $untranslated = Product::factory()->create(['name' => 'Bơm mỡ tự động']);

        $this->get(route('en.products.show', $translated))
            ->assertOk()
            ->assertSee('Loctite 243 threadlocker')
            ->assertSee('Keo khóa ren độ bền trung bình.')
            ->assertSee('hreflang="en"', false);

        $this->get(route('products.show', $translated))->assertOk()->assertSee('Keo khóa ren Loctite 243')->assertDontSee('threadlocker');

        // No English name yet: the Vietnamese text is shown and the page is not offered as an English alternate.
        $this->get(route('en.products.show', $untranslated))
            ->assertOk()
            ->assertSee('Bơm mỡ tự động')
            ->assertDontSee('<link rel="alternate" hreflang="en"', false);
    }

    public function test_untranslated_english_record_pages_are_noindex_in_production(): void
    {
        $this->app['env'] = 'production';
        $translated = Product::factory()->create(['translations' => ['en' => ['name' => 'Bearing heater']]]);
        $untranslated = Product::factory()->create();

        $this->get(route('en.products.show', $translated))->assertOk()->assertSee('<meta name="robots" content="index, follow', false);
        $this->get(route('en.products.show', $untranslated))->assertOk()->assertSee('<meta name="robots" content="noindex, follow">', false);
        $this->get(route('products.show', $untranslated))->assertOk()->assertSee('<meta name="robots" content="index, follow', false);
    }

    public function test_english_settings_fall_back_to_vietnamese_when_empty(): void
    {
        Setting::putMany([
            'home_hero_content' => '<h2>Tiêu đề tiếng Việt</h2>',
            'home_hero_content_en' => '<h2>English title</h2>',
            'about_hero_content' => '<h2>Giới thiệu tiếng Việt</h2>',
            'about_hero_content_en' => null,
        ]);

        $this->get('/en')->assertOk()->assertSee('English title');
        $this->get('/')->assertOk()->assertSee('Tiêu đề tiếng Việt')->assertDontSee('English title');
        $this->get('/en/about')->assertOk()->assertSee('Giới thiệu tiếng Việt');
    }

    public function test_contact_form_errors_are_in_english_on_the_english_site(): void
    {
        $this->from('/en/contact')->post('/en/contact', ['topic' => 'product'])
            ->assertRedirect('/en/contact')
            ->assertSessionHasErrors(['name' => 'Please enter your full name.']);
    }

    public function test_sitemap_lists_english_pages_only_for_translated_records(): void
    {
        Product::factory()->create(['slug' => 'da-dich', 'translations' => ['en' => ['name' => 'Translated']]]);
        Product::factory()->create(['slug' => 'chua-dich']);

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertSee(url('/en/about'), false)
            ->assertSee(url('/en/products/da-dich'), false)
            ->assertDontSee(url('/en/products/chua-dich'), false)
            ->assertSee(url('/san-pham/chua-dich'), false);
    }

    public function test_editors_fill_in_english_content_in_the_admin(): void
    {
        $this->actingAs(User::factory()->create());
        $product = Product::factory()->create(['name' => 'Máy vát mép ống']);

        Livewire::test(EditProduct::class, ['record' => $product->getRouteKey()])
            ->fillForm(['translations.en.name' => 'Pipe bevelling machine', 'translations.en.summary' => 'Portable bevelling.'])
            ->call('save')
            ->assertHasNoFormErrors();

        $product->refresh();
        $this->assertSame('Máy vát mép ống', $product->name);
        $this->assertSame('Pipe bevelling machine', $product->translation('name', 'en'));
        $this->assertStringContainsString('pipe bevelling machine', $product->search_text);
    }
}
