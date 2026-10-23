<?php

namespace App\Filament\Resources\ContactMessages\Tables;

use App\Models\ContactMessage;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ContactMessagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->recordClasses(fn (ContactMessage $record): ?string => $record->read_at ? null : 'bg-primary-50/50')
            ->columns([
                TextColumn::make('subject')
                    ->label('Tiêu đề')
                    ->searchable(['subject', 'message'])
                    ->wrap()
                    ->weight(fn (ContactMessage $record): ?FontWeight => $record->read_at ? null : FontWeight::Bold)
                    ->description(fn (ContactMessage $record): string => $record->name.($record->company ? ' · '.$record->company : '')),
                TextColumn::make('topic')
                    ->label('Chủ đề')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => ContactMessage::TOPICS[$state] ?? (string) $state)
                    ->toggleable(),
                TextColumn::make('email')->label('Email')->searchable()->toggleable(),
                TextColumn::make('phone')->label('Điện thoại')->searchable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')->label('Gửi lúc')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->filters([
                TernaryFilter::make('read_at')
                    ->label('Trạng thái')
                    ->placeholder('Tất cả')
                    ->trueLabel('Đã đọc')
                    ->falseLabel('Chưa đọc')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('read_at'),
                        false: fn (Builder $query) => $query->whereNull('read_at'),
                    ),
                SelectFilter::make('topic')->label('Chủ đề')->options(ContactMessage::TOPICS),
            ])
            ->recordActions([
                ViewAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('mark_read')
                        ->label('Đánh dấu đã đọc')
                        ->icon(Heroicon::OutlinedEnvelopeOpen)
                        ->action(fn (Collection $records) => ContactMessage::query()->whereKey($records->modelKeys())->whereNull('read_at')->update(['read_at' => now()]))
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('mark_unread')
                        ->label('Đánh dấu chưa đọc')
                        ->icon(Heroicon::OutlinedEnvelope)
                        ->action(fn (Collection $records) => ContactMessage::query()->whereKey($records->modelKeys())->update(['read_at' => null]))
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
