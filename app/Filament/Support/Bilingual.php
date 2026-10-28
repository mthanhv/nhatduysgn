<?php

namespace App\Filament\Support;

use Closure;
use Filament\Forms\Components\Field;
use Filament\Schemas\Components\Grid;

/**
 * A Vietnamese input and its English counterpart shown together, so editors translate while looking at the original.
 * Short fields sit side by side; long rich text and narrow sidebars stack English under Vietnamese.
 * An empty English input shows the Vietnamese text on the English site.
 */
class Bilingual
{
    public const FALLBACK = 'Để trống: dùng bản tiếng Việt';

    /** What an empty English SEO text does (see HasTranslations::ownValue). */
    public const GENERATED = 'Để trống: tự viết từ nội dung tiếng Anh';

    /**
     * @param  Closure(string $name, bool $english): Field  $field  builds the input for a state path; required rules
     *                                                              belong on the Vietnamese one only
     */
    public static function pair(string $vietnamese, string $english, Closure $field, bool $stacked = false, string $emptyEnglish = self::FALLBACK): Grid
    {
        $englishInput = $field($english, true)->hint('English')->hintColor('primary');

        // Repeaters have no placeholder; text inputs say what an empty English field does.
        if (method_exists($englishInput, 'placeholder')) {
            $englishInput->placeholder($emptyEnglish);
        }

        return Grid::make($stacked ? 1 : ['default' => 1, 'lg' => 2])
            ->schema([$field($vietnamese, false)->hint('Tiếng Việt'), $englishInput])
            ->columnSpanFull();
    }

    /**
     * A content field and its English version in the record's `translations` column (see HasTranslations).
     *
     * @param  Closure(string $name, bool $english): Field  $field
     */
    public static function translated(string $field, Closure $input, bool $stacked = false): Grid
    {
        $emptyEnglish = in_array($field, ['meta_title', 'meta_description'], true) ? self::GENERATED : self::FALLBACK;

        return self::pair($field, "translations.en.{$field}", $input, $stacked, $emptyEnglish);
    }
}
