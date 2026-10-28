<?php

namespace App\Filament\Resources\Projects\Schemas;

use App\Filament\Support\Bilingual;
use App\Filament\Support\Uploads;
use App\Models\Project;
use App\Models\Setting;
use App\Services\ContentBlock;
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
                        Bilingual::translated('title', fn (string $name, bool $en) => TextInput::make($name)->label('Tên dự án')->required(! $en)->maxLength(255)),
                        Bilingual::translated('industry', fn (string $name, bool $en) => TextInput::make($name)
                            ->label('Ngành')
                            ->datalist(fn () => collect(ContentBlock::parse(Setting::get($en ? 'industries_content_en' : 'industries_content'))->items)
                                ->map(fn (array $item) => trim(($item['title'] ? $item['title'].' ' : '').$item['text']))
                                ->when(! $en, fn ($names) => Project::query()->whereNotNull('industry')->distinct()->pluck('industry')->merge($names))
                                ->unique()->values()->all())
                            ->maxLength(255)),
                        Bilingual::translated('client', fn (string $name) => TextInput::make($name)->label('Khách hàng')->maxLength(255)),
                        TextInput::make('year')->label('Năm')->maxLength(20),
                        Bilingual::translated('summary', fn (string $name) => Textarea::make($name)->label('Tóm tắt')->rows(2)->maxLength(500)),
                        TagsInput::make('tags')->label('Dịch vụ / thiết bị liên quan')->columnSpanFull(),
                    ]),
                    Section::make('Nội dung dự án')->schema([
                        Bilingual::translated('content', fn (string $name) => RichEditor::make($name)->label('Nội dung')->fileAttachmentsDisk('public')->fileAttachmentsDirectory('content')->fileAttachmentsVisibility('public'), stacked: true),
                    ]),
                ]),
                Group::make()->columnSpan(['lg' => 1])->schema([
                    Section::make('Hiển thị')->schema([
                        Toggle::make('is_published')->label('Hiển thị trên website')->default(true),
                        TextInput::make('sort_order')->label('Thứ tự hiển thị')->numeric()->default(0),
                        TextInput::make('slug')->label('Đường dẫn (slug)')->prefix('/du-an/')->alphaDash()->maxLength(190)->unique(ignoreRecord: true)->helperText('Để trống để tự tạo.'),
                    ]),
                    Section::make('Hình ảnh')->schema([
                        Uploads::image('cover', 'projects')->label('Ảnh bìa'),
                        Uploads::gallery('gallery', 'projects', 20)->label('Ảnh bổ sung'),
                    ]),
                    Section::make('SEO')->collapsible()->collapsed()->schema([
                        Bilingual::translated('meta_title', fn (string $name) => TextInput::make($name)->label('Tiêu đề SEO')->maxLength(255), stacked: true),
                        Bilingual::translated('meta_description', fn (string $name, bool $en) => Textarea::make($name)->label('Mô tả SEO')->rows(3)->maxLength(500)->when(! $en, fn (Textarea $input) => $input->placeholder(fn ($record) => $record ? Seo::projectDescription($record, generated: true) : null)->helperText('Để trống: hệ thống tự viết mô tả như nội dung mờ trong ô (khoảng 155 ký tự).')), stacked: true),
                    ]),
                ]),
            ]);
    }
}
