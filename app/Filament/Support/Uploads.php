<?php

namespace App\Filament\Support;

use Filament\Forms\Components\FileUpload;

/**
 * Upload fields shared by every resource. Files get random names on the public uploads disk and only
 * plain image/document types are accepted (no SVG/HTML, which could carry scripts).
 */
class Uploads
{
    /**
     * Phone photos are often 4–8 MB. The server's PHP upload_max_filesize must be above this (production: 12M).
     */
    public const MAX_IMAGE_KB = 10240;

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
            ->maxSize(self::MAX_IMAGE_KB)
            ->imageEditor()
            ->openable()
            ->helperText('JPG, PNG, WEBP hoặc GIF, tối đa 10 MB.');
    }

    /**
     * Several images shown as a grid of small square thumbnails (file name under each, full name on hover, remove
     * button top right; styled by .nd-gallery-upload in public/css/admin.css). Drag to reorder.
     */
    public static function gallery(string $name, string $directory, int $maxFiles): FileUpload
    {
        return self::image($name, $directory)
            ->multiple()
            ->reorderable()
            ->appendFiles()
            ->maxFiles($maxFiles)
            ->maxParallelUploads(4)
            ->imageEditor(false)
            ->panelLayout('grid')
            ->itemPanelAspectRatio(1)
            ->removeUploadedFileButtonPosition('right')
            ->uploadProgressIndicatorPosition('right')
            ->extraAttributes(['class' => 'nd-gallery-upload'])
            ->helperText("Chọn hoặc kéo-thả nhiều ảnh cùng lúc, kéo ảnh để sắp xếp. Tối đa {$maxFiles} ảnh; JPG, PNG, WEBP hoặc GIF, tối đa 10 MB mỗi ảnh.");
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
