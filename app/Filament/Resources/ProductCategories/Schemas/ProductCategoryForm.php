<?php

namespace App\Filament\Resources\ProductCategories\Schemas;

use App\Filament\Support\Uploads;
use App\Models\ProductCategory;
use App\Services\Seo;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class ProductCategoryForm
{
    public const ICONS = [
        'wrench' => 'Dụng cụ / xiết bu lông', 'ring' => 'Vòng bi', 'drop' => 'Dầu mỡ bôi trơn', 'glue' => 'Keo', 'can' => 'Bình xịt / hóa chất',
        'pipe' => 'Ống thép', 'tube' => 'Lò hơi / trao đổi nhiệt', 'valve' => 'Van / seal', 'clamp' => 'Đai kẹp', 'mark' => 'Đánh dấu',
        'heat' => 'Gia nhiệt', 'shim' => 'Shim', 'bolt' => 'Điện / cách điện', 'hoist' => 'Nâng hạ', 'gun' => 'Súng khí nén',
        'scan' => 'Thiết bị đo / phân tích', 'box' => 'Khác',
    ];

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make()->columnSpan(['lg' => 2])->schema([
                    Section::make('Thông tin')->columns(2)->schema([
                        TextInput::make('name')->label('Tên nhóm')->required()->maxLength(255)->columnSpanFull(),
                        TextInput::make('slug')
                            ->label('Đường dẫn (slug)')
                            ->prefix('/danh-muc/')
                            ->alphaDash()
                            ->maxLength(190)
                            ->unique(ignoreRecord: true)
                            ->helperText('Để trống để tự tạo từ tên.'),
                        Select::make('parent_id')
                            ->label('Thuộc nhóm cha')
                            ->relationship('parent', 'name', fn (Builder $query, ?ProductCategory $record) => $query->when($record, fn (Builder $query) => $query->whereKeyNot($record->id)))
                            ->searchable()
                            ->preload()
                            ->placeholder('— Nhóm cấp 1 —'),
                        Textarea::make('summary')->label('Mô tả ngắn (hiện dưới tên nhóm)')->rows(2)->maxLength(500)->columnSpanFull(),
                    ]),
                    Section::make('Giới thiệu nhóm sản phẩm')->schema([
                        RichEditor::make('description')->hiddenLabel()->fileAttachmentsDisk('public')->fileAttachmentsDirectory('content')->fileAttachmentsVisibility('public'),
                    ]),
                ]),
                Group::make()->columnSpan(['lg' => 1])->schema([
                    Section::make('Hiển thị')->schema([
                        Toggle::make('is_published')->label('Hiển thị trên website')->default(true),
                        Toggle::make('is_featured')->label('Nhóm chủ lực (trang Sản phẩm)'),
                        Select::make('icon')->label('Biểu tượng')->options(self::ICONS),
                        TextInput::make('sort_order')->label('Thứ tự hiển thị')->numeric()->default(0),
                    ]),
                    Section::make('Ảnh đại diện')->schema([
                        Uploads::image('image', 'categories')->hiddenLabel(),
                    ]),
                    Section::make('SEO')->collapsible()->collapsed()->schema([
                        TextInput::make('meta_title')->label('Tiêu đề SEO')->maxLength(255),
                        Textarea::make('meta_description')->label('Mô tả SEO')->rows(3)->maxLength(500)->placeholder(fn ($record) => $record ? Seo::categoryDescription($record, generated: true) : null)->helperText('Để trống: hệ thống tự viết mô tả như nội dung mờ trong ô (khoảng 155 ký tự).'),
                    ]),
                ]),
            ]);
    }
}
