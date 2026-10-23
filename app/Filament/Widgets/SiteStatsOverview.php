<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ContactMessages\ContactMessageResource;
use App\Filament\Resources\Products\ProductResource;
use App\Models\ContactMessage;
use App\Models\Post;
use App\Models\Product;
use App\Models\ProductCategory;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SiteStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $unread = ContactMessage::query()->unread()->count();
        $lastWeek = ContactMessage::query()->where('created_at', '>=', now()->subDays(7))->count();

        return [
            Stat::make('Tin nhắn chưa đọc', $unread)
                ->description($lastWeek.' tin nhắn trong 7 ngày qua')
                ->color($unread > 0 ? 'danger' : 'success')
                ->url(ContactMessageResource::getUrl('index')),
            Stat::make('Sản phẩm đang hiển thị', Product::query()->published()->count())
                ->description(Product::query()->where('is_published', false)->count().' sản phẩm đang ẩn')
                ->url(ProductResource::getUrl('index')),
            Stat::make('Nhóm sản phẩm', ProductCategory::query()->published()->count()),
            Stat::make('Bài viết', Post::query()->where('is_published', true)->count()),
        ];
    }
}
