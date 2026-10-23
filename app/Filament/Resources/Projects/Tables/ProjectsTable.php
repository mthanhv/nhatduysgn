<?php

namespace App\Filament\Resources\Projects\Tables;

use App\Models\Project;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ProjectsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                ImageColumn::make('cover')->label('Ảnh')->disk('public')->imageSize(56),
                TextColumn::make('title')->label('Dự án')->searchable()->wrap(),
                TextColumn::make('industry')->label('Ngành')->badge()->sortable(),
                TextColumn::make('client')->label('Khách hàng')->searchable()->toggleable(),
                TextColumn::make('year')->label('Năm')->sortable()->toggleable(),
                ToggleColumn::make('is_published')->label('Hiển thị'),
            ])
            ->filters([
                SelectFilter::make('industry')->label('Ngành')->options(fn () => Project::query()->whereNotNull('industry')->distinct()->pluck('industry', 'industry')->all()),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
