<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Models\Product;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // A hidden product has no public page (404), so the button only shows while it is published.
            Action::make('view_on_site')
                ->label('Xem trên website')
                ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                ->color('gray')
                ->url(fn (Product $record): string => route('products.show', $record))
                ->openUrlInNewTab()
                ->visible(fn (Product $record): bool => $record->is_published),
            DeleteAction::make(),
        ];
    }
}
