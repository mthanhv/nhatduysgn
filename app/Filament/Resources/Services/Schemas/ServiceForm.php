<?php

namespace App\Filament\Resources\Services\Schemas;

use App\Filament\Support\Uploads;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Thông tin')->columns(2)->schema([
                    TextInput::make('title')->label('Tên dịch vụ')->required()->maxLength(255)->columnSpanFull(),
                    TextInput::make('slug')->label('Mã neo (slug)')->prefix('/dich-vu#')->alphaDash()->maxLength(190)->unique(ignoreRecord: true)->helperText('Để trống để tự tạo.'),
                    TextInput::make('sort_order')->label('Thứ tự hiển thị')->numeric()->default(0),
                    Textarea::make('summary')->label('Tóm tắt (hiện trên trang chủ)')->rows(2)->maxLength(500)->columnSpanFull(),
                    TagsInput::make('equipment')->label('Thiết bị / thương hiệu sử dụng')->placeholder('vd. ITH')->columnSpanFull(),
                    Toggle::make('is_published')->label('Hiển thị trên website')->default(true),
                ]),
                Section::make('Nội dung')->schema([
                    Uploads::image('image', 'services')->label('Ảnh minh họa'),
                    RichEditor::make('description')->label('Mô tả chi tiết')->fileAttachmentsDisk('public')->fileAttachmentsDirectory('content')->fileAttachmentsVisibility('public'),
                ]),
                Section::make('SEO')->collapsed()->schema([
                    TextInput::make('meta_title')->label('Tiêu đề SEO')->maxLength(255),
                    Textarea::make('meta_description')->label('Mô tả SEO')->rows(3)->maxLength(500),
                ]),
            ]);
    }
}
