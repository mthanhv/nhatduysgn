<?php

namespace App\Filament\Resources\Projects\Schemas;

use App\Filament\Support\Uploads;
use App\Models\Project;
use App\Services\Seo;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make()->columnSpan(['lg' => 2])->schema([
                    Section::make('Thông tin')->columns(3)->schema([
                        TextInput::make('title')->label('Tên dự án')->required()->maxLength(255)->columnSpanFull(),
                        TextInput::make('industry')
                            ->label('Ngành')
                            ->datalist(fn () => Project::query()->whereNotNull('industry')->distinct()->pluck('industry')->merge(collect(config('site.industries'))->pluck('name'))->unique()->values()->all())
                            ->maxLength(255),
                        TextInput::make('client')->label('Khách hàng')->maxLength(255),
                        TextInput::make('year')->label('Năm')->maxLength(20),
                        Textarea::make('summary')->label('Tóm tắt')->rows(2)->maxLength(500)->columnSpanFull(),
                        TagsInput::make('tags')->label('Dịch vụ / thiết bị liên quan')->columnSpanFull(),
                    ]),
                    Section::make('Nội dung dự án')->schema([
                        RichEditor::make('content')->hiddenLabel()->fileAttachmentsDisk('public')->fileAttachmentsDirectory('content')->fileAttachmentsVisibility('public'),
                    ]),
                ]),
                Group::make()->columnSpan(['lg' => 1])->schema([
                    Section::make('Hiển thị')->schema([
                        Toggle::make('is_published')->label('Hiển thị trên website')->default(true),
                        TextInput::make('sort_order')->label('Thứ tự hiển thị')->numeric()->default(0),
                        TextInput::make('slug')->label('Đường dẫn (slug)')->prefix('/du-an/')->alphaDash()->maxLength(190)->unique(ignoreRecord: true)->helperText('Để trống để tự tạo.'),
                    ]),
                    Section::make('Ảnh bìa')->schema([
                        Uploads::image('cover', 'projects')->hiddenLabel(),
                    ]),
                    Section::make('SEO')->collapsible()->collapsed()->schema([
                        TextInput::make('meta_title')->label('Tiêu đề SEO')->maxLength(255),
                        Textarea::make('meta_description')->label('Mô tả SEO')->rows(3)->maxLength(500)->placeholder(fn ($record) => $record ? Seo::projectDescription($record, generated: true) : null)->helperText('Để trống: hệ thống tự viết mô tả như nội dung mờ trong ô (khoảng 155 ký tự).'),
                    ]),
                ]),
            ]);
    }
}
