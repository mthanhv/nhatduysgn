<?php

namespace App\Filament\Resources\Services\Tables;

use App\Filament\Support\SortOrder;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class ServicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->description(SortOrder::HELP)
            ->columns([
                ImageColumn::make('image')->label('Ảnh')->disk('public')->square()->imageSize(48),
                TextColumn::make('title')->label('Dịch vụ')->searchable()->wrap()->description(fn ($record) => $record->summary),
                SortOrder::column(),
                ToggleColumn::make('is_published')->label('Hiển thị'),
            ])
            ->recordActions([EditAction::make()->iconButton()->tooltip('Chỉnh sửa'), DeleteAction::make()->iconButton()->tooltip('Xóa')])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
