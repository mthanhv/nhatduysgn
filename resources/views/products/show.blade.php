@extends('layouts.site')
@unless ($product->isTranslatedInto('en'))
    @section('untranslated', '1')
@endunless

@section('title', \App\Services\Seo::productTitle($product))
@section('description', \App\Services\Seo::productDescription($product))
@section('og_type', 'product')
@if ($product->image)
    @section('og_image', media_url($product->image))
@endif

@push('head')
    <x-json-ld :data="\App\Services\Seo::product($product)" />
@endpush

@section('content')
    @php
        $crumbs = [__('Sản phẩm') => lroute('products.index')];
        if ($product->category) {
            if ($product->category->parent) {
                $crumbs[$product->category->parent->name] = lroute('products.category', $product->category->parent);
            }
            $crumbs[$product->category->name] = lroute('products.category', $product->category);
        }
        $crumbs[$product->name] = null;
        $images = collect([$product->image])->merge($product->gallery ?? [])->filter()->unique()->values();
        $documents = collect($product->documents ?? [])->filter(fn ($document) => filled($document['file'] ?? null));
    @endphp

    <div class="container" style="padding-top:20px"><x-breadcrumb :items="$crumbs" /></div>

    <section class="container product-top">
        <div class="gallery" data-gallery>
            <div class="main">
                @if ($images->isNotEmpty())
                    <img src="{{ thumb_url($images->first(), 960) }}" alt="{{ $product->name }}" fetchpriority="high" data-gallery-main>
                @else
                    <x-ui-icon name="box" :size="96" :stroke="1.2" style="color:#9aa5b0" />
                @endif
            </div>
            @if ($images->count() > 1)
                <div class="thumbs">
                    @foreach ($images as $image)
                        <button type="button" data-gallery-thumb data-src="{{ thumb_url($image, 960) }}" aria-current="{{ $loop->first ? 'true' : 'false' }}" aria-label="{{ __('Ảnh') }} {{ $loop->iteration }}">
                            <img src="{{ thumb_url($image, 160) }}" alt="{{ $product->name }} – {{ __('ảnh') }} {{ $loop->iteration }}" loading="lazy">
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="product-info">
            <div class="badges">
                @if ($product->brand)<a class="badge badge--dark" href="{{ lroute('products.brand', $product->brand) }}">{{ $product->brand->name }}</a>@endif
                @if ($product->origin)<span class="badge">{{ $product->origin }}</span>@endif
                <span class="badge badge--ok">{{ __('Hàng chính hãng') }}</span>
            </div>
            <h1>{{ $product->name }}</h1>
            @if ($product->summary)<p class="muted" style="font-size:16px">{{ $product->summary }}</p>@endif

            <div class="card info-card">
                @if ($product->code || $product->category)
                    <dl class="spec-list">
                        @if ($product->code)<div><dt>{{ __('Mã sản phẩm') }}</dt><dd>{{ $product->code }}</dd></div>@endif
                        @if ($product->category)<div><dt>{{ __('Nhóm') }}</dt><dd style="font-family:var(--font);font-size:15px;font-weight:600">{{ $product->category->name }}</dd></div>@endif
                    </dl>
                @endif
                <div class="cta-row">
                    <a class="btn btn-primary btn-lg" href="{{ lroute('contact', ['chu-de' => 'product', 'tieu-de' => __('Tư vấn').': '.$product->name.($product->code ? ' ('.$product->code.')' : '')]) }}">{{ __('Liên hệ tư vấn sản phẩm') }}</a>
                    @if ($documents->isNotEmpty())
                        <a class="btn btn-outline btn-lg" href="{{ media_url($documents->first()['file']) }}" target="_blank" rel="noopener"><x-ui-icon name="download" :size="18" /> {{ __('Tải tài liệu') }}</a>
                    @endif
                </div>
                <span class="muted" style="font-size:14px">{{ __('Hoặc gọi') }} <a href="{{ tel_href(setting('phone')) }}"><strong>{{ setting('phone') }}</strong></a> {{ __('để được tư vấn trực tiếp.') }}</span>
            </div>
        </div>
    </section>

    @php
        $panels = collect([
            'mo-ta' => ['label' => __('Mô tả'), 'show' => filled($product->description)],
            'thong-so' => ['label' => __('Thông số kỹ thuật'), 'show' => filled($product->specifications)],
            'tai-lieu' => ['label' => __('Tài liệu').' ('.$documents->count().')', 'show' => $documents->isNotEmpty()],
        ])->filter(fn ($panel) => $panel['show']);
    @endphp
    @if ($panels->isNotEmpty())
        <section class="container tabs" style="padding-bottom:40px">
            <div role="tablist" aria-label="{{ __('Thông tin sản phẩm') }}" data-tabs hidden>
                @foreach ($panels as $id => $panel)
                    <button type="button" role="tab" id="tab-{{ $id }}" aria-controls="panel-{{ $id }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}">{{ $panel['label'] }}</button>
                @endforeach
            </div>
            @foreach ($panels as $id => $panel)
                <div class="panel" role="tabpanel" id="panel-{{ $id }}" aria-labelledby="tab-{{ $id }}">
                    @if ($id === 'mo-ta')
                        <div class="prose">{!! $product->description !!}</div>
                    @elseif ($id === 'thong-so')
                        <div class="prose">{!! $product->specifications !!}</div>
                    @else
                        <div class="grid grid-280">
                            @foreach ($documents as $document)
                                <a class="card doc-link" href="{{ media_url($document['file']) }}" target="_blank" rel="noopener">
                                    <span class="ext">{{ strtoupper(pathinfo($document['file'], PATHINFO_EXTENSION)) }}</span>
                                    <span>{{ $document['title'] ?: basename($document['file']) }}</span>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </section>
    @endif

    @if ($related->isNotEmpty())
        <section class="container" style="padding-bottom:72px">
            <div class="section-head">
                <h2>{{ __('Sản phẩm cùng nhóm') }}</h2>
                @if ($product->category)<a class="link-more" href="{{ lroute('products.category', $product->category) }}">{{ __('Xem tất cả') }} →</a>@endif
            </div>
            <div class="grid grid-250">
                @foreach ($related as $item)
                    <x-product-card :product="$item" />
                @endforeach
            </div>
        </section>
    @endif
@endsection
