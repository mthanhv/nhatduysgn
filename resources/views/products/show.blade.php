@extends('layouts.site')

@section('title', $product->meta_title ?: $product->name)
@section('description', $product->meta_description ?: ($product->summary ?: \Illuminate\Support\Str::limit(strip_tags((string) $product->description), 160)))
@if ($product->image)
    @section('og_image', media_url($product->image))
@endif

@section('content')
    @php
        $crumbs = ['Sản phẩm' => route('products.index')];
        if ($product->category) {
            if ($product->category->parent) {
                $crumbs[$product->category->parent->name] = route('products.category', $product->category->parent);
            }
            $crumbs[$product->category->name] = route('products.category', $product->category);
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
                    <img src="{{ media_url($images->first()) }}" alt="{{ $product->name }}" data-gallery-main>
                @else
                    <x-ui-icon name="box" :size="96" :stroke="1.2" style="color:#9aa5b0" />
                @endif
            </div>
            @if ($images->count() > 1)
                <div class="thumbs">
                    @foreach ($images as $image)
                        <button type="button" data-gallery-thumb data-src="{{ media_url($image) }}" aria-current="{{ $loop->first ? 'true' : 'false' }}" aria-label="Ảnh {{ $loop->iteration }}">
                            <img src="{{ media_url($image) }}" alt="" loading="lazy">
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="product-info">
            <div class="badges">
                @if ($product->brand)<a class="badge badge--dark" href="{{ route('products.brand', $product->brand) }}">{{ $product->brand->name }}</a>@endif
                @if ($product->origin)<span class="badge">{{ $product->origin }}</span>@endif
                <span class="badge badge--ok">Hàng chính hãng</span>
            </div>
            <h1>{{ $product->name }}</h1>
            @if ($product->summary)<p class="muted" style="font-size:16px">{{ $product->summary }}</p>@endif

            <div class="card info-card">
                @if ($product->code || $product->category)
                    <dl class="spec-list">
                        @if ($product->code)<div><dt>Mã sản phẩm</dt><dd>{{ $product->code }}</dd></div>@endif
                        @if ($product->category)<div><dt>Nhóm</dt><dd style="font-family:var(--font);font-size:15px;font-weight:600">{{ $product->category->name }}</dd></div>@endif
                    </dl>
                @endif
                <div class="cta-row">
                    <a class="btn btn-primary btn-lg" href="{{ route('contact', ['chu-de' => 'product', 'tieu-de' => 'Tư vấn: '.$product->name.($product->code ? ' ('.$product->code.')' : '')]) }}">Liên hệ tư vấn sản phẩm</a>
                    @if ($documents->isNotEmpty())
                        <a class="btn btn-outline btn-lg" href="{{ media_url($documents->first()['file']) }}" target="_blank" rel="noopener"><x-ui-icon name="download" :size="18" /> Tải tài liệu</a>
                    @endif
                </div>
                <span class="muted" style="font-size:14px">Hoặc gọi <a href="{{ tel_href(setting('phone')) }}"><strong>{{ setting('phone') }}</strong></a> để được tư vấn trực tiếp.</span>
            </div>
        </div>
    </section>

    @php
        $panels = collect([
            'mo-ta' => ['label' => 'Mô tả', 'show' => filled($product->description)],
            'thong-so' => ['label' => 'Thông số kỹ thuật', 'show' => filled($product->specifications)],
            'tai-lieu' => ['label' => 'Tài liệu ('.$documents->count().')', 'show' => $documents->isNotEmpty()],
        ])->filter(fn ($panel) => $panel['show']);
    @endphp
    @if ($panels->isNotEmpty())
        <section class="container tabs" style="padding-bottom:40px">
            <div role="tablist" aria-label="Thông tin sản phẩm" data-tabs hidden>
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
                <h2>Sản phẩm cùng nhóm</h2>
                @if ($product->category)<a class="link-more" href="{{ route('products.category', $product->category) }}">Xem tất cả →</a>@endif
            </div>
            <div class="grid grid-250">
                @foreach ($related as $item)
                    <x-product-card :product="$item" />
                @endforeach
            </div>
        </section>
    @endif
@endsection
