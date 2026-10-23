<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\ManageSiteSettings;
use App\Filament\Resources\Brands\BrandResource;
use App\Filament\Resources\Certificates\CertificateResource;
use App\Filament\Resources\ProductCategories\ProductCategoryResource;
use App\Filament\Resources\Products\ProductResource;
use App\Models\Brand;
use App\Models\Certificate;
use App\Models\Product;
use App\Models\ProductCategory;
use Filament\Widgets\Widget;

/**
 * Content gaps editors should close (missing images, descriptions, logos, settings).
 */
class ContentChecklist extends Widget
{
    protected static ?int $sort = 2;

    protected string $view = 'filament.widgets.content-checklist';

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $shoutingNames = Product::query()->published()->pluck('name')
            ->filter(fn (string $name) => preg_match('/\p{Ll}/u', $name) === 0 && preg_match('/\p{Lu}.*\p{Lu}.*\p{Lu}/u', $name) === 1)
            ->count();

        $items = [
            ['label' => 'Sản phẩm chưa có ảnh', 'count' => Product::query()->published()->whereNull('image')->count(), 'url' => ProductResource::getUrl('index')],
            ['label' => 'Sản phẩm chưa có mô tả', 'count' => Product::query()->published()->whereNull('description')->count(), 'url' => ProductResource::getUrl('index')],
            ['label' => 'Tên sản phẩm VIẾT HOA toàn bộ', 'count' => $shoutingNames, 'url' => ProductResource::getUrl('index')],
            ['label' => 'Nhóm sản phẩm chưa có mô tả ngắn', 'count' => ProductCategory::query()->published()->whereNull('summary')->count(), 'url' => ProductCategoryResource::getUrl('index')],
            ['label' => 'Thương hiệu chưa có logo', 'count' => Brand::query()->published()->whereNull('logo')->count(), 'url' => BrandResource::getUrl('index')],
            ['label' => 'Chưa có chứng nhận nào', 'count' => Certificate::query()->published()->exists() ? 0 : 1, 'url' => CertificateResource::getUrl('index'), 'flag' => true],
            ['label' => 'Chưa nhập số Zalo', 'count' => blank(setting('zalo')) ? 1 : 0, 'url' => ManageSiteSettings::getUrl(), 'flag' => true],
            ['label' => 'Chưa nhúng bản đồ Google Maps', 'count' => blank(setting('map_embed_url')) ? 1 : 0, 'url' => ManageSiteSettings::getUrl(), 'flag' => true],
        ];

        return [
            'items' => $items,
            'done' => collect($items)->where('count', 0)->count(),
            'total' => count($items),
        ];
    }
}
