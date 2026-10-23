<?php

namespace App\Filament\Resources\Projects\Schemas;

use App\Filament\Support\Uploads;
use App\Models\Project;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Thông tin')->columns(2)->schema([
                    TextInput::make('title')->label('Tên dự án')->required()->maxLength(255)->columnSpanFull(),
                    TextInput::make('slug')->label('Đường dẫn (slug)')->prefix('/du-an/')->alphaDash()->maxLength(190)->unique(ignoreRecord: true)->helperText('Để trống để tự tạo.'),
                    TextInput::make('industry')
                        ->label('Ngành')
                        ->datalist(fn () => Project::query()->whereNotNull('industry')->distinct()->pluck('industry')->merge(collect(config('site.industries'))->pluck('name'))->unique()->values()->all())
                        ->maxLength(255),
                    TextInput::make('client')->label('Khách hàng')->maxLength(255),
                    TextInput::make('year')->label('Năm')->maxLength(20),
                    Textarea::make('summary')->label('Tóm tắt')->rows(2)->maxLength(500)->columnSpanFull(),
                    TagsInput::make('tags')->label('Dịch vụ / thiết bị liên quan')->columnSpanFull(),
                    TextInput::make('sort_order')->label('Thứ tự hiển thị')->numeric()->default(0),
                    Toggle::make('is_published')->label('Hiển thị trên website')->default(true),
                ]),
                Section::make('Nội dung')->schema([
                    Uploads::image('cover', 'projects')->label('Ảnh bìa'),
                    RichEditor::make('content')->label('Nội dung dự án')->fileAttachmentsDisk('public')->fileAttachmentsDirectory('content')->fileAttachmentsVisibility('public'),
                ]),
                Section::make('SEO')->collapsed()->schema([
                    TextInput::make('meta_title')->label('Tiêu đề SEO')->maxLength(255),
                    Textarea::make('meta_description')->label('Mô tả SEO')->rows(3)->maxLength(500),
                ]),
            ]);
    }
}
