<?php

namespace App\Filament\Resources\Products\Tables;

use App\Models\Product;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                ImageColumn::make('image')->label('Ảnh')->disk('public')->square()->imageSize(56),
                TextColumn::make('name')
                    ->label('Tên sản phẩm')
                    ->searchable(['name', 'code'])
                    ->sortable()
                    ->wrap()
                    ->description(fn (Product $record): ?string => $record->code),
                TextColumn::make('category.name')->label('Nhóm')->sortable()->toggleable()->wrap(),
                TextColumn::make('brand.name')->label('Thương hiệu')->sortable()->toggleable(),
                ToggleColumn::make('is_published')->label('Hiển thị'),
                IconColumn::make('is_featured')->label('Tiêu biểu')->boolean()->toggleable(),
                TextColumn::make('view_count')->label('Lượt xem')->numeric()->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->label('Cập nhật')->since()->sortable()->toggleable(),
            ])
            ->filters([
                SelectFilter::make('product_category_id')->label('Nhóm')->relationship('category', 'name')->searchable()->preload(),
                SelectFilter::make('brand_id')->label('Thương hiệu')->relationship('brand', 'name')->searchable()->preload(),
                TernaryFilter::make('is_published')->label('Hiển thị'),
                TernaryFilter::make('is_featured')->label('Tiêu biểu'),
            ])
            ->recordActions([
                Action::make('view_on_site')
                    ->label('Xem')
                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->url(fn (Product $record): string => route('products.show', $record))
                    ->openUrlInNewTab()
                    ->visible(fn (Product $record): bool => $record->is_published),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('publish')
                        ->label('Hiển thị')
                        ->icon(Heroicon::OutlinedEye)
                        ->action(fn (Collection $records) => $records->each->update(['is_published' => true]))
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('unpublish')
                        ->label('Ẩn')
                        ->icon(Heroicon::OutlinedEyeSlash)
                        ->action(fn (Collection $records) => $records->each->update(['is_published' => false]))
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
