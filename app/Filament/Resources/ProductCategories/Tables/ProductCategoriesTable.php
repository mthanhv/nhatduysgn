<?php

namespace App\Filament\Resources\ProductCategories\Tables;

use App\Models\ProductCategory;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductCategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('name')
                    ->label('Tên nhóm')
                    ->searchable()
                    ->wrap()
                    ->description(fn (ProductCategory $record): ?string => $record->parent ? '↳ '.$record->parent->name : null),
                TextColumn::make('products_count')->label('Sản phẩm')->counts('products')->sortable(),
                ToggleColumn::make('is_published')->label('Hiển thị'),
                IconColumn::make('is_featured')->label('Chủ lực')->boolean()->trueIcon(Heroicon::Star)->falseIcon(Heroicon::OutlinedStar)->trueColor('warning')->falseColor('gray'),
                TextColumn::make('sort_order')->label('Thứ tự')->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('level')
                    ->label('Cấp')
                    ->options(['root' => 'Nhóm cấp 1', 'child' => 'Nhóm con'])
                    ->query(fn (Builder $query, array $data) => match ($data['value'] ?? null) {
                        'root' => $query->whereNull('parent_id'),
                        'child' => $query->whereNotNull('parent_id'),
                        default => $query,
                    }),
                SelectFilter::make('parent_id')->label('Nhóm cha')->relationship('parent', 'name')->searchable()->preload(),
                TernaryFilter::make('is_published')->label('Hiển thị'),
            ])
            ->recordActions([
                Action::make('view_on_site')
                    ->label('Xem trên website')
                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->iconButton()
                    ->color('gray')
                    ->tooltip('Xem trên website')
                    ->url(fn (ProductCategory $record): string => route('products.category', $record))
                    ->openUrlInNewTab(),
                EditAction::make()->iconButton()->tooltip('Chỉnh sửa'),
                DeleteAction::make()->iconButton()->tooltip('Xóa'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
