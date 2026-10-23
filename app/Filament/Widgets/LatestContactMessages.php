<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ContactMessages\ContactMessageResource;
use App\Models\ContactMessage;
use Filament\Actions\Action;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class LatestContactMessages extends TableWidget
{
    protected static ?int $sort = 3;

    protected static ?string $heading = 'Tin nhắn liên hệ mới nhất';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => ContactMessage::query()->latest())
            ->paginated([5])
            ->columns([
                TextColumn::make('subject')
                    ->label('Tiêu đề')
                    ->wrap()
                    ->weight(fn (ContactMessage $record): ?FontWeight => $record->read_at ? null : FontWeight::Bold)
                    ->description(fn (ContactMessage $record): string => $record->name.' · '.$record->email),
                TextColumn::make('created_at')->label('Gửi lúc')->since(),
            ])
            ->recordUrl(fn (ContactMessage $record): string => ContactMessageResource::getUrl('view', ['record' => $record]))
            ->headerActions([
                Action::make('all')->label('Xem tất cả')->url(ContactMessageResource::getUrl('index'))->link(),
            ]);
    }
}
