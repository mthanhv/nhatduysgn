<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Filament\Support\Bilingual;
use App\Filament\Support\Uploads;
use App\Services\Seo;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Tabs::make()
                    ->persistTabInQueryString()
                    ->tabs([
                        Tab::make('Thông tin')->schema([
                            Grid::make(2)->schema([
                                Bilingual::translated('name', fn (string $name, bool $en) => TextInput::make($name)
                                    ->label('Tên sản phẩm')
                                    ->maxLength(255)
                                    ->when(! $en, fn (TextInput $input) => $input
                                        ->required()
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(function (Set $set, Get $get, ?string $state, string $operation): void {
                                            if ($operation === 'create' && blank($get('slug'))) {
                                                $set('slug', Str::slug((string) $state, '-', 'vi'));
                                            }
                                        }))),
                                TextInput::make('slug')
                                    ->label('Đường dẫn (slug)')
                                    ->prefix('/san-pham/')
                                    ->maxLength(190)
                                    ->alphaDash()
                                    ->unique(ignoreRecord: true)
                                    ->helperText('Để trống để tự tạo từ tên. Đổi slug sẽ đổi địa chỉ trang.'),
                                TextInput::make('code')->label('Mã / Model')->maxLength(255),
                                Select::make('product_category_id')
                                    ->label('Nhóm sản phẩm')
                                    ->relationship('category', 'name')
                                    ->searchable()
                                    ->preload(),
                                Select::make('brand_id')
                                    ->label('Thương hiệu')
                                    ->relationship('brand', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->createOptionForm([TextInput::make('name')->label('Tên thương hiệu')->required()]),
                                TextInput::make('origin')->label('Xuất xứ')->placeholder('vd. Made in Germany')->maxLength(255),
                                TextInput::make('sort_order')->label('Thứ tự hiển thị')->numeric()->default(0),
                                Bilingual::translated('summary', fn (string $name) => Textarea::make($name)->label('Mô tả ngắn')->rows(2)->maxLength(500)),
                                TagsInput::make('keywords')
                                    ->label('Từ khóa tìm kiếm')
                                    ->separator(',')
                                    ->placeholder('vd. bôi trơn, chống xoay')
                                    ->helperText('Giúp khách tìm thấy sản phẩm khi gõ các từ này (không hiển thị trên trang).')
                                    ->columnSpanFull(),
                                Toggle::make('is_published')->label('Hiển thị trên website')->default(true),
                                Toggle::make('is_featured')->label('Sản phẩm tiêu biểu (trang chủ)'),
                            ]),
                        ]),
                        Tab::make('Mô tả & thông số')->schema([
                            Bilingual::translated('description', fn (string $name) => RichEditor::make($name)
                                ->label('Mô tả chi tiết')
                                ->fileAttachmentsDisk('public')
                                ->fileAttachmentsDirectory('content')
                                ->fileAttachmentsVisibility('public')
                                ->fileAttachmentsAcceptedFileTypes(Uploads::IMAGE_TYPES), stacked: true),
                            Bilingual::translated('specifications', fn (string $name, bool $en) => RichEditor::make($name)
                                ->label('Thông số kỹ thuật')
                                ->helperText($en ? null : 'Dùng bảng để trình bày thông số (nút bảng trên thanh công cụ).')
                                ->fileAttachmentsDisk('public')
                                ->fileAttachmentsDirectory('content')
                                ->fileAttachmentsVisibility('public')
                                ->fileAttachmentsAcceptedFileTypes(Uploads::IMAGE_TYPES), stacked: true),
                        ]),
                        Tab::make('Hình ảnh & tài liệu')->schema([
                            Uploads::image('image', 'products')->label('Ảnh chính'),
                            Uploads::gallery('gallery', 'products', 12)->label('Ảnh bổ sung'),
                            Repeater::make('documents')
                                ->label('Tài liệu tải về (catalogue, hướng dẫn…)')
                                ->schema([
                                    TextInput::make('title')->label('Tên tài liệu')->required(),
                                    Uploads::document('file', 'documents')->label('Tệp')->required(),
                                ])
                                ->columns(2)
                                ->addActionLabel('Thêm tài liệu')
                                ->collapsible()
                                ->defaultItems(0),
                        ]),
                        Tab::make('SEO')->schema([
                            Section::make()->schema([
                                Bilingual::translated('meta_title', fn (string $name, bool $en) => TextInput::make($name)->label('Tiêu đề SEO')->maxLength(255)->helperText($en ? null : 'Để trống sẽ dùng tên sản phẩm.')),
                                Bilingual::translated('meta_description', fn (string $name, bool $en) => Textarea::make($name)->label('Mô tả SEO')->rows(3)->maxLength(500)
                                    ->when(! $en, fn (Textarea $input) => $input
                                        ->placeholder(fn ($record) => $record ? Seo::productDescription($record, generated: true) : null)
                                        ->helperText('Để trống: hệ thống tự viết mô tả như nội dung mờ trong ô (khoảng 155 ký tự).'))),
                                TextInput::make('legacy_id')->label('ID trên website cũ')->disabled()->dehydrated(false)->visible(fn (?string $state) => filled($state)),
                            ]),
                        ]),
                    ]),
            ]);
    }
}
