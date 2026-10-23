<?php

namespace App\Filament\Resources\Redirects;

use App\Filament\Resources\Redirects\Pages\ManageRedirects;
use App\Models\Redirect;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use UnitEnum;

class RedirectResource extends Resource
{
    protected static ?string $model = Redirect::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUturnRight;

    protected static string|UnitEnum|null $navigationGroup = 'Hệ thống';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'chuyển hướng';

    protected static ?string $pluralModelLabel = 'Chuyển hướng URL';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $recordTitleAttribute = 'from_path';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('from_path')
                    ->label('Đường dẫn cũ')
                    ->required()
                    ->maxLength(512)
                    ->placeholder('/duong-dan-cu.html')
                    ->unique(ignoreRecord: true)
                    ->helperText('Chỉ phần đường dẫn, không gồm tên miền. Ưu tiên hơn các quy tắc tự động.')
                    ->columnSpanFull(),
                TextInput::make('to_url')
                    ->label('Chuyển đến')
                    ->required()
                    ->maxLength(1024)
                    ->placeholder('/san-pham/ten-san-pham hoặc https://…')
                    ->columnSpanFull(),
                Select::make('status_code')->label('Loại')->options([301 => '301 — Vĩnh viễn (SEO)', 302 => '302 — Tạm thời'])->default(301)->required(),
                Toggle::make('is_active')->label('Đang bật')->default(true)->inline(false),
                TextInput::make('note')->label('Ghi chú')->maxLength(255)->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('hits', 'desc')
            ->columns([
                TextColumn::make('from_path')->label('Đường dẫn cũ')->searchable()->wrap()->fontFamily('mono'),
                TextColumn::make('to_url')->label('Chuyển đến')->searchable()->wrap(),
                TextColumn::make('status_code')->label('Loại')->badge(),
                TextColumn::make('hits')->label('Lượt dùng')->numeric()->sortable(),
                TextColumn::make('last_hit_at')->label('Lần cuối')->since()->sortable()->toggleable(),
                ToggleColumn::make('is_active')->label('Bật'),
            ])
            ->recordActions([EditAction::make()->iconButton()->tooltip('Chỉnh sửa'), DeleteAction::make()->iconButton()->tooltip('Xóa')])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageRedirects::route('/'),
        ];
    }
}
