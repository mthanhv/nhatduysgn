<?php

namespace App\Filament\Resources\Certificates;

use App\Filament\Resources\Certificates\Pages\ManageCertificates;
use App\Filament\Support\SortOrder;
use App\Filament\Support\Uploads;
use App\Models\Certificate;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use UnitEnum;

class CertificateResource extends Resource
{
    protected static ?string $model = Certificate::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCheckBadge;

    protected static string|UnitEnum|null $navigationGroup = 'Nội dung';

    protected static ?int $navigationSort = 5;

    protected static ?string $modelLabel = 'chứng nhận';

    protected static ?string $pluralModelLabel = 'Chứng nhận';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('title')->label('Tên chứng nhận / giấy ủy quyền')->required()->maxLength(255)->columnSpanFull(),
                Uploads::image('image', 'certificates')->label('Ảnh chụp (hiển thị)'),
                Uploads::document('file', 'certificates')->label('Bản đầy đủ (PDF, tùy chọn)'),
                TextInput::make('sort_order')->label('Thứ tự hiển thị')->numeric()->default(0),
                Toggle::make('is_published')->label('Hiển thị trên trang Giới thiệu')->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->description(SortOrder::HELP)
            ->columns([
                ImageColumn::make('image')->label('Ảnh')->disk('public')->imageHeight(56),
                TextColumn::make('title')->label('Chứng nhận')->searchable()->wrap(),
                SortOrder::column(),
                ToggleColumn::make('is_published')->label('Hiển thị'),
            ])
            ->recordActions([EditAction::make()->iconButton()->tooltip('Chỉnh sửa'), DeleteAction::make()->iconButton()->tooltip('Xóa')])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCertificates::route('/'),
        ];
    }
}
