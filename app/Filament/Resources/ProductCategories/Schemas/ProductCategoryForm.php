<?php

namespace App\Filament\Resources\ProductCategories\Schemas;

use App\Filament\Support\Uploads;
use App\Models\ProductCategory;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
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
            ->components([
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
                    Select::make('icon')->label('Biểu tượng')->options(self::ICONS),
                    TextInput::make('sort_order')->label('Thứ tự hiển thị')->numeric()->default(0),
                    Textarea::make('summary')->label('Mô tả ngắn (hiện dưới tên nhóm)')->rows(2)->maxLength(500)->columnSpanFull(),
                    Toggle::make('is_published')->label('Hiển thị trên website')->default(true),
                    Toggle::make('is_featured')->label('Nhóm chủ lực (trang Sản phẩm)'),
                ]),
                Section::make('Nội dung & hình ảnh')->schema([
                    Uploads::image('image', 'categories')->label('Ảnh đại diện'),
                    RichEditor::make('description')->label('Giới thiệu nhóm sản phẩm')->fileAttachmentsDisk('public')->fileAttachmentsDirectory('content')->fileAttachmentsVisibility('public'),
                ]),
                Section::make('SEO')->collapsed()->schema([
                    TextInput::make('meta_title')->label('Tiêu đề SEO')->maxLength(255),
                    Textarea::make('meta_description')->label('Mô tả SEO')->rows(3)->maxLength(500),
                ]),
            ]);
    }
}
