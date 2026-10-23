<x-filament-widgets::widget>
    <div class="nd-welcome">
        <div class="nd-welcome__text">
            <p class="nd-welcome__date">{{ $today }}</p>
            <h2 class="nd-welcome__title">{{ $greeting }}{{ $name ? ', '.$name : '' }}</h2>
            <p class="nd-welcome__lead">
                @if ($unread > 0)
                    Bạn có <a href="{{ $links['messages'] }}"><strong>{{ $unread }} tin nhắn liên hệ chưa đọc</strong></a> từ khách hàng.
                @else
                    Không có tin nhắn liên hệ mới. Mọi thứ đã được xử lý.
                @endif
            </p>
        </div>
        <div class="nd-welcome__actions">
            <x-filament::button tag="a" :href="$links['newProduct']" icon="heroicon-m-plus" size="lg">Thêm sản phẩm</x-filament::button>
            <x-filament::button tag="a" :href="$links['newPost']" icon="heroicon-m-pencil-square" size="lg" color="gray">Viết tin tức</x-filament::button>
            <x-filament::button tag="a" :href="url('/')" target="_blank" icon="heroicon-m-arrow-top-right-on-square" size="lg" color="gray">Xem website</x-filament::button>
        </div>
    </div>
</x-filament-widgets::widget>
