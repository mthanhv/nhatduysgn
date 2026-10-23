<?php

namespace App\Filament\Resources\Posts\Tables;

use App\Models\Post;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class PostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('published_at', 'desc')
            ->columns([
                ImageColumn::make('cover')->label('Ảnh')->disk('public')->imageSize(56),
                TextColumn::make('title')->label('Tiêu đề')->searchable()->wrap(),
                TextColumn::make('published_at')->label('Ngày đăng')->dateTime('d/m/Y')->sortable(),
                ToggleColumn::make('is_published')->label('Hiển thị'),
                TextColumn::make('view_count')->label('Lượt xem')->numeric()->sortable()->toggleable(),
            ])
            ->recordActions([
                Action::make('view_on_site')
                    ->label('Xem trên website')
                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->iconButton()
                    ->color('gray')
                    ->tooltip('Xem trên website')
                    ->url(fn (Post $record): string => route('posts.show', $record))
                    ->openUrlInNewTab(),
                EditAction::make()->iconButton()->tooltip('Chỉnh sửa'),
                DeleteAction::make()->iconButton()->tooltip('Xóa'),
            ])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
