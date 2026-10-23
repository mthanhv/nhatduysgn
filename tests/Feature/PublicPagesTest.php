<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Post;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Project;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_static_pages_render(): void
    {
        Service::factory()->create(['title' => 'Tháo lắp bu lông bằng thủy lực']);

        foreach (['home', 'about', 'services', 'products.index', 'projects.index', 'posts.index', 'contact'] as $route) {
            $this->get(route($route))->assertOk();
        }

        $this->get(route('services'))->assertSee('Tháo lắp bu lông bằng thủy lực');
    }

    public function test_product_page_shows_published_product_and_hides_unpublished(): void
    {
        $product = Product::factory()->create(['name' => 'Đầu xiết thủy lực dòng CX', 'code' => 'CX-5']);
        $hidden = Product::factory()->create(['is_published' => false]);

        $this->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('Đầu xiết thủy lực dòng CX')
            ->assertSee('CX-5');

        $this->get(route('products.show', $hidden))->assertNotFound();
        $this->assertSame(1, $product->fresh()->view_count);
    }

    public function test_category_listing_includes_products_of_sub_groups(): void
    {
        $parent = ProductCategory::factory()->create(['name' => 'Dụng cụ thủy lực ITH']);
        $child = ProductCategory::factory()->create(['parent_id' => $parent->id]);
        Product::factory()->create(['product_category_id' => $child->id, 'name' => 'Bơm thủy lực DAX-21']);

        $this->get(route('products.category', $parent))
            ->assertOk()
            ->assertSee('Bơm thủy lực DAX-21');
    }

    public function test_brand_listing_shows_its_products(): void
    {
        $brand = Brand::factory()->create(['name' => 'KLÜBER']);
        Product::factory()->create(['brand_id' => $brand->id, 'name' => 'Centoplex 2 EP']);

        $this->get(route('products.brand', $brand))->assertOk()->assertSee('Centoplex 2 EP');
    }

    public function test_search_ignores_vietnamese_accents_and_code_punctuation(): void
    {
        Product::factory()->create(['name' => 'Keo khóa ren Loctite 243', 'code' => 'LOCTITE 243']);
        Product::factory()->create(['name' => 'Đầu xiết thủy lực', 'code' => 'CX-5']);
        Product::factory()->create(['name' => 'Không liên quan', 'code' => 'ZZ-1']);

        $this->get(route('products.index', ['q' => 'keo khoa ren']))->assertOk()->assertSee('Keo khóa ren Loctite 243')->assertDontSee('Không liên quan');
        $this->get(route('products.index', ['q' => 'cx5']))->assertOk()->assertSee('Đầu xiết thủy lực');
    }

    public function test_project_and_post_pages_render(): void
    {
        $project = Project::factory()->create(['title' => 'Xiết bu lông mặt bích giàn khoan']);
        $post = Post::factory()->create(['title' => 'Tiềm năng phát triển điện gió']);
        $scheduled = Post::factory()->create(['published_at' => now()->addWeek()]);

        $this->get(route('projects.show', $project))->assertOk()->assertSee('Xiết bu lông mặt bích giàn khoan');
        $this->get(route('posts.show', $post))->assertOk()->assertSee('Tiềm năng phát triển điện gió');
        $this->get(route('posts.show', $scheduled))->assertNotFound();
        $this->get(route('posts.index'))->assertDontSee($scheduled->title);
    }

    public function test_sitemap_lists_published_content_only(): void
    {
        $visible = Product::factory()->create();
        $hidden = Product::factory()->create(['is_published' => false]);

        $this->get(route('sitemap'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee(route('products.show', $visible), false)
            ->assertDontSee(route('products.show', $hidden), false);
    }

    public function test_robots_blocks_crawlers_outside_production(): void
    {
        $this->get(route('robots'))->assertOk()->assertSee('Disallow: /');
    }

    public function test_unknown_url_returns_404_page(): void
    {
        $this->get('/trang-khong-ton-tai')->assertNotFound()->assertSee('Không tìm thấy trang');
    }

    public function test_news_page_highlights_newest_post_and_searches(): void
    {
        $older = Post::factory()->create(['title' => 'Tiềm năng phát triển điện gió', 'published_at' => now()->subMonth(), 'view_count' => 50]);
        $newest = Post::factory()->create(['title' => 'Khai trương văn phòng mới', 'published_at' => now()->subDay()]);

        $this->get(route('posts.index'))
            ->assertOk()
            ->assertSeeInOrder(['Mới nhất', 'Khai trương văn phòng mới', 'Bài viết khác', 'Tiềm năng phát triển điện gió'])
            ->assertSee('phút đọc');

        $this->get(route('posts.index', ['q' => 'điện gió']))
            ->assertOk()
            ->assertSee('Tiềm năng phát triển điện gió')
            ->assertDontSee('Khai trương văn phòng mới');

        $this->get(route('posts.show', $older))->assertOk()->assertSee('Bài sau')->assertSee($newest->title)->assertSee('Sao chép liên kết');
    }

    public function test_article_hides_excerpt_that_only_repeats_the_opening(): void
    {
        $auto = Post::factory()->create(['excerpt' => 'Năng lượng gió được kỳ vọng…', 'content' => '<p>Năng lượng gió được kỳ vọng giúp Việt Nam.</p>']);
        $written = Post::factory()->create(['excerpt' => 'Tóm tắt riêng do biên tập viên viết.', 'content' => '<p>Nội dung chi tiết.</p>']);

        $this->get(route('posts.show', $auto))->assertOk()->assertDontSee('class="article-lead"', false);
        $this->get(route('posts.show', $written))->assertOk()->assertSee('Tóm tắt riêng do biên tập viên viết.');
    }

    public function test_contact_page_shows_map_of_the_office_address(): void
    {
        $this->get(route('contact'))
            ->assertOk()
            ->assertSee('https://maps.google.com/maps?q='.urlencode(config('site.address').', Việt Nam'), false);
    }
}
