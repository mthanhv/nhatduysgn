@extends('layouts.site')

@section('title', __('Sản phẩm'))
@section('description', __('Thiết bị, dụng cụ và vật tư bảo trì công nghiệp chính hãng: dụng cụ thủy lực ITH, vòng bi Schaeffler, bôi trơn Klüber, keo Loctite, thiết bị ống thép JBE…'))

@section('content')
    <section class="page-hero page-hero--grid">
        <div class="container">
            <x-breadcrumb :items="[__('Sản phẩm') => null]" />
            <h1>{{ __('Sản phẩm') }}</h1>
            <p>{{ __('Thiết bị, dụng cụ và vật tư bảo trì chính hãng từ :brands thương hiệu quốc tế, chia theo :groups nhóm ứng dụng.', ['brands' => $brands->count(), 'groups' => $categories->count()]) }}</p>
            <form class="search-form search-form--hero" action="{{ lroute('products.index') }}" method="get" role="search" style="max-width:720px;margin-top:6px">
                <label for="product-search" class="sr-only">{{ __('Tìm sản phẩm') }}</label>
                <input id="product-search" type="search" name="q" placeholder="{{ __('Tìm theo tên, mã sản phẩm hoặc thương hiệu — vd. Loctite 243, CX-5') }}">
                <button type="submit"><x-ui-icon name="search" /> {{ __('Tìm') }}</button>
            </form>
        </div>
    </section>

    @if ($featured->isNotEmpty())
        <section class="section" style="padding-top:56px">
            <div class="container">
                <div class="section-head"><h2>{{ __('Nhóm sản phẩm chủ lực') }}</h2></div>
                <div class="grid grid-280">
                    @foreach ($featured as $category)
                        <a class="card product-card" href="{{ lroute('products.category', $category) }}">
                            <span class="thumb">
                                @if ($cover = $category->coverImage())
                                    <img src="{{ thumb_url($cover, 640) }}" alt="{{ $category->name }}" loading="lazy">
                                @else
                                    <span class="icon-tile" style="width:72px;height:72px"><x-ui-icon :name="$category->icon ?: 'box'" :size="36" :stroke="1.6" /></span>
                                @endif
                            </span>
                            <span class="body">
                                <h3 style="font-size:18px">{{ $category->name }}</h3>
                                <span class="count" style="margin-top:0">{{ __(':count sản phẩm', ['count' => $category->total_products]) }}</span>
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section class="section">
        <div class="container">
            <div class="section-head"><h2>{{ __('Tất cả nhóm sản phẩm') }}</h2></div>
            <div class="card category-index">
                @foreach ($categories as $category)
                    <div class="category-index__item">
                        <a class="category-index__head" href="{{ lroute('products.category', $category) }}">
                            <span class="icon-tile"><x-ui-icon :name="$category->icon ?: 'box'" :size="22" :stroke="1.8" /></span>
                            <strong>{{ $category->name }}</strong>
                            <span class="mono muted" style="font-size:13px">{{ $category->total_products }}</span>
                        </a>
                        @if ($category->children->isNotEmpty())
                            <ul class="tree__sub category-index__sub">
                                @foreach ($category->children as $child)
                                    <li>
                                        <a class="tree__sublink" href="{{ lroute('products.category', $child) }}">
                                            <span class="tree__name">{{ $child->name }}</span>
                                            <span class="tree__count">{{ $child->products_count }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    @if ($brands->isNotEmpty())
        <section id="thuong-hieu" class="section" style="padding-bottom:72px">
            <div class="container">
                <div class="section-head"><h2>{{ __('Theo thương hiệu') }}</h2></div>
                <div class="grid grid-brands">
                    @foreach ($brands as $brand)
                        <a class="card brand-tile" href="{{ lroute('products.brand', $brand) }}">
                            @if ($brand->logo)<img src="{{ thumb_url($brand->logo, 320) }}" alt="{{ $brand->name }}" loading="lazy">@else{{ $brand->name }}@endif
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @include('partials.cta-band', ['title' => __('Không tìm thấy sản phẩm cần?'), 'text' => __('Gửi tên hoặc thông số, kỹ sư :name sẽ tư vấn giúp.', ['name' => setting('brand_name')])])
@endsection
