<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ContactMessages\ContactMessageResource;
use App\Filament\Resources\Posts\PostResource;
use App\Filament\Resources\ProductCategories\ProductCategoryResource;
use App\Filament\Resources\Products\ProductResource;
use App\Models\ContactMessage;
use App\Models\Post;
use App\Models\Product;
use App\Models\ProductCategory;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SiteStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $unread = ContactMessage::query()->unread()->count();
        $perDay = ContactMessage::query()
            ->where('created_at', '>=', now()->subDays(13)->startOfDay())
            ->get(['created_at'])
            ->countBy(fn (ContactMessage $message) => $message->created_at->toDateString());
        $chart = collect(range(13, 0))->map(fn (int $daysAgo) => $perDay[now()->subDays($daysAgo)->toDateString()] ?? 0)->all();

        return [
            Stat::make('Tin nhắn chưa đọc', $unread)
                ->icon(Heroicon::OutlinedInbox)
                ->description(array_sum($chart).' tin nhắn trong 14 ngày qua')
                ->descriptionIcon(Heroicon::ArrowTrendingUp)
                ->chart($chart)
                ->color($unread > 0 ? 'danger' : 'success')
                ->url(ContactMessageResource::getUrl('index')),
            Stat::make('Sản phẩm đang hiển thị', number_format(Product::query()->published()->count(), 0, ',', '.'))
                ->icon(Heroicon::OutlinedCube)
                ->description(Product::query()->where('is_published', false)->count().' sản phẩm đang ẩn')
                ->url(ProductResource::getUrl('index')),
            Stat::make('Nhóm sản phẩm', ProductCategory::query()->published()->count())
                ->icon(Heroicon::OutlinedFolder)
                ->description(ProductCategory::query()->published()->whereNull('parent_id')->count().' nhóm cấp 1')
                ->url(ProductCategoryResource::getUrl('index')),
            Stat::make('Bài viết', Post::query()->where('is_published', true)->count())
                ->icon(Heroicon::OutlinedNewspaper)
                ->description('Bài mới nhất: '.(Post::query()->published()->first()?->published_at?->format('d/m/Y') ?? '—'))
                ->url(PostResource::getUrl('index')),
        ];
    }
}
