<?php

namespace Tests\Feature;

use App\Filament\Pages\ManageSiteSettings;
use App\Filament\Resources\ContactMessages\Pages\ViewContactMessage;
use App\Filament\Widgets\ContentChecklist;
use App\Filament\Widgets\LatestContactMessages;
use App\Filament\Widgets\SiteStatsOverview;
use App\Filament\Widgets\WelcomeBanner;
use App\Models\ContactMessage;
use App\Models\Product;
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

    public function test_opening_a_message_marks_it_as_read(): void
    {
        $this->actingAs(User::factory()->create());
        $message = ContactMessage::factory()->create();

        Livewire::test(ViewContactMessage::class, ['record' => $message->getRouteKey()]);

        $this->assertNotNull($message->fresh()->read_at);
    }

    public function test_settings_page_saves_values_used_by_the_public_site(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(ManageSiteSettings::class)
            ->set('data.phone', '028 1111 2222')
            ->set('data.home_hero_title', 'Tiêu đề mới cho trang chủ')
            ->call('save')
            ->assertHasNoErrors();

        $this->get(route('home'))
            ->assertSee('028 1111 2222')
            ->assertSee('Tiêu đề mới cho trang chủ');
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
