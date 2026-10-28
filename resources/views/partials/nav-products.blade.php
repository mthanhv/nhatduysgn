@php($routeCategory = request()->route('category'))
<ul class="nav-mega">
    @foreach ($entries as $navCategory)
        <li class="nav-mega__group">
            <a class="nav-menu__item nav-mega__root" href="{{ lroute('products.category', $navCategory) }}" @if ($routeCategory?->is($navCategory)) aria-current="page" @endif>
                <span class="tree__icon"><x-ui-icon :name="$navCategory->icon ?: 'box'" :size="18" :stroke="1.8" /></span>
                <span class="nav-menu__name">{{ $navCategory->name }}</span>
                <span class="tree__count">{{ $navCategory->total_products }}</span>
            </a>
            @if ($navCategory->children->isNotEmpty())
                <ul class="nav-mega__children">
                    @foreach ($navCategory->children as $navChild)
                        <li><a href="{{ lroute('products.category', $navChild) }}" @if ($routeCategory?->is($navChild)) aria-current="page" @endif>{{ $navChild->name }}</a></li>
                    @endforeach
                </ul>
            @endif
        </li>
    @endforeach
</ul>
<a class="nav-menu__all" href="{{ lroute('products.index') }}">{{ __('Xem tất cả sản phẩm') }}</a>
