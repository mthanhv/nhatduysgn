<ul class="nav-menu__grid">
    @foreach ($entries as $navBrand)
        <li>
            <a class="nav-menu__item" href="{{ lroute('products.brand', $navBrand) }}" @if (request()->route('brand')?->is($navBrand)) aria-current="page" @endif>
                <span class="nav-menu__logo">@if ($navBrand->logo)<img src="{{ thumb_url($navBrand->logo, 160) }}" alt="" loading="lazy">@endif</span>
                <span class="nav-menu__name">{{ $navBrand->name }}</span>
                <span class="tree__count">{{ $navBrand->products_count }}</span>
            </a>
        </li>
    @endforeach
</ul>
<a class="nav-menu__all" href="{{ lroute('products.index') }}#thuong-hieu">{{ __('Xem tất cả sản phẩm theo thương hiệu') }}</a>
