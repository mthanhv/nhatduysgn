<?php

namespace App\Services;

use App\Models\Product;

/**
 * Clean-up rules for product data from the old sites, applied on import and by `catalog:tidy` for data already
 * in the database:
 * - ALL-CAPS names in normal case ("KEO KHÓA REN LOCTITE 243" → "Keo khóa ren LOCTITE 243");
 * - series codes cut at the hyphen ("DRS-" → "DRS") and codes that are only the brand name removed;
 * - "download the technical file" sentences removed from products that have no file to download.
 */
class ProductTidier
{
    private const DOWNLOAD_PHRASES = '(tải\s+tài\s+liệu|file\s+đính\s+kèm|tệp\s+đính\s+kèm|tài\s+liệu\s+đính\s+kèm)';

    public static function name(string $name): string
    {
        return Seo::readableCase($name);
    }

    public static function code(?string $code, ?string $brand = null): ?string
    {
        $code = trim((string) preg_replace('/^[\s\-–_.]+|[\s\-–_.]+$/u', '', (string) $code));

        if ($code === '' || ($brand !== null && mb_strtoupper($code) === mb_strtoupper($brand))) {
            return null;
        }

        return $code;
    }

    /**
     * Removes paragraphs/headings/list items that only point to an attachment, when there is none.
     */
    public static function description(?string $html, bool $hasDocuments): ?string
    {
        if ($hasDocuments || blank($html)) {
            return $html;
        }

        $cleaned = preg_replace('#<(p|h[1-6]|li|div)\b[^>]*>(?:(?!</\1>).)*'.self::DOWNLOAD_PHRASES.'(?:(?!</\1>).)*</\1>\s*#isu', '', $html);

        return $cleaned === null ? $html : (trim($cleaned) === '' ? null : $cleaned);
    }

    /**
     * Applies every rule to a product; returns the changed attributes (nothing is saved).
     *
     * @return array<string, mixed>
     */
    public static function changes(Product $product): array
    {
        $hasDocuments = collect($product->documents ?? [])->contains(fn ($document) => filled($document['file'] ?? null));

        $tidy = [
            'name' => self::name($product->name),
            'code' => self::code($product->code, $product->brand?->name),
            'description' => self::description($product->description, $hasDocuments),
            'specifications' => self::description($product->specifications, $hasDocuments),
        ];

        return array_filter($tidy, fn ($value, string $field) => $value !== $product->{$field}, ARRAY_FILTER_USE_BOTH);
    }
}
