<?php

namespace App\Filament\Resources\Brands;

use App\Filament\Resources\Brands\Pages\ManageBrands;
use App\Filament\Support\Bilingual;
use App\Filament\Support\SortOrder;
use App\Filament\Support\Uploads;
use App\Models\Brand;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use UnitEnum;

class BrandResource extends Resource
{
    protected static ?string $model = Brand::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static string|UnitEnum|null $navigationGroup = 'Sản phẩm';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'thương hiệu';

    protected static ?string $pluralModelLabel = 'Thương hiệu';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('name')->label('Tên thương hiệu')->required()->maxLength(255),
                TextInput::make('slug')->label('Đường dẫn (slug)')->prefix('/thuong-hieu/')->alphaDash()->maxLength(190)->unique(ignoreRecord: true)->helperText('Để trống để tự tạo.'),
                TextInput::make('website')->label('Website hãng')->url()->maxLength(255),
                Uploads::image('logo', 'brands')->label('Logo')->columnSpanFull(),
                Bilingual::translated('country', fn (string $name) => TextInput::make($name)->label('Quốc gia')->maxLength(255)),
                Bilingual::translated('description', fn (string $name) => Textarea::make($name)->label('Giới thiệu ngắn')->rows(3)),
                TextInput::make('sort_order')->label('Thứ tự hiển thị')->numeric()->default(0),
                Toggle::make('is_published')->label('Hiển thị trên website')->default(true),
                Toggle::make('is_featured')->label('Hiện trên trang chủ'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->description(SortOrder::HELP)
            ->columns([
                ImageColumn::make('logo')->label('Logo')->disk('public')->imageHeight(36),
                TextColumn::make('name')->label('Thương hiệu')->searchable()->sortable(),
                TextColumn::make('country')->label('Quốc gia')->toggleable(),
                TextColumn::make('products_count')->label('Sản phẩm')->counts('products')->sortable(),
                SortOrder::column(),
                ToggleColumn::make('is_published')->label('Hiển thị'),
                IconColumn::make('is_featured')->label('Trang chủ')->boolean()->trueIcon(Heroicon::Star)->falseIcon(Heroicon::OutlinedStar)->trueColor('warning')->falseColor('gray'),
            ])
            ->recordActions([
                Action::make('view_on_site')
                    ->label('Xem trên website')
                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->iconButton()
                    ->color('gray')
                    ->tooltip('Xem trên website')
                    ->url(fn (Brand $record): string => route('products.brand', $record))
                    ->openUrlInNewTab(),
                EditAction::make()->iconButton()->tooltip('Chỉnh sửa'),
                DeleteAction::make()->iconButton()->tooltip('Xóa'),
            ])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageBrands::route('/'),
        ];
    }
}
