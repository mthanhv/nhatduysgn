<?php

namespace App\Filament\Resources\Posts\Schemas;

use App\Filament\Support\Uploads;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make()->columnSpan(2)->schema([
                    TextInput::make('title')->label('Tiêu đề')->required()->maxLength(255),
                    Textarea::make('excerpt')->label('Tóm tắt (hiện trong danh sách tin)')->rows(3)->maxLength(500),
                    RichEditor::make('content')->label('Nội dung')->required()->fileAttachmentsDisk('public')->fileAttachmentsDirectory('content')->fileAttachmentsVisibility('public'),
                ]),
                Grid::make(1)->columnSpan(1)->schema([
                    Section::make('Đăng bài')->schema([
                        Toggle::make('is_published')->label('Hiển thị trên website')->default(true),
                        DateTimePicker::make('published_at')->label('Ngày đăng')->default(now())->native(false)->displayFormat('d/m/Y H:i')->helperText('Chọn ngày trong tương lai để hẹn giờ đăng.'),
                        TextInput::make('slug')->label('Đường dẫn (slug)')->alphaDash()->maxLength(190)->unique(ignoreRecord: true)->helperText('Để trống để tự tạo từ tiêu đề.'),
                    ]),
                    Section::make('Ảnh bìa')->schema([
                        Uploads::image('cover', 'posts')->hiddenLabel(),
                    ]),
                    Section::make('SEO')->collapsible()->collapsed()->schema([
                        TextInput::make('meta_title')->label('Tiêu đề SEO')->maxLength(255),
                        Textarea::make('meta_description')->label('Mô tả SEO')->rows(3)->maxLength(500),
                    ]),
                ]),
            ]);
    }
}
