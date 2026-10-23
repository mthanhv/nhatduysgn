<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ContactMessages\ContactMessageResource;
use App\Filament\Resources\Posts\PostResource;
use App\Filament\Resources\Products\ProductResource;
use App\Models\ContactMessage;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class WelcomeBanner extends Widget
{
    protected static ?int $sort = 0;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.welcome-banner';

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $hour = (int) now()->format('G');

        return [
            'greeting' => match (true) {
                $hour < 11 => 'Chào buổi sáng',
                $hour < 14 => 'Chào buổi trưa',
                $hour < 18 => 'Chào buổi chiều',
                default => 'Chào buổi tối',
            },
            'name' => Auth::user()?->name,
            'today' => ucfirst(now()->locale('vi')->isoFormat('dddd, D [tháng] M, YYYY')),
            'unread' => ContactMessage::query()->unread()->count(),
            'links' => [
                'newProduct' => ProductResource::getUrl('create'),
                'newPost' => PostResource::getUrl('create'),
                'messages' => ContactMessageResource::getUrl('index'),
            ],
        ];
    }
}
