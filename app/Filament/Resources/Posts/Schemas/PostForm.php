<?php

namespace App\Filament\Resources\Posts\Schemas;

use App\Filament\Support\Bilingual;
use App\Filament\Support\Uploads;
use App\Services\Seo;
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
                    Bilingual::translated('title', fn (string $name, bool $en) => TextInput::make($name)->label('Tiêu đề')->required(! $en)->maxLength(255)),
                    Bilingual::translated('excerpt', fn (string $name) => Textarea::make($name)->label('Tóm tắt (hiện trong danh sách tin)')->rows(3)->maxLength(500)),
                    Bilingual::translated('content', fn (string $name, bool $en) => RichEditor::make($name)->label('Nội dung')->required(! $en)->fileAttachmentsDisk('public')->fileAttachmentsDirectory('content')->fileAttachmentsVisibility('public'), stacked: true),
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
                        Bilingual::translated('meta_title', fn (string $name) => TextInput::make($name)->label('Tiêu đề SEO')->maxLength(255), stacked: true),
                        Bilingual::translated('meta_description', fn (string $name, bool $en) => Textarea::make($name)->label('Mô tả SEO')->rows(3)->maxLength(500)->when(! $en, fn (Textarea $input) => $input->placeholder(fn ($record) => $record ? Seo::postDescription($record, generated: true) : null)->helperText('Để trống: hệ thống tự viết mô tả như nội dung mờ trong ô (khoảng 155 ký tự).')), stacked: true),
                    ]),
                ]),
            ]);
    }
}
