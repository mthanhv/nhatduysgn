<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentBlockMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_saved_services_fields_are_rebuilt_as_content_blocks_and_then_removed(): void
    {
        Setting::putMany([
            'services_intro' => 'Giới thiệu dịch vụ đã lưu.',
            'service_benefits' => [['title' => 'Tại nhà máy', 'text' => 'Toàn quốc.']],
            'service_steps' => [['title' => 'Khảo sát', 'text' => 'Kỹ sư đến hiện trường.']],
            'service_faqs' => [['question' => 'Có bàn giao hồ sơ không?', 'answer' => 'Có, sau nghiệm thu.']],
            'service_faqs_en' => [['question' => 'Handover documents?', 'answer' => 'Yes, after acceptance.']],
        ]);

        (require database_path('migrations/2026_10_28_075637_merge_services_settings_into_content_blocks.php'))->up();
        Service::factory()->create();

        $this->assertSame('<h3>Có bàn giao hồ sơ không?</h3><p>Có, sau nghiệm thu.</p>', Setting::get('service_faqs_content'));
        $this->assertFalse(Setting::query()->whereIn('key', ['services_intro', 'service_benefits', 'service_steps', 'service_faqs', 'service_faqs_en'])->exists());

        $this->get(route('services'))->assertOk()
            ->assertSee('Đúng thiết bị, đúng quy trình, ngay tại nhà máy của bạn')->assertSee('Giới thiệu dịch vụ đã lưu.')
            ->assertSee('Tại nhà máy')->assertSee('Kỹ sư đến hiện trường.')
            ->assertSee('Có bàn giao hồ sơ không?')->assertSee('"@type":"FAQPage"', false);
        $this->get(route('en.services'))->assertOk()->assertSee('Handover documents?');
        $this->get(route('home'))->assertOk()->assertSee('Giới thiệu dịch vụ đã lưu.');
    }

    public function test_saved_home_and_about_fields_are_rebuilt_as_content_blocks_and_then_removed(): void
    {
        Setting::putMany([
            'home_hero_eyebrow' => 'Từ 2011',
            'home_hero_title' => 'Tiêu đề đã lưu',
            'home_hero_text' => 'Giới thiệu đã lưu.',
            'home_hero_caption_brand' => 'ITH',
            'home_hero_caption_title' => 'Đầu xiết CX',
            'home_about_title' => 'Về chúng tôi đã lưu',
            'home_about_text' => 'Nội dung.',
            'home_about_points' => [['title' => 'Hàng chính hãng', 'text' => 'từ G7']],
            'home_stats' => [['value' => '2011', 'label' => 'năm thành lập']],
            'industries' => [['name' => 'Dầu khí', 'icon' => 'oil']],
            'about_hero_title' => 'Giới thiệu đã lưu',
            'about_hero_text' => 'Từ năm 2011.',
            'about_intro_title' => 'Câu chuyện',
            'about_intro_body' => '<p>Thân bài.</p>',
            'about_pillars' => [['letter' => 'M', 'en' => 'Maintenance', 'title' => 'Bảo trì', 'text' => 'Dầu mỡ.']],
            'about_values' => [['title' => 'Chính hãng', 'text' => 'Nguồn gốc rõ ràng.']],
        ]);

        (require database_path('migrations/2026_10_28_074509_merge_home_about_settings_into_content_blocks.php'))->up();
        (require database_path('migrations/2026_10_28_075353_delete_unused_home_about_settings.php'))->up();

        $this->assertSame('<p>Từ 2011</p><h2>Tiêu đề đã lưu</h2><p>Giới thiệu đã lưu.</p>', Setting::get('home_hero_content'));
        $this->assertSame("ITH\nĐầu xiết CX", Setting::get('home_hero_caption'));
        $this->assertSame('<h3>Bảo trì (Maintenance)</h3><p>Dầu mỡ.</p>', Setting::get('about_pillars_content'));
        $this->assertFalse(Setting::query()->whereIn('key', ['home_hero_title', 'home_stats', 'about_pillars', 'about_spotlight_title'])->exists());

        $this->get(route('home'))->assertOk()
            ->assertSee('Từ 2011')->assertSee('Tiêu đề đã lưu')->assertSee('Đầu xiết CX')
            ->assertSee('Về chúng tôi đã lưu')->assertSeeInOrder(['<strong>Hàng chính hãng</strong>', '—', 'từ G7'], false);
        $this->get(route('about'))->assertOk()
            ->assertSee('Giới thiệu đã lưu')->assertSee('năm thành lập')->assertSee('Dầu khí')
            ->assertSee('<strong>M</strong><span>Maintenance</span>', false)->assertSee('Nguồn gốc rõ ràng.');
    }
}
