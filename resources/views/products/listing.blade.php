@extends('layouts.site')

@section('title', $title)
@isset($category)
    @section('description', $category->meta_description ?: ($category->summary ?: strip_tags((string) $category->description)))
@endisset

@section('content')
    @php
        $crumbs = ['Sản phẩm' => route('products.index')];
        if (isset($category)) {
            foreach ($category->ancestors() as $ancestor) {
                $crumbs[$ancestor->name] = route('products.category', $ancestor);
            }
            $crumbs[$category->name] = null;
        } elseif (isset($brand)) {
            $crumbs[$brand->name] = null;
        } else {
            $crumbs['Tìm kiếm'] = null;
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
                    <a class="btn btn-ghost-light" href="{{ route('contact', ['chu-de' => 'product', 'tieu-de' => 'Tư vấn: '.$title]) }}">Liên hệ tư vấn</a>
                </div>
            </div>
            @if ((isset($category) && $category->image) || (isset($brand) && $brand->logo))
                <div class="media"><img src="{{ media_url($category->image ?? $brand->logo) }}" alt=""></div>
            @endif
        </div>
    </section>

    <div class="container catalog-layout">
        <aside aria-label="Danh mục sản phẩm">
            <nav class="card tree">
                <h2>Danh mục sản phẩm</h2>
                <ul>
                    @foreach ($tree as $root)
                        <li>
                            <a href="{{ route('products.category', $root) }}" @class(['is-active' => $currentCategory?->id === $root->id]) @if ($currentCategory?->id === $root->id) aria-current="page" @endif>{{ $root->name }}</a>
                            @if ($activeRootId === $root->id && $root->children->isNotEmpty())
                                <ul>
                                    @foreach ($root->children as $child)
                                        <li><a href="{{ route('products.category', $child) }}" @class(['is-active' => $currentCategory?->id === $child->id]) @if ($currentCategory?->id === $child->id) aria-current="page" @endif>{{ $child->name }}</a></li>
                                    @endforeach
                                </ul>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </nav>
            <div class="card" style="padding:20px;display:flex;flex-direction:column;gap:10px">
                <span class="icon-tile"><x-ui-icon name="phone" :size="22" :stroke="1.9" /></span>
                <strong style="font-size:16px">Cần tư vấn chọn thiết bị?</strong>
                <span class="muted" style="font-size:14px">Cho chúng tôi biết thông số và điều kiện vận hành.</span>
                <a href="{{ tel_href(setting('phone')) }}" style="font-size:18px;font-weight:800">{{ setting('phone') }}</a>
                <a class="link-more" href="{{ route('contact') }}">Gửi liên hệ →</a>
            </div>
        </aside>

        <section style="flex:999 1 600px;min-width:0;display:flex;flex-direction:column;gap:20px" aria-labelledby="results-heading">
            <div class="toolbar">
                <div>
                    <h2 id="results-heading">{{ isset($term) ? 'Kết quả' : 'Sản phẩm' }}</h2>
                    <span class="muted" style="font-size:14px">{{ $products->total() }} sản phẩm</span>
                </div>
                <form class="search-form" action="{{ route('products.index') }}" method="get" role="search">
                    <label for="listing-search" class="sr-only">Tìm sản phẩm</label>
                    <input id="listing-search" type="search" name="q" value="{{ $term ?? '' }}" placeholder="Tìm theo tên hoặc mã" style="height:44px;width:240px;max-width:100%">
                    <button type="submit" style="height:44px" aria-label="Tìm"><x-ui-icon name="search" /></button>
                </form>
            </div>

            @if ($products->isEmpty())
                <div class="card empty">
                    <p>Không tìm thấy sản phẩm phù hợp.</p>
                    <p style="margin-top:12px"><a class="link-more" href="{{ route('contact', ['chu-de' => 'product', 'tieu-de' => isset($term) ? 'Tìm sản phẩm: '.$term : '']) }}">Gửi yêu cầu để kỹ sư tư vấn →</a></p>
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
