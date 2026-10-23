<?php

namespace App\Filament\Pages;

use App\Filament\Support\Uploads;
use App\Models\Setting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
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

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(collect($this->settingKeys())->mapWithKeys(fn (string $key) => [$key => Setting::get($key)])->all());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Tabs::make()->persistTabInQueryString()->tabs([
                    Tab::make('Công ty & liên hệ')->icon(Heroicon::OutlinedBuildingOffice)->schema([
                        Grid::make(2)->schema([
                            TextInput::make('company_name')->label('Tên công ty')->required()->columnSpanFull(),
                            TextInput::make('brand_name')->label('Tên ngắn / thương hiệu')->required(),
                            TextInput::make('tagline')->label('Khẩu hiệu dưới logo'),
                            TextInput::make('phone')->label('Điện thoại')->tel()->required(),
                            TextInput::make('fax')->label('Fax'),
                            TextInput::make('email')->label('Email liên hệ')->email()->required(),
                            TextInput::make('contact_notify_email')->label('Email nhận thông báo liên hệ')->email()->required()->helperText('Tin nhắn từ form liên hệ được gửi về địa chỉ này.'),
                            TextInput::make('zalo')->label('Số Zalo')->helperText('Để trống để ẩn nút Zalo.'),
                            TextInput::make('tax_code')->label('Mã số thuế'),
                            TextInput::make('founded_year')->label('Năm thành lập'),
                            TextInput::make('working_hours')->label('Giờ làm việc'),
                            TextInput::make('address')->label('Địa chỉ')->required()->columnSpanFull(),
                            TextInput::make('map_url')->label('Link Google Maps (nút Chỉ đường)')->url()->columnSpanFull(),
                            TextInput::make('map_embed_url')
                                ->label('Link nhúng bản đồ (Google Maps → Chia sẻ → Nhúng bản đồ → phần src)')
                                ->url()
                                ->rule('starts_with:https://www.google.com/maps/embed')
                                ->columnSpanFull(),
                        ]),
                    ]),
                    Tab::make('Trang chủ')->icon(Heroicon::OutlinedHome)->schema([
                        Section::make('Phần đầu trang')->columns(2)->schema([
                            TextInput::make('home_hero_eyebrow')->label('Dòng nhỏ phía trên tiêu đề')->columnSpanFull(),
                            Textarea::make('home_hero_title')->label('Tiêu đề lớn')->rows(2)->required()->columnSpanFull(),
                            Textarea::make('home_hero_text')->label('Đoạn giới thiệu')->rows(3)->columnSpanFull(),
                            Uploads::image('home_hero_image', 'settings')->label('Ảnh sản phẩm nổi bật')->helperText('Ảnh nền trắng/trong suốt đẹp nhất. Để trống dùng ảnh mặc định.')->columnSpanFull(),
                            TextInput::make('home_hero_caption_brand')->label('Chú thích ảnh — dòng 1'),
                            TextInput::make('home_hero_caption_title')->label('Chú thích ảnh — dòng 2'),
                        ]),
                        Section::make('Số liệu nổi bật')->schema([
                            Repeater::make('home_stats')->hiddenLabel()->schema([
                                TextInput::make('value')->label('Số')->required(),
                                TextInput::make('label')->label('Nhãn')->required(),
                            ])->columns(2)->maxItems(4)->reorderable()->addActionLabel('Thêm số liệu'),
                        ]),
                        Section::make('Khối "Về chúng tôi"')->schema([
                            TextInput::make('home_about_title')->label('Tiêu đề'),
                            Textarea::make('home_about_text')->label('Nội dung')->rows(4),
                            Repeater::make('home_about_points')->label('Điểm nổi bật')->schema([
                                TextInput::make('title')->label('In đậm')->required(),
                                TextInput::make('text')->label('Mô tả')->required(),
                            ])->columns(2)->reorderable()->addActionLabel('Thêm điểm'),
                            Uploads::image('home_about_image', 'settings')->label('Ảnh minh họa'),
                        ]),
                        Section::make('Ngành công nghiệp phục vụ')->schema([
                            Repeater::make('industries')->hiddenLabel()->schema([
                                TextInput::make('name')->label('Ngành')->required(),
                                Select::make('icon')->label('Biểu tượng')->options([
                                    'oil' => 'Dầu khí', 'bolt' => 'Điện', 'factory' => 'Nhà máy', 'flask' => 'Hóa chất', 'food' => 'Thực phẩm', 'wrench' => 'Cơ khí',
                                ]),
                            ])->columns(2)->reorderable()->addActionLabel('Thêm ngành'),
                        ]),
                    ]),
                    Tab::make('Giới thiệu')->icon(Heroicon::OutlinedInformationCircle)->schema([
                        Section::make('Phần đầu trang')->schema([
                            Textarea::make('about_hero_title')->label('Tiêu đề lớn')->rows(2),
                            Textarea::make('about_hero_text')->label('Đoạn giới thiệu')->rows(2),
                            Uploads::image('about_hero_image', 'settings')->label('Ảnh nền'),
                        ]),
                        Section::make('Câu chuyện công ty')->schema([
                            TextInput::make('about_intro_title')->label('Tiêu đề'),
                            RichEditor::make('about_intro_body')->label('Nội dung')->toolbarButtons([['bold', 'italic', 'link'], ['bulletList', 'orderedList'], ['undo', 'redo']]),
                        ]),
                        Section::make('Lĩnh vực M · R · O')->schema([
                            Repeater::make('about_pillars')->hiddenLabel()->schema([
                                TextInput::make('letter')->label('Chữ')->maxLength(2)->required(),
                                TextInput::make('en')->label('Tiếng Anh'),
                                TextInput::make('title')->label('Tiêu đề')->required(),
                                Textarea::make('text')->label('Mô tả')->rows(2)->columnSpanFull(),
                            ])->columns(3)->maxItems(4)->reorderable(),
                        ]),
                        Section::make('Đối tác nổi bật (JBE)')->columns(2)->schema([
                            TextInput::make('about_spotlight_label')->label('Dòng nhỏ'),
                            TextInput::make('about_spotlight_country')->label('Quốc gia'),
                            TextInput::make('about_spotlight_title')->label('Tiêu đề')->columnSpanFull()->helperText('Để trống để ẩn khối này.'),
                            Textarea::make('about_spotlight_text')->label('Nội dung')->rows(3)->columnSpanFull(),
                            Uploads::image('about_spotlight_image', 'settings')->label('Logo đối tác')->columnSpanFull(),
                        ]),
                        Section::make('Thương hiệu & cam kết')->schema([
                            Textarea::make('about_origins')->label('Xuất xứ hàng hóa')->rows(2),
                            Repeater::make('about_values')->label('Cam kết')->schema([
                                TextInput::make('title')->label('Tiêu đề')->required(),
                                Textarea::make('text')->label('Mô tả')->rows(2),
                            ])->reorderable()->addActionLabel('Thêm cam kết'),
                        ]),
                    ]),
                    Tab::make('Dịch vụ')->icon(Heroicon::OutlinedWrenchScrewdriver)->schema([
                        Textarea::make('services_intro')->label('Đoạn giới thiệu trang Dịch vụ')->rows(3),
                        Repeater::make('service_steps')->label('Quy trình làm việc')->schema([
                            TextInput::make('title')->label('Bước')->required(),
                            Textarea::make('text')->label('Mô tả')->rows(2),
                        ])->reorderable()->maxItems(6)->addActionLabel('Thêm bước'),
                    ]),
                    Tab::make('SEO')->icon(Heroicon::OutlinedMagnifyingGlass)->schema([
                        TextInput::make('seo_title')->label('Tiêu đề trang chủ trên Google')->maxLength(70),
                        Textarea::make('seo_description')->label('Mô tả mặc định trên Google')->rows(3)->maxLength(200),
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
            'working_hours', 'address', 'map_url', 'map_embed_url',
            'home_hero_eyebrow', 'home_hero_title', 'home_hero_text', 'home_hero_image', 'home_hero_caption_brand', 'home_hero_caption_title',
            'home_stats', 'home_about_title', 'home_about_text', 'home_about_points', 'home_about_image', 'industries',
            'about_hero_title', 'about_hero_text', 'about_hero_image', 'about_intro_title', 'about_intro_body', 'about_pillars',
            'about_spotlight_label', 'about_spotlight_country', 'about_spotlight_title', 'about_spotlight_text', 'about_spotlight_image',
            'about_origins', 'about_values', 'services_intro', 'service_steps', 'seo_title', 'seo_description',
        ];
    }
}
