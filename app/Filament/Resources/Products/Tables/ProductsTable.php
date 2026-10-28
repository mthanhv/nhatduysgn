<?php

namespace App\Filament\Resources\Products\Tables;

use App\Filament\Support\SortOrder;
use App\Models\Product;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Contracts\HasTable;
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
            ->defaultPaginationPageOption(25)
            // Order on the website is per group, so dragging is offered only once the list is filtered to one group.
            ->reorderable('sort_order', fn (HasTable $livewire): bool => filled($livewire->getTableFilterState('product_category_id')['value'] ?? null))
            ->description('Thứ tự sản phẩm trong từng nhóm trên website: số nhỏ hiện trước. Sửa số trong cột "Thứ tự", hoặc lọc theo Nhóm rồi bấm nút sắp xếp để kéo-thả.')
            ->columns([
                ImageColumn::make('image')->label('Ảnh')->disk('public')->square()->imageSize(56),
                TextColumn::make('name')
                    ->label('Tên sản phẩm')
                    ->searchable(['name', 'code'])
                    ->sortable()
                    ->weight(FontWeight::Medium)
                    ->wrap()
                    ->lineClamp(2)
                    ->tooltip(fn (Product $record): string => $record->name)
                    ->description(fn (Product $record): ?string => $record->code),
                TextColumn::make('category.name')
                    ->label('Nhóm')
                    ->sortable()
                    ->toggleable()
                    ->limit(26)
                    ->tooltip(fn (Product $record): ?string => $record->category?->name)
                    ->color('gray'),
                TextColumn::make('brand.name')->label('Thương hiệu')->badge()->color('gray')->sortable()->toggleable(),
                SortOrder::column(),
                ToggleColumn::make('is_published')->label('Hiển thị'),
                IconColumn::make('is_featured')->label('Tiêu biểu')->boolean()->trueIcon(Heroicon::Star)->falseIcon(Heroicon::OutlinedStar)->trueColor('warning')->falseColor('gray')->toggleable(),
                TextColumn::make('view_count')->label('Lượt xem')->numeric()->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->label('Cập nhật')->since()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('product_category_id')->label('Nhóm')->relationship('category', 'name')->searchable()->preload(),
                SelectFilter::make('brand_id')->label('Thương hiệu')->relationship('brand', 'name')->searchable()->preload(),
                TernaryFilter::make('is_published')->label('Hiển thị'),
                TernaryFilter::make('is_featured')->label('Tiêu biểu'),
            ])
            ->recordActions([
                Action::make('view_on_site')
                    ->label('Xem trên website')
                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->iconButton()
                    ->color('gray')
                    ->tooltip('Xem trên website')
                    ->url(fn (Product $record): string => route('products.show', $record))
                    ->openUrlInNewTab()
                    ->visible(fn (Product $record): bool => $record->is_published),
                EditAction::make()->iconButton()->tooltip('Chỉnh sửa'),
                DeleteAction::make()->iconButton()->tooltip('Xóa'),
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
