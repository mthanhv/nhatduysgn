<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Post;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Project;
use App\Models\Service;
use App\Models\Setting;
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

    public function test_category_listing_follows_sub_group_order_then_product_order(): void
    {
        $parent = ProductCategory::factory()->create(['sort_order' => 1]);
        $later = ProductCategory::factory()->create(['parent_id' => $parent->id, 'sort_order' => 3]);
        $earlier = ProductCategory::factory()->create(['parent_id' => $parent->id, 'sort_order' => 2]);
        Product::factory()->create(['product_category_id' => $later->id, 'name' => 'Bơm Z', 'sort_order' => 1]);
        Product::factory()->create(['product_category_id' => $earlier->id, 'name' => 'Bơm Y', 'sort_order' => 2]);
        Product::factory()->create(['product_category_id' => $earlier->id, 'name' => 'Bơm X', 'sort_order' => 1]);

        $this->get(route('products.category', $parent))->assertOk()->assertSeeInOrder(['Bơm X', 'Bơm Y', 'Bơm Z']);
    }

    public function test_products_page_lists_sub_groups_as_links_under_their_group(): void
    {
        $parent = ProductCategory::factory()->create(['name' => 'Dụng cụ thủy lực ITH']);
        $child = ProductCategory::factory()->create(['parent_id' => $parent->id, 'name' => 'Bơm thủy lực']);
        Product::factory()->count(2)->create(['product_category_id' => $child->id]);

        $this->get(route('products.index'))
            ->assertOk()
            ->assertSeeInOrder(['Dụng cụ thủy lực ITH', 'Bơm thủy lực'])
            ->assertSee('href="'.route('products.category', $child).'"', false)
            ->assertSee('<span class="tree__count">2</span>', false);
    }

    public function test_header_brand_menu_links_every_published_brand_to_its_products(): void
    {
        $brand = Brand::factory()->create(['name' => 'Loctite']);
        Brand::factory()->create(['name' => 'Hãng ẩn', 'is_published' => false]);
        Product::factory()->count(3)->create(['brand_id' => $brand->id]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('<summary class="">Thương hiệu', false)
            ->assertSee('href="'.route('products.brand', $brand).'"', false)
            ->assertSee('<span class="tree__count">3</span>', false)
            ->assertDontSee('Hãng ẩn');

        $this->get(route('products.brand', $brand))
            ->assertOk()
            ->assertSee('<summary class="is-current">Thương hiệu', false)
            ->assertDontSee('aria-current="page">Sản phẩm</a>', false);
    }

    public function test_product_description_shows_embedded_youtube_videos(): void
    {
        $product = Product::factory()->create([
            'description' => '<p>Hướng dẫn sử dụng:</p><div data-type="customBlock" data-config="{&quot;url&quot;:&quot;https://www.youtube.com/watch?v=dQw4w9WgXcQ&quot;}" data-id="youtube"></div>',
        ]);

        $this->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('<figure class="video-embed"><iframe src="https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ"', false)
            ->assertDontSee('data-type="customBlock"', false);
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

    public function test_home_page_leaves_stats_industries_and_news_to_their_own_pages(): void
    {
        Post::factory()->create(['title' => 'Bài viết mới nhất về bảo trì']);

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('Số liệu nổi bật')
            ->assertDontSee('Ngành công nghiệp phục vụ')
            ->assertDontSee('Bài viết mới nhất về bảo trì');

        $this->get(route('about'))->assertOk()->assertSee('Dầu khí')->assertSee('Thiết bị chính hãng, đội ngũ kỹ thuật tận nơi');
    }

    public function test_about_page_has_no_partner_block(): void
    {
        $this->get(route('about'))
            ->assertOk()
            ->assertDontSee('class="spotlight"', false)
            ->assertDontSee('JBE — thiết bị ngành ống thép')
            ->assertDontSee('Đại diện chính thức tại Việt Nam');
    }

    public function test_contact_page_shows_both_offices_and_maps_the_representative_office(): void
    {
        $this->get(route('contact'))
            ->assertOk()
            ->assertSee(config('site.address'))
            ->assertSee(config('site.office_address'))
            ->assertSee('https://maps.google.com/maps?q='.urlencode(config('site.office_address').', Việt Nam'), false);
    }

    public function test_map_falls_back_to_head_office_when_representative_office_is_cleared(): void
    {
        Setting::putMany(['office_address' => null]);

        $this->get(route('contact'))
            ->assertOk()
            ->assertDontSee('VPĐD')
            ->assertSee('https://maps.google.com/maps?q='.urlencode(config('site.address').', Việt Nam'), false);
    }

    public function test_header_products_menu_lists_groups_and_sub_groups(): void
    {
        $group = ProductCategory::factory()->create(['name' => 'Keo công nghiệp']);
        $subGroup = ProductCategory::factory()->create(['parent_id' => $group->id, 'name' => 'Keo khóa ren']);
        Product::factory()->count(2)->create(['product_category_id' => $subGroup->id]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('class="nav-menu nav-menu--products"', false)
            ->assertSeeInOrder(['Keo công nghiệp', '<span class="tree__count">2</span>', 'Keo khóa ren'], false)
            ->assertSee('href="'.route('products.category', $subGroup).'"', false);

        $this->get(route('products.category', $subGroup))
            ->assertOk()
            ->assertSee('<summary class="is-current">Sản phẩm', false)
            ->assertSee('aria-current="page" >Keo khóa ren</a>', false);
    }

    public function test_project_images_open_together_in_the_pop_up(): void
    {
        $project = Project::factory()->create(['cover' => 'projects/cover.jpg', 'gallery' => ['projects/2.jpg', 'projects/3.jpg']]);

        $this->get(route('projects.show', $project))
            ->assertOk()
            ->assertSee('Hình ảnh dự án')
            ->assertSee('data-lightbox-index="2"', false)
            ->assertSee('data-lightbox-dialog', false);
    }

    public function test_service_images_open_together_in_the_pop_up(): void
    {
        Service::factory()->create([
            'title' => 'Gia công ống tại hiện trường',
            'image' => 'services/main.jpg',
            'gallery' => ['services/2.jpg', 'services/main.jpg', 'services/3.jpg'],
        ]);

        $this->get(route('services'))
            ->assertOk()
            ->assertSee('Xem 3 ảnh')
            ->assertSee('data-lightbox-index="2"', false)
            ->assertDontSee('data-lightbox-index="3"', false)
            ->assertSee('data-lightbox-dialog', false);
    }

    public function test_services_page_shows_services_and_faq_without_equipment_lists(): void
    {
        Product::factory()->create(['brand_id' => Brand::factory()->create(['name' => 'ITH'])->id, 'name' => 'Đầu xiết thủy lực dòng CX', 'image' => 'products/cx.jpg']);
        Service::factory()->create(['title' => 'Tháo lắp bu lông bằng thủy lực', 'equipment' => ['ITH']]);

        $this->get(route('services'))
            ->assertOk()
            ->assertSee('Tháo lắp bu lông bằng thủy lực')
            ->assertDontSee('Thiết bị sử dụng')
            ->assertDontSee('Thiết bị liên quan')
            ->assertDontSee('Đầu xiết thủy lực dòng CX')
            ->assertSee('Câu hỏi thường gặp')
            ->assertSee('Quy trình làm việc');
    }
}
