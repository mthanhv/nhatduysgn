<?php

namespace App\Filament\Resources\Services\Schemas;

use App\Filament\Support\Uploads;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TagsInput;
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
                        TextInput::make('title')->label('Tên dịch vụ')->required()->maxLength(255),
                        Textarea::make('summary')->label('Tóm tắt (hiện trên trang chủ)')->rows(2)->maxLength(500),
                        TagsInput::make('equipment')->label('Thiết bị / thương hiệu sử dụng')->placeholder('vd. ITH'),
                    ]),
                    Section::make('Mô tả chi tiết')->schema([
                        RichEditor::make('description')->hiddenLabel()->fileAttachmentsDisk('public')->fileAttachmentsDirectory('content')->fileAttachmentsVisibility('public'),
                    ]),
                ]),
                Group::make()->columnSpan(['lg' => 1])->schema([
                    Section::make('Hiển thị')->schema([
                        Toggle::make('is_published')->label('Hiển thị trên website')->default(true),
                        TextInput::make('sort_order')->label('Thứ tự hiển thị')->numeric()->default(0),
                        TextInput::make('slug')->label('Mã neo (slug)')->prefix('#')->alphaDash()->maxLength(190)->unique(ignoreRecord: true)->helperText('Để trống để tự tạo.'),
                    ]),
                    Section::make('Ảnh minh họa')->schema([
                        Uploads::image('image', 'services')->hiddenLabel(),
                    ]),
                    Section::make('SEO')->collapsible()->collapsed()->schema([
                        TextInput::make('meta_title')->label('Tiêu đề SEO')->maxLength(255),
                        Textarea::make('meta_description')->label('Mô tả SEO')->rows(3)->maxLength(500),
                    ]),
                ]),
            ]);
    }
}
