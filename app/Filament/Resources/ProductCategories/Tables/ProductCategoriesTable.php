<?php

namespace App\Filament\Resources\ProductCategories\Tables;

use App\Filament\Support\SortOrder;
use App\Models\ProductCategory;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Enums\FontWeight;
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
            // sort_order follows the tree (ProductCategory::renumberAsTree), so sub-groups sit right under their group.
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->afterReordering(fn () => ProductCategory::renumberAsTree())
            ->description('Thứ tự hiển thị trên website: số nhỏ hiện trước. Sửa số trong cột "Thứ tự" hoặc bấm nút sắp xếp rồi kéo-thả. Nhóm cấp 1 di chuyển kèm các nhóm con; nhóm con chỉ đổi thứ tự trong nhóm cha của nó. Sau mỗi thay đổi, số được đánh lại liên tục từ 1.')
            ->columns([
                TextColumn::make('name')
                    ->label('Tên nhóm')
                    ->searchable()
                    ->wrap()
                    ->formatStateUsing(fn (string $state, ProductCategory $record): string => $record->parent_id ? '↳ '.$state : $state)
                    ->weight(fn (ProductCategory $record): ?FontWeight => $record->parent_id ? null : FontWeight::Bold)
                    ->extraAttributes(fn (ProductCategory $record): array => $record->parent_id ? ['style' => 'padding-inline-start: 1.75rem'] : []),
                TextColumn::make('products_count')->label('Sản phẩm')->counts('products')->sortable(),
                // A typed number moves the group among its siblings; renumbering keeps sub-groups under their group.
                SortOrder::column()->afterStateUpdated(fn () => ProductCategory::renumberAsTree()),
                ToggleColumn::make('is_published')->label('Hiển thị'),
                IconColumn::make('is_featured')->label('Chủ lực')->boolean()->trueIcon(Heroicon::Star)->falseIcon(Heroicon::OutlinedStar)->trueColor('warning')->falseColor('gray'),
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
