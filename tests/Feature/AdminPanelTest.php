<?php

namespace Tests\Feature;

use App\Filament\Pages\ManageSiteSettings;
use App\Filament\Resources\Brands\Pages\ManageBrands;
use App\Filament\Resources\Certificates\Pages\ManageCertificates;
use App\Filament\Resources\Clients\Pages\ManageClients;
use App\Filament\Resources\ContactMessages\Pages\ViewContactMessage;
use App\Filament\Resources\ProductCategories\Pages\ListProductCategories;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Filament\Resources\Projects\Pages\ListProjects;
use App\Filament\Resources\Services\Pages\ListServices;
use App\Filament\Widgets\ContentChecklist;
use App\Filament\Widgets\LatestContactMessages;
use App\Filament\Widgets\SiteStatsOverview;
use App\Filament\Widgets\WelcomeBanner;
use App\Models\ContactMessage;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Service;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_sent_to_login(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_disabled_accounts_cannot_open_the_admin(): void
    {
        $this->actingAs(User::factory()->create(['is_active' => false]))
            ->get('/admin')
            ->assertForbidden();
    }

    public function test_every_admin_screen_loads_for_an_active_admin(): void
    {
        $this->actingAs(User::factory()->create());
        $product = Product::factory()->create();
        $message = ContactMessage::factory()->create();

        $urls = [
            '/admin',
            '/admin/products', '/admin/products/create', "/admin/products/{$product->slug}/edit",
            '/admin/product-categories', '/admin/product-categories/create',
            '/admin/brands', '/admin/services', '/admin/services/create', '/admin/projects', '/admin/projects/create',
            '/admin/posts', '/admin/posts/create', '/admin/clients', '/admin/certificates',
            '/admin/contact-messages', "/admin/contact-messages/{$message->id}",
            '/admin/redirects', '/admin/users', '/admin/cai-dat',
        ];

        foreach ($urls as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_dragging_a_group_moves_its_sub_groups_with_it(): void
    {
        $this->actingAs(User::factory()->create());
        $first = ProductCategory::factory()->create(['sort_order' => 1]);
        $firstChild = ProductCategory::factory()->create(['parent_id' => $first->id, 'sort_order' => 2]);
        $second = ProductCategory::factory()->create(['sort_order' => 3]);
        $secondChild = ProductCategory::factory()->create(['parent_id' => $second->id, 'sort_order' => 4]);

        // The second group is dragged to the top, leaving its sub-group behind in the flat list.
        Livewire::test(ListProductCategories::class)
            ->call('reorderTable', [$second->id, $first->id, $firstChild->id, $secondChild->id]);

        $this->assertSame(
            [$second->id, $secondChild->id, $first->id, $firstChild->id],
            ProductCategory::query()->orderBy('sort_order')->pluck('id')->all(),
        );
    }

    public function test_typing_a_group_order_in_the_list_moves_it_with_its_sub_groups(): void
    {
        $this->actingAs(User::factory()->create());
        $first = ProductCategory::factory()->create(['sort_order' => 1]);
        $firstChild = ProductCategory::factory()->create(['parent_id' => $first->id, 'sort_order' => 2]);
        $second = ProductCategory::factory()->create(['sort_order' => 3]);

        Livewire::test(ListProductCategories::class)
            ->assertTableColumnExists('sort_order')
            ->call('updateTableColumnState', 'sort_order', (string) $second->getKey(), 0);

        $this->assertSame(
            [$second->id, $first->id, $firstChild->id],
            ProductCategory::query()->orderBy('sort_order')->pluck('id')->all(),
        );
        $this->assertSame(1, $second->fresh()->sort_order);
    }

    public function test_every_ordered_list_shows_an_editable_order_column(): void
    {
        $this->actingAs(User::factory()->create());
        $product = Product::factory()->create(['sort_order' => 5]);

        foreach ([ListProducts::class, ListProductCategories::class, ManageBrands::class, ListServices::class, ListProjects::class, ManageClients::class, ManageCertificates::class] as $page) {
            Livewire::test($page)->assertTableColumnExists('sort_order');
        }

        Livewire::test(ListProducts::class)->call('updateTableColumnState', 'sort_order', (string) $product->getKey(), 2);
        $this->assertSame(2, $product->fresh()->sort_order);
    }

    public function test_product_edit_page_links_to_the_published_product(): void
    {
        $this->actingAs(User::factory()->create());
        $published = Product::factory()->create();
        $hidden = Product::factory()->create(['is_published' => false]);

        $this->get("/admin/products/{$published->slug}/edit")->assertOk()
            ->assertSee('Xem trên website')
            ->assertSee('href="'.route('products.show', $published).'"', false);
        $this->get("/admin/products/{$hidden->slug}/edit")->assertOk()
            ->assertDontSee('href="'.route('products.show', $hidden).'"', false);
    }

    public function test_products_can_be_reordered_only_within_one_group(): void
    {
        $this->actingAs(User::factory()->create());
        $category = ProductCategory::factory()->create();

        $list = Livewire::test(ListProducts::class);
        $this->assertFalse($list->instance()->getTable()->isReorderable());

        $list->filterTable('product_category_id', $category->id);
        $this->assertTrue($list->instance()->getTable()->isReorderable());
    }

    public function test_opening_a_message_marks_it_as_read(): void
    {
        $this->actingAs(User::factory()->create());
        $message = ContactMessage::factory()->create();

        Livewire::test(ViewContactMessage::class, ['record' => $message->getRouteKey()]);

        $this->assertNotNull($message->fresh()->read_at);
    }

    public function test_every_rich_editor_has_an_html_source_button(): void
    {
        $this->actingAs(User::factory()->create());
        $service = Service::factory()->create();

        $this->get("/admin/services/{$service->slug}/edit")->assertOk()->assertSee('Mã HTML');
    }

    public function test_saving_settings_without_changes_keeps_every_page_block(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(ManageSiteSettings::class)->call('save')->assertHasNoErrors();

        foreach (['home_hero_content', 'about_intro_content', 'services_hero_content', 'service_faqs_content', 'service_faqs_content_en'] as $key) {
            $this->assertStringContainsString('<h', (string) Setting::get($key), $key);
        }
        $this->get(route('services'))->assertSee('Đúng thiết bị, đúng quy trình, ngay tại nhà máy của bạn');
    }

    public function test_settings_page_saves_values_used_by_the_public_site(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(ManageSiteSettings::class)
            ->set('data.phone', '028 1111 2222')
            ->set('data.home_hero_content', '<h2>Tiêu đề mới cho trang chủ</h2>')
            ->set('data.home_hero_content_en', '<h2>New home page title</h2>')
            ->call('save')
            ->assertHasNoErrors();

        $this->get(route('home'))
            ->assertSee('028 1111 2222')
            ->assertSee('Tiêu đề mới cho trang chủ');
        $this->get(route('en.home'))->assertSee('New home page title');
    }

    public function test_dashboard_widgets_render(): void
    {
        $this->actingAs(User::factory()->create(['name' => 'Chị Lan']));
        ContactMessage::factory()->create(['subject' => 'Hỏi giá CX-5']);
        Product::factory()->create(['name' => 'KEO KHÓA REN LOCTITE 290', 'image' => null]);

        Livewire::test(WelcomeBanner::class)->assertSee('Chị Lan')->assertSee('1 tin nhắn liên hệ chưa đọc');
        Livewire::test(SiteStatsOverview::class)->assertSee('Tin nhắn chưa đọc');
        Livewire::test(ContentChecklist::class)->assertSee('Sản phẩm chưa có ảnh')->assertSee('Tên sản phẩm VIẾT HOA toàn bộ');
        Livewire::test(LatestContactMessages::class)->assertSee('Hỏi giá CX-5');
    }
}
