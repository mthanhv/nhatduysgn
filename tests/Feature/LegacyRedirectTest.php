<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Post;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Redirect;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class LegacyRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_old_product_url_redirects_permanently_to_new_product_page(): void
    {
        $product = Product::factory()->create(['legacy_id' => 832, 'name' => 'Mỡ bôi trơn ISOFLEX TOPAS NB 52']);

        $this->get('/p832-mo-boi-tron-o-lan-va-o-truot---isoflex-topas-nb-52.html')
            ->assertStatus(301)
            ->assertRedirect(route('products.show', $product));

        $this->get('/products/view/832')->assertRedirect(route('products.show', $product));
    }

    public function test_missing_or_hidden_old_product_falls_back_to_catalogue(): void
    {
        Product::factory()->create(['legacy_id' => 5, 'is_published' => false]);

        $this->get('/p5-an.html')->assertStatus(301)->assertRedirect(route('products.index'));
        $this->get('/p999999-khong-co.html')->assertRedirect(route('products.index'));
    }

    public function test_old_category_urls_with_named_params_redirect_to_category(): void
    {
        $category = ProductCategory::factory()->create(['legacy_id' => 85]);

        $this->get('/products/category:85/title:dung-cu-thao-lap')->assertStatus(301)->assertRedirect(route('products.category', $category));
        $this->get('/products/category%3A85/title%3Adung-cu')->assertRedirect(route('products.category', $category));
        $this->get('/products/category:85/12/34/page:2')->assertRedirect(route('products.category', $category));
    }

    public function test_old_brand_and_supplier_urls_redirect_to_merged_brand(): void
    {
        $brand = Brand::factory()->create(['name' => 'SCHAEFFLER', 'legacy_keys' => ['brand:30', 'supplier:26', 'supplier:33']]);
        Brand::factory()->create(['legacy_keys' => ['brand:3']]);

        $this->get('/products/brand:30/schaeffler')->assertRedirect(route('products.brand', $brand));
        $this->get('/products/supplier:33/title:fag')->assertRedirect(route('products.brand', $brand));
    }

    public function test_old_search_and_tag_urls_become_new_searches(): void
    {
        Redirect::factory()->create(['from_path' => '/products/tag:10', 'to_url' => '/san-pham?q=B%C3%B4i%20tr%C6%A1n']);

        $this->get('/products/key:'.rtrim(base64_encode('LPS'), '='))->assertRedirect(route('products.index', ['q' => 'LPS']));
        $this->get('/products/tag:10/title:boi-tron')->assertRedirect('/san-pham?q=B%C3%B4i%20tr%C6%A1n');
        $this->get('/products/page:3')->assertRedirect(route('products.index'));
    }

    public function test_old_articles_map_to_posts_or_fixed_pages(): void
    {
        $post = Post::factory()->create(['legacy_id' => 63]);

        $this->get('/a63-tiem-nang-phat-trien-dien-gio.html')->assertRedirect(route('posts.show', $post));
        $this->get('/a1-loi-gioi-thieu.html')->assertRedirect(route('about'));
        $this->get('/a64-dich-vu.html')->assertRedirect(route('services'));
        $this->get('/a65-chung-chi.html')->assertRedirect(route('about').'#chung-nhan');
        $this->get('/a777-da-xoa.html')->assertRedirect(route('posts.index'));
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function fixedPages(): array
    {
        return [
            'about' => ['/gioi-thieu.html', 'about'],
            'services' => ['/dich-vu.html', 'services'],
            'contact' => ['/lien-he.html', 'contact'],
            'events' => ['/su-kien', 'posts.index'],
            'old contact controller' => ['/contacts/contactus', 'contact'],
            'old projects' => ['/articles/projects', 'projects.index'],
        ];
    }

    #[DataProvider('fixedPages')]
    public function test_old_fixed_pages_redirect(string $oldPath, string $route): void
    {
        $this->get($oldPath)->assertStatus(301)->assertRedirect(route($route));
    }

    public function test_admin_managed_redirect_takes_priority_and_counts_hits(): void
    {
        Product::factory()->create(['legacy_id' => 12]);
        $redirect = Redirect::factory()->create(['from_path' => '/p12-dac-biet.html', 'to_url' => '/dich-vu']);

        $this->get('/p12-dac-biet.html')->assertRedirect('/dich-vu');
        $this->get('/P12-DAC-BIET.html')->assertRedirect('/dich-vu');

        $this->assertSame(2, $redirect->fresh()->hits);
    }

    public function test_inactive_managed_redirect_is_ignored(): void
    {
        Redirect::factory()->create(['from_path' => '/khuyen-mai.html', 'to_url' => '/san-pham', 'is_active' => false]);

        $this->get('/khuyen-mai.html')->assertNotFound();
    }

    public function test_old_upload_paths_redirect_to_copied_files_only_for_safe_types(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('legacy/files/products/attachments/catalogue.pdf', '%PDF-1.4');
        Storage::disk('public')->put('legacy/media/shell.php', '<?php echo 1;');

        $this->get('/files/products/attachments/catalogue.pdf')
            ->assertStatus(301)
            ->assertRedirect(Storage::disk('public')->url('legacy/files/products/attachments/catalogue.pdf'));

        $this->get('/media/shell.php')->assertNotFound();
        $this->get('/files/khong-co.pdf')->assertNotFound();
    }
}
