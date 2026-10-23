<?php

namespace App\Filament\Resources\ContactMessages\Schemas;

use App\Models\ContactMessage;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ContactMessageInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make(fn (ContactMessage $record): string => $record->subject)
                    ->columnSpan(2)
                    ->schema([
                        TextEntry::make('topic')
                            ->label('Chủ đề')
                            ->badge()
                            ->formatStateUsing(fn (?string $state): string => ContactMessage::TOPICS[$state] ?? (string) $state),
                        TextEntry::make('message')->label('Nội dung')->prose()->extraAttributes(['style' => 'white-space: pre-line']),
                    ]),
                Section::make('Người gửi')
                    ->columnSpan(1)
                    ->schema([
                        TextEntry::make('name')->label('Họ tên'),
                        TextEntry::make('company')->label('Công ty')->placeholder('—'),
                        TextEntry::make('email')->label('Email')->copyable()->url(fn (ContactMessage $record): string => 'mailto:'.$record->email),
                        TextEntry::make('phone')->label('Điện thoại')->placeholder('—')->copyable()->url(fn (ContactMessage $record): ?string => $record->phone ? tel_href($record->phone) : null),
                        TextEntry::make('address')->label('Địa chỉ')->placeholder('—'),
                        TextEntry::make('created_at')->label('Gửi lúc')->dateTime('d/m/Y H:i'),
                        TextEntry::make('ip_address')->label('IP')->placeholder('—'),
                    ]),
            ]);
    }
}
