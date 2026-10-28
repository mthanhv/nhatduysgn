@extends('layouts.site')
@if (($category ?? $brand ?? null)?->isTranslatedInto('en') === false)
    @section('untranslated', '1')
@endif

@section('title', $title)
@php
    $listingDescription = match (true) {
        isset($category) => \App\Services\Seo::categoryDescription($category),
        isset($brand) => \App\Services\Seo::brandDescription($brand),
        default => __('Kết quả tìm kiếm sản phẩm tại :company.', ['company' => setting('company_name')]),
    };
@endphp
@section('description', $listingDescription)
@isset($term)
    {{-- Search results are not indexed (endless thin pages); links on them are still followed. --}}
    @section('robots', 'noindex, follow')
@endisset

@push('head')
    @unless (isset($term))
        <x-json-ld :data="\App\Services\Seo::productList($title, $listingDescription, $products)" />
    @endunless
@endpush

@section('content')
    @php
        $crumbs = [__('Sản phẩm') => lroute('products.index')];
        if (isset($category)) {
            foreach ($category->ancestors() as $ancestor) {
                $crumbs[$ancestor->name] = lroute('products.category', $ancestor);
            }
            $crumbs[$category->name] = null;
        } elseif (isset($brand)) {
            $crumbs[$brand->name] = null;
        } else {
            $crumbs[__('Tìm kiếm')] = null;
        }
        $activeRootId = isset($category) ? ($category->ancestors()->first()?->id ?? $category->id) : null;
    @endphp

    <section class="page-hero page-hero--grid catalog-hero">
        <div class="container">
            <div class="text">
                <x-breadcrumb :items="$crumbs" />
                @isset($brand)<span class="eyebrow eyebrow--light">{{ $brand->country }}</span>@endisset
                <h1>{{ $title }}</h1>
                @if (isset($category) && $category->summary)
                    <p>{{ $category->summary }}</p>
                @elseif (isset($brand) && $brand->description)
                    <p>{{ $brand->description }}</p>
                @endif
                <div class="hero-actions">
                    <a class="btn btn-ghost-light" href="{{ lroute('contact', ['chu-de' => 'product', 'tieu-de' => __('Tư vấn').': '.$title]) }}">{{ __('Liên hệ tư vấn') }}</a>
                </div>
            </div>
            @if ((isset($category) && $category->image) || (isset($brand) && $brand->logo))
                <div class="media"><img src="{{ thumb_url($category->image ?? $brand->logo, 640) }}" alt=""></div>
            @endif
        </div>
    </section>

    <div class="container catalog-layout">
        <aside aria-label="{{ __('Danh mục sản phẩm') }}">
            {{-- Open by default (works without JS); site.js folds it on phones so products come first. --}}
            <details class="card tree" data-tree open>
                <summary class="tree__head">
                    <span class="tree__title"><x-ui-icon name="box" :size="18" :stroke="1.9" /> {{ __('Danh mục sản phẩm') }}</span>
                    <x-ui-icon name="chevron-right" :size="18" class="tree__toggle" />
                </summary>
                <nav aria-label="{{ __('Nhóm sản phẩm') }}">
                    <ul class="tree__list">
                        @foreach ($tree as $root)
                            @php $isOpen = $activeRootId === $root->id; @endphp
                            <li @class(['tree__item', 'is-open' => $isOpen])>
                                <a href="{{ lroute('products.category', $root) }}" @class(['tree__link', 'is-active' => $currentCategory?->id === $root->id]) @if ($currentCategory?->id === $root->id) aria-current="page" @endif>
                                    <span class="tree__icon"><x-ui-icon :name="$root->icon ?: 'box'" :size="18" :stroke="1.8" /></span>
                                    <span class="tree__name">{{ $root->name }}</span>
                                    <span class="tree__count">{{ $root->total_products }}</span>
                                </a>
                                @if ($isOpen && $root->children->isNotEmpty())
                                    <ul class="tree__sub">
                                        @foreach ($root->children as $child)
                                            <li>
                                                <a href="{{ lroute('products.category', $child) }}" @class(['tree__sublink', 'is-active' => $currentCategory?->id === $child->id]) @if ($currentCategory?->id === $child->id) aria-current="page" @endif>
                                                    <span class="tree__name">{{ $child->name }}</span>
                                                    <span class="tree__count">{{ $child->products_count }}</span>
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                    <a class="tree__all" href="{{ lroute('products.index') }}">{{ __('Xem tất cả nhóm sản phẩm') }} <x-ui-icon name="arrow-right" :size="15" /></a>
                </nav>
            </details>
            <div class="card" style="padding:20px;display:flex;flex-direction:column;gap:10px">
                <span class="icon-tile"><x-ui-icon name="phone" :size="22" :stroke="1.9" /></span>
                <strong style="font-size:16px">{{ __('Cần tư vấn chọn thiết bị?') }}</strong>
                <span class="muted" style="font-size:14px">{{ __('Cho chúng tôi biết thông số và điều kiện vận hành.') }}</span>
                <a href="{{ tel_href(setting('phone')) }}" style="font-size:18px;font-weight:800">{{ setting('phone') }}</a>
                <a class="link-more" href="{{ lroute('contact') }}">{{ __('Gửi liên hệ') }} →</a>
            </div>
        </aside>

        <section style="flex:999 1 600px;min-width:0;display:flex;flex-direction:column;gap:20px" aria-labelledby="results-heading">
            <div class="toolbar">
                <div>
                    <h2 id="results-heading">{{ isset($term) ? __('Kết quả') : __('Sản phẩm') }}</h2>
                    <span class="muted" style="font-size:14px">{{ __(':count sản phẩm', ['count' => $products->total()]) }}</span>
                </div>
                <form class="search-form" action="{{ lroute('products.index') }}" method="get" role="search">
                    <label for="listing-search" class="sr-only">{{ __('Tìm sản phẩm') }}</label>
                    <input id="listing-search" type="search" name="q" value="{{ $term ?? '' }}" placeholder="{{ __('Tìm theo tên hoặc mã') }}" style="height:44px;width:240px;max-width:100%">
                    <button type="submit" style="height:44px" aria-label="{{ __('Tìm') }}"><x-ui-icon name="search" /></button>
                </form>
            </div>

            @if ($products->isEmpty())
                <div class="card empty">
                    <p>{{ __('Không tìm thấy sản phẩm phù hợp.') }}</p>
                    <p style="margin-top:12px"><a class="link-more" href="{{ lroute('contact', ['chu-de' => 'product', 'tieu-de' => isset($term) ? __('Tìm sản phẩm').': '.$term : '']) }}">{{ __('Gửi yêu cầu để kỹ sư tư vấn') }} →</a></p>
                </div>
            @else
                <div class="grid grid-250">
                    @foreach ($products as $product)
                        <x-product-card :product="$product" />
                    @endforeach
                </div>
                {{ $products->links('partials.pagination') }}
            @endif
        </section>
    </div>
@endsection
