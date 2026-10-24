@extends('layouts.site')

@section('title', 'Sản phẩm')
@section('description', 'Thiết bị, dụng cụ và vật tư bảo trì công nghiệp chính hãng: dụng cụ thủy lực ITH, vòng bi Schaeffler, bôi trơn Klüber, keo Loctite, thiết bị ống thép JBE…')

@section('content')
    <section class="page-hero page-hero--grid">
        <div class="container">
            <x-breadcrumb :items="['Sản phẩm' => null]" />
            <h1>Sản phẩm</h1>
            <p>Thiết bị, dụng cụ và vật tư bảo trì chính hãng từ {{ $brands->count() }} thương hiệu quốc tế, chia theo {{ $categories->count() }} nhóm ứng dụng.</p>
            <form class="search-form search-form--hero" action="{{ route('products.index') }}" method="get" role="search" style="max-width:720px;margin-top:6px">
                <label for="product-search" class="sr-only">Tìm sản phẩm</label>
                <input id="product-search" type="search" name="q" placeholder="Tìm theo tên, mã sản phẩm hoặc thương hiệu — vd. Loctite 243, CX-5">
                <button type="submit"><x-ui-icon name="search" /> Tìm</button>
            </form>
        </div>
    </section>

    @if ($featured->isNotEmpty())
        <section class="section" style="padding-top:56px">
            <div class="container">
                <div class="section-head"><h2>Nhóm sản phẩm chủ lực</h2></div>
                <div class="grid grid-280">
                    @foreach ($featured as $category)
                        <a class="card product-card" href="{{ route('products.category', $category) }}">
                            <span class="thumb">
                                @if ($cover = $category->coverImage())
                                    <img src="{{ thumb_url($cover, 640) }}" alt="{{ $category->name }}" loading="lazy">
                                @else
                                    <span class="icon-tile" style="width:72px;height:72px"><x-ui-icon :name="$category->icon ?: 'box'" :size="36" :stroke="1.6" /></span>
                                @endif
                            </span>
                            <span class="body">
                                <h3 style="font-size:18px">{{ $category->name }}</h3>
                                <span class="count" style="margin-top:0">{{ $counts[$category->id] ?? 0 }} sản phẩm</span>
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section class="section">
        <div class="container">
            <div class="section-head"><h2>Tất cả nhóm sản phẩm</h2></div>
            <div class="card" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(min(380px,100%),1fr));overflow:hidden">
                @foreach ($categories as $category)
                    <a href="{{ route('products.category', $category) }}" style="padding:20px 22px;display:flex;gap:16px;align-items:flex-start;border-bottom:1px solid var(--line-soft);border-right:1px solid var(--line-soft)">
                        <span class="icon-tile"><x-ui-icon :name="$category->icon ?: 'box'" :size="22" :stroke="1.8" /></span>
                        <span style="flex:1;min-width:0;display:flex;flex-direction:column;gap:4px">
                            <span style="display:flex;justify-content:space-between;gap:12px;align-items:baseline">
                                <strong style="font-size:16px;line-height:1.35">{{ $category->name }}</strong>
                                <span class="mono muted" style="font-size:13px">{{ $counts[$category->id] ?? 0 }}</span>
                            </span>
                            @if ($category->children->isNotEmpty())
                                <span class="muted" style="font-size:13px;line-height:1.5">{{ $category->children->pluck('name')->take(5)->implode(' · ') }}</span>
                            @endif
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    @if ($brands->isNotEmpty())
        <section class="section" style="padding-bottom:72px">
            <div class="container">
                <div class="section-head"><h2>Theo thương hiệu</h2></div>
                <div class="grid grid-brands">
                    @foreach ($brands as $brand)
                        <a class="card brand-tile" href="{{ route('products.brand', $brand) }}">
                            @if ($brand->logo)<img src="{{ thumb_url($brand->logo, 320) }}" alt="{{ $brand->name }}" loading="lazy">@else{{ $brand->name }}@endif
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @include('partials.cta-band', ['title' => 'Không tìm thấy sản phẩm cần?', 'text' => 'Gửi tên hoặc thông số, kỹ sư '.setting('brand_name').' sẽ tư vấn giúp.'])
@endsection
