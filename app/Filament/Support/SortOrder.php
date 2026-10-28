<?php

namespace App\Filament\Support;

use Filament\Tables\Columns\TextInputColumn;

/**
 * The editable "Thứ tự" column shared by every list ordered by `sort_order`: editors type a number in the table
 * (smaller shows first on the website) or drag rows with the reorder button.
 */
class SortOrder
{
    public const HELP = 'Thứ tự hiển thị trên website: số nhỏ hiện trước. Sửa số ngay trong cột "Thứ tự", hoặc bấm nút sắp xếp rồi kéo-thả.';

    public static function column(): TextInputColumn
    {
        return TextInputColumn::make('sort_order')
            ->label('Thứ tự')
            ->type('number')
            ->rules(['required', 'integer', 'min:0', 'max:999999'])
            ->sortable()
            // Shrink the column to the input instead of sharing the table width with text columns.
            ->extraInputAttributes(['style' => 'width: 5.5rem'])
            ->extraCellAttributes(['style' => 'width: 1%']);
    }
}
