<?php

/*
|--------------------------------------------------------------------------
| Legacy site (CakePHP, nhatduysgn.com until 2026)
|--------------------------------------------------------------------------
|
| Used by `php artisan legacy:import` and by the fallback router that sends
| old URLs to their new location with 301 redirects.
|
*/

return [

    // Absolute path of the old site's app/webroot folder (contains files/ and media/).
    'webroot' => env('LEGACY_WEBROOT'),

    // Where copies of the old /files and /media trees are published, relative to the uploads disk.
    'uploads_prefix' => 'legacy',

    // Old article IDs that were fixed pages rather than news posts.
    'article_pages' => [
        1 => '/gioi-thieu',
        64 => '/dich-vu',
        65 => '/gioi-thieu#chung-nhan',
    ],

    // Old article categories imported as news posts (2 = News, 4 = Project, 5 = Event).
    'news_category_ids' => [2, 4, 5],

    // Old brand/supplier names that should merge into one new brand (normalised, uppercase).
    'brand_aliases' => [
        'ITH BOLTING TECHNOLOGY' => 'ITH',
        'KLUEBER' => 'KLÜBER',
        'KLBER' => 'KLÜBER',
        'FAG' => 'SCHAEFFLER',
        'INGERSOLL-RAND' => 'INGERSOLL RAND',
    ],

    // Brands highlighted on the homepage after import (normalised names).
    'featured_brands' => ['ITH', 'SCHAEFFLER', 'KLÜBER', 'LOCTITE', 'JBE', 'LPS', 'KEMET', 'SCIAPS'],

    // Old top-level category IDs shown as "featured" groups, and the icon each group gets.
    'featured_category_ids' => [85, 171, 164, 98],
    'category_icons' => [
        94 => 'tube', 100 => 'valve', 67 => 'glue', 171 => 'ring', 88 => 'can', 161 => 'clamp',
        98 => 'pipe', 164 => 'drop', 85 => 'wrench', 159 => 'mark', 187 => 'heat', 130 => 'shim',
        101 => 'bolt', 95 => 'hoist', 96 => 'gun', 162 => 'scan', 163 => 'wrench',
    ],

    // Only these extensions are ever copied from the old upload folders.
    'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'zip', 'rar', 'mp4'],

    'site_hosts' => ['nhatduysgn.com', 'www.nhatduysgn.com'],

];
