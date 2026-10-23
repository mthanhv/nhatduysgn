<?php

namespace App\Filament\Support;

use Filament\Forms\Components\FileUpload;

/**
 * Upload fields shared by every resource. Files get random names on the public uploads disk and only
 * plain image/document types are accepted (no SVG/HTML, which could carry scripts).
 */
class Uploads
{
    public const IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

    public const DOCUMENT_TYPES = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/zip',
    ];

    public static function image(string $name, string $directory): FileUpload
    {
        return FileUpload::make($name)
            ->image()
            ->disk('public')
            ->directory($directory)
            ->visibility('public')
            ->acceptedFileTypes(self::IMAGE_TYPES)
            ->maxSize(5120)
            ->imageEditor()
            ->openable()
            ->helperText('JPG, PNG, WEBP hoặc GIF, tối đa 5 MB.');
    }

    public static function document(string $name, string $directory): FileUpload
    {
        return FileUpload::make($name)
            ->disk('public')
            ->directory($directory)
            ->visibility('public')
            ->acceptedFileTypes([...self::DOCUMENT_TYPES, ...self::IMAGE_TYPES])
            ->maxSize(20480)
            ->openable()
            ->downloadable()
            ->helperText('PDF, Word, Excel, ZIP hoặc ảnh, tối đa 20 MB.');
    }
}
