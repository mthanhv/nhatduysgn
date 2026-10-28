<?php

namespace App\Filament\Resources\Services\Schemas;

use App\Filament\Support\Bilingual;
use App\Filament\Support\Uploads;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make()->columnSpan(['lg' => 2])->schema([
                    Section::make('Thông tin')->schema([
                        Bilingual::translated('title', fn (string $name, bool $en) => TextInput::make($name)->label('Tên dịch vụ')->required(! $en)->maxLength(255)),
                        Bilingual::translated('summary', fn (string $name) => Textarea::make($name)->label('Tóm tắt (hiện trên trang chủ)')->rows(2)->maxLength(500)),
                    ]),
                    Section::make('Mô tả chi tiết')->schema([
                        Bilingual::translated('description', fn (string $name) => RichEditor::make($name)->label('Mô tả chi tiết')->fileAttachmentsDisk('public')->fileAttachmentsDirectory('content')->fileAttachmentsVisibility('public'), stacked: true),
                    ]),
                ]),
                Group::make()->columnSpan(['lg' => 1])->schema([
                    Section::make('Hiển thị')->schema([
                        Toggle::make('is_published')->label('Hiển thị trên website')->default(true),
                        TextInput::make('sort_order')->label('Thứ tự hiển thị')->numeric()->default(0),
                        TextInput::make('slug')->label('Mã neo (slug)')->prefix('#')->alphaDash()->maxLength(190)->unique(ignoreRecord: true)->helperText('Để trống để tự tạo.'),
                    ]),
                    Section::make('Ảnh minh họa')->schema([
                        Uploads::image('image', 'services')->label('Ảnh chính'),
                        Uploads::gallery('gallery', 'services', 12)->label('Ảnh bổ sung'),
                    ]),
                    Section::make('SEO')->collapsible()->collapsed()->schema([
                        Bilingual::translated('meta_title', fn (string $name) => TextInput::make($name)->label('Tiêu đề SEO')->maxLength(255), stacked: true),
                        Bilingual::translated('meta_description', fn (string $name) => Textarea::make($name)->label('Mô tả SEO')->rows(3)->maxLength(500), stacked: true),
                    ]),
                ]),
            ]);
    }
}
