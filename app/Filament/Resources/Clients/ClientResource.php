<?php

namespace App\Filament\Resources\Clients;

use App\Filament\Resources\Clients\Pages\ManageClients;
use App\Filament\Support\Uploads;
use App\Models\Client;
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

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static string|UnitEnum|null $navigationGroup = 'Nội dung';

    protected static ?int $navigationSort = 4;

    protected static ?string $modelLabel = 'khách hàng';

    protected static ?string $pluralModelLabel = 'Khách hàng tiêu biểu';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('name')->label('Tên khách hàng')->required()->maxLength(255),
                TextInput::make('website')->label('Website')->url()->maxLength(255),
                Uploads::image('logo', 'clients')->label('Logo')->columnSpanFull(),
                TextInput::make('sort_order')->label('Thứ tự hiển thị')->numeric()->default(0),
                Toggle::make('is_published')->label('Hiển thị trên trang Giới thiệu')->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                ImageColumn::make('logo')->label('Logo')->disk('public')->imageHeight(40),
                TextColumn::make('name')->label('Khách hàng')->searchable(),
                ToggleColumn::make('is_published')->label('Hiển thị'),
            ])
            ->recordActions([EditAction::make()->iconButton()->tooltip('Chỉnh sửa'), DeleteAction::make()->iconButton()->tooltip('Xóa')])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageClients::route('/'),
        ];
    }
}
