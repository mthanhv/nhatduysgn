<?php

namespace App\Filament\Pages;

use App\Filament\Support\Bilingual;
use App\Filament\Support\HtmlSource;
use App\Filament\Support\Uploads;
use App\Models\Setting;
use BackedEnum;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/**
 * Company details and the editable copy of the homepage, about and services pages.
 *
 * @property-read Schema $form
 */
class ManageSiteSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|UnitEnum|null $navigationGroup = 'Hệ thống';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Cài đặt website';

    protected static ?string $title = 'Cài đặt website';

    protected static ?string $slug = 'cai-dat';

    private const HELP_HERO = 'Đoạn đầu (tuỳ chọn): dòng nhỏ phía trên. Dòng định dạng Tiêu đề (H2): tiêu đề lớn. Các đoạn sau: giới thiệu.';

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(collect($this->settingKeys())->mapWithKeys(fn (string $key) => [$key => Setting::get($key)])->all());
    }

    public function form(Schema $schema): Schema
    {
        // Text settings show their English version (key + "_en", see Setting::TRANSLATABLE) right next to them.
        $pair = fn (string $key, Closure $field, bool $stacked = false) => Bilingual::pair($key, "{$key}_en", $field, $stacked);
        // One rich-text field per page block, read by App\Services\ContentBlock.
        $block = fn (string $name) => RichEditor::make($name)->label('Nội dung')->toolbarButtons([['bold', 'italic', 'link'], ['h2', 'h3'], ['bulletList'], ['undo', 'redo'], [HtmlSource::NAME]]);
        $titleText = fn (string $title = 'Tiêu đề', string $text = 'Mô tả') => [
            TextInput::make('title')->label($title)->required(),
            Textarea::make('text')->label($text)->rows(2),
        ];

        return $schema
            ->statePath('data')
            ->components([
                Tabs::make()->persistTabInQueryString()->tabs([
                    Tab::make('Công ty & liên hệ')->icon(Heroicon::OutlinedBuildingOffice)->schema([
                        Text::make('Ô có nhãn "English" là nội dung trang tiếng Anh (/en); để trống sẽ hiện bản tiếng Việt. Số điện thoại, email, link và ảnh dùng chung cho cả hai ngôn ngữ.'),
                        $pair('company_name', fn (string $name, bool $en) => TextInput::make($name)->label('Tên công ty')->required(! $en)),
                        Grid::make(2)->schema([
                            TextInput::make('brand_name')->label('Tên ngắn / thương hiệu')->required(),
                            TextInput::make('phone')->label('Điện thoại')->tel()->required(),
                            TextInput::make('fax')->label('Fax'),
                            TextInput::make('email')->label('Email liên hệ')->email()->required(),
                            TextInput::make('contact_notify_email')->label('Email nhận thông báo liên hệ')->email()->required()->helperText('Tin nhắn từ form liên hệ được gửi về địa chỉ này.'),
                            TextInput::make('zalo')->label('Số Zalo')->helperText('Để trống để ẩn nút Zalo.'),
                            TextInput::make('tax_code')->label('Mã số thuế'),
                            TextInput::make('founded_year')->label('Năm thành lập'),
                        ]),
                        $pair('tagline', fn (string $name) => TextInput::make($name)->label('Khẩu hiệu dưới logo')),
                        $pair('working_hours', fn (string $name) => TextInput::make($name)->label('Giờ làm việc')),
                        $pair('address', fn (string $name, bool $en) => TextInput::make($name)->label('Địa chỉ trụ sở')->required(! $en)),
                        $pair('office_address', fn (string $name, bool $en) => TextInput::make($name)
                            ->label('Địa chỉ văn phòng đại diện (VPĐD)')
                            ->helperText($en ? null : 'Bản đồ trên trang Liên hệ hiển thị theo địa chỉ này. Để trống để ẩn và dùng địa chỉ trụ sở.')),
                        TextInput::make('map_url')->label('Link Google Maps (nút Chỉ đường)')->url(),
                        TextInput::make('map_embed_url')
                            ->label('Link nhúng bản đồ (tùy chọn)')
                            ->helperText('Để trống: bản đồ tự hiển thị theo địa chỉ. Muốn ghim chính xác: Google Maps → Chia sẻ → Nhúng bản đồ → dán phần src.')
                            ->url()
                            ->rule('starts_with:https://www.google.com/maps/embed'),
                    ]),
                    Tab::make('Trang chủ')->icon(Heroicon::OutlinedHome)->schema([
                        Section::make('Phần đầu trang')->schema([
                            $pair('home_hero_content', fn (string $name) => $block($name)->helperText(self::HELP_HERO)),
                            Uploads::image('home_hero_image', 'settings')->label('Ảnh sản phẩm nổi bật')->helperText('Ảnh nền trắng/trong suốt đẹp nhất. Để trống dùng ảnh mặc định.'),
                            $pair('home_hero_caption', fn (string $name) => Textarea::make($name)->label('Chú thích ảnh')->rows(2)->helperText('Dòng 1: chữ nhỏ (thương hiệu). Dòng 2: chữ đậm (tên sản phẩm). Để trống để ẩn.')),
                        ]),
                        Section::make('Khối "Về chúng tôi"')->schema([
                            $pair('home_about_content', fn (string $name) => $block($name)->helperText('Tiêu đề (H2), đoạn giới thiệu, rồi danh sách gạch đầu dòng các điểm nổi bật: phần in đậm đầu dòng là ý chính.'), stacked: true),
                            Uploads::image('home_about_image', 'settings')->label('Ảnh minh họa'),
                        ]),
                        Section::make('Số liệu & ngành phục vụ')->description('Hiển thị trên trang Giới thiệu.')->schema([
                            $pair('home_stats_content', fn (string $name) => $block($name)->label('Số liệu nổi bật')->helperText('Danh sách gạch đầu dòng, mỗi dòng một số liệu: số in đậm rồi đến nhãn, vd. **2011** năm thành lập. Tối đa 4.')),
                            $pair('industries_content', fn (string $name) => $block($name)->label('Ngành công nghiệp phục vụ')->helperText('Danh sách gạch đầu dòng, mỗi dòng một ngành. Biểu tượng tự chọn theo tên ngành.')),
                        ]),
                    ]),
                    Tab::make('Giới thiệu')->icon(Heroicon::OutlinedInformationCircle)->schema([
                        Section::make('Phần đầu trang')->schema([
                            $pair('about_hero_content', fn (string $name) => $block($name)->helperText('Tiêu đề (H2) là tiêu đề lớn; đoạn tiếp theo là giới thiệu.')),
                            Uploads::image('about_hero_image', 'settings')->label('Ảnh nền'),
                        ]),
                        Section::make('Câu chuyện công ty')->schema([
                            $pair('about_intro_content', fn (string $name) => $block($name)->helperText('Tiêu đề (H2) hiện ở cột trái; các đoạn và danh sách sau đó hiện ở cột phải.'), stacked: true),
                        ]),
                        Section::make('Lĩnh vực & cam kết')->schema([
                            $pair('about_pillars_content', fn (string $name) => $block($name)->label('Lĩnh vực M · R · O')->helperText('Mỗi lĩnh vực: một dòng Tiêu đề nhỏ (H3), vd. "Bảo trì (Maintenance)" — chữ cái lớn lấy từ chữ đầu của phần trong ngoặc — rồi một đoạn mô tả.')),
                            $pair('about_values_content', fn (string $name) => $block($name)->label('Cam kết')->helperText('Mỗi cam kết: một dòng Tiêu đề nhỏ (H3) rồi một đoạn mô tả.')),
                            $pair('about_origins', fn (string $name) => Textarea::make($name)->label('Xuất xứ hàng hóa')->rows(2)),
                        ]),
                    ]),
                    Tab::make('Dịch vụ')->icon(Heroicon::OutlinedWrenchScrewdriver)->schema([
                        Section::make('Phần đầu trang')->schema([
                            $pair('services_hero_content', fn (string $name) => $block($name)->helperText(self::HELP_HERO.' Đoạn giới thiệu cũng hiện trên trang chủ.')),
                            Uploads::image('services_hero_image', 'settings')->label('Ảnh nền')->helperText('Để trống dùng ảnh mặc định.'),
                        ]),
                        Section::make('Vì sao chọn & quy trình')->schema([
                            $pair('service_benefits_content', fn (string $name) => $block($name)->label('Vì sao chọn Nhật Duy')->helperText('Mỗi lý do: một dòng Tiêu đề nhỏ (H3) rồi một đoạn mô tả. Tối đa 6.')),
                            $pair('service_steps_content', fn (string $name) => $block($name)->label('Quy trình làm việc')->helperText('Mỗi bước: một dòng Tiêu đề nhỏ (H3) rồi một đoạn mô tả. Các bước tự đánh số.')),
                        ]),
                        Section::make('Câu hỏi thường gặp')->schema([
                            $pair('service_faqs_content', fn (string $name) => $block($name)->label('Câu hỏi thường gặp')->helperText('Mỗi câu: câu hỏi là một dòng Tiêu đề nhỏ (H3), câu trả lời là đoạn văn ngay dưới. Cũng được gửi cho Google dưới dạng FAQ.'), stacked: true),
                        ]),
                    ]),
                    Tab::make('SEO')->icon(Heroicon::OutlinedMagnifyingGlass)->schema([
                        $pair('seo_title', fn (string $name) => TextInput::make($name)->label('Tiêu đề trang chủ trên Google')->maxLength(70)),
                        $pair('seo_description', fn (string $name) => Textarea::make($name)->label('Mô tả mặc định trên Google')->rows(3)->maxLength(200)),
                        TextInput::make('google_site_verification')
                            ->label('Mã xác minh Google Search Console')
                            ->helperText('Search Console → Thêm tài sản → Thẻ HTML: dán phần content="…". Tệp xác minh cũ google853675dffcac168c.html đã được giữ lại.'),
                        TextInput::make('bing_site_verification')
                            ->label('Mã xác minh Bing Webmaster Tools')
                            ->helperText('Chỉ mục của Bing được Copilot, DuckDuckGo và một phần ChatGPT Search sử dụng. Bing → Thẻ meta msvalidate.01: dán phần content="…".'),
                    ]),
                ]),
            ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([EmbeddedSchema::make('form')])
                ->id('form')
                ->livewireSubmitHandler('save')
                ->footer([
                    Actions::make([
                        Action::make('save')->label('Lưu cài đặt')->submit('save')->keyBindings(['mod+s']),
                    ])->sticky(),
                ]),
        ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        Setting::putMany(collect($this->settingKeys())->mapWithKeys(fn (string $key) => [$key => $data[$key] ?? null])->all());

        Notification::make()->title('Đã lưu cài đặt website')->success()->send();
    }

    /**
     * @return array<int, string>
     */
    private function settingKeys(): array
    {
        return [
            'company_name', 'brand_name', 'tagline', 'phone', 'fax', 'email', 'contact_notify_email', 'zalo', 'tax_code', 'founded_year',
            'working_hours', 'address', 'office_address', 'map_url', 'map_embed_url',
            'home_hero_content', 'home_hero_image', 'home_hero_caption', 'home_about_content', 'home_about_image', 'home_stats_content', 'industries_content',
            'about_hero_content', 'about_hero_image', 'about_intro_content', 'about_pillars_content', 'about_values_content', 'about_origins',
            'services_hero_content', 'services_hero_image', 'service_benefits_content', 'service_steps_content', 'service_faqs_content', 'seo_title', 'seo_description', 'google_site_verification', 'bing_site_verification',
            ...array_map(fn (string $key) => "{$key}_en", Setting::TRANSLATABLE),
        ];
    }
}
