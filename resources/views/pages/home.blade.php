@extends('layouts.site')

@section('content')
    <section class="hero">
        <div class="container">
            @php
                $hero = \App\Services\ContentBlock::parse(setting('home_hero_content'));
                $caption = array_values(array_filter(array_map('trim', preg_split('/\R/', (string) setting('home_hero_caption')))));
            @endphp
            <div class="hero-text">
                @if ($hero->eyebrow)<span class="eyebrow eyebrow--pill" style="align-self:flex-start">{{ $hero->eyebrow }}</span>@endif
                <h1>{{ $hero->title }}</h1>
                {!! $hero->body !!}
                <div class="hero-actions">
                    <a class="btn btn-primary btn-lg" href="{{ lroute('about') }}">{{ __('Về :name', ['name' => setting('brand_name')]) }} <x-ui-icon name="arrow-right" :size="18" /></a>
                    <a class="btn btn-outline btn-lg" href="{{ lroute('products.index') }}">{{ __('Xem sản phẩm') }}</a>
                </div>
            </div>
            <div class="hero-media">
                <img src="{{ thumb_url(setting('home_hero_image'), 1280, asset('images/hero-ith-cx.jpg')) }}" alt="{{ end($caption) ?: '' }}" fetchpriority="high">
                @if ($caption)
                    <div class="hero-caption">
                        @if (count($caption) > 1)<small>{{ $caption[0] }}</small>@endif
                        <strong>{{ end($caption) }}</strong>
                    </div>
                @endif
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container split">
            @php
                $about = \App\Services\ContentBlock::parse(setting('home_about_content'));
            @endphp
            <div class="split-text">
                <span class="eyebrow">{{ __('Về chúng tôi') }}</span>
                @if ($about->title)<h2>{{ $about->title }}</h2>@endif
                {!! \App\Services\RichContent::render($about->body) !!}
                @if ($about->items)
                    <ul class="checklist">
                        @foreach ($about->items as $point)
                            <li><x-ui-icon name="check" :size="22" /><span>@if ($point['title'])<strong>{{ $point['title'] }}</strong>@if ($point['text']) — @endif @endif{{ $point['text'] }}</span></li>
                        @endforeach
                    </ul>
                @endif
                <a class="link-more" href="{{ lroute('about') }}">{{ __('Đọc thêm về :name', ['name' => setting('brand_name')]) }} →</a>
            </div>
            <div class="split-media">
                <img src="{{ thumb_url(setting('home_about_image'), 1280, asset('images/about-office.jpg')) }}" alt="" loading="lazy">
            </div>
        </div>
    </section>

    @if ($categories->isNotEmpty())
        <section class="section">
            <div class="container">
                <div class="section-head">
                    <div>
                        <h2>{{ __('Sản phẩm') }}</h2>
                        <p>{{ __('Thiết bị và vật tư bảo trì theo từng nhóm ứng dụng.') }}</p>
                    </div>
                    <a class="link-more" href="{{ lroute('products.index') }}">{{ __('Xem tất cả nhóm sản phẩm') }} →</a>
                </div>
                <div class="grid grid-280">
                    @foreach ($categories as $category)
                        <a class="card cat-card" href="{{ lroute('products.category', $category) }}">
                            <span class="icon-tile"><x-ui-icon :name="$category->icon ?: 'box'" :size="22" :stroke="1.8" /></span>
                            <span>
                                <h3>{{ $category->name }}</h3>
                                @if ($category->summary)<p>{{ $category->summary }}</p>@endif
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($featuredProducts->isNotEmpty())
        <section class="section">
            <div class="container">
                <div class="section-head">
                    <h2>{{ __('Sản phẩm tiêu biểu') }}</h2>
                    <a class="link-more" href="{{ lroute('products.index') }}">{{ __('Xem danh mục') }} →</a>
                </div>
                <div class="grid grid-250">
                    @foreach ($featuredProducts as $product)
                        <x-product-card :product="$product" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($services->isNotEmpty())
        <section class="band-navy">
            <div class="container">
                <div class="section-head">
                    <div style="max-width:680px">
                        <h2>{{ __('Dịch vụ kỹ thuật tại hiện trường') }}</h2>
                        <p>{{ \App\Services\ContentBlock::parse(setting('services_hero_content'))->bodyText() }}</p>
                    </div>
                    <a href="{{ lroute('services') }}" style="font-weight:700">{{ __('Tất cả dịch vụ') }} →</a>
                </div>
                <div class="grid grid-280">
                    @foreach ($services as $service)
                        <a class="service-tile" href="{{ lroute('services') }}#{{ $service->slug }}">
                            <span class="num">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                            <h3>{{ $service->title }}</h3>
                            <p>{{ $service->summary }}</p>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($brands->isNotEmpty())
        <section class="section" style="padding-bottom:72px">
            <div class="container">
                <div class="section-head"><h2>{{ __('Thương hiệu đại diện & phân phối') }}</h2></div>
                <div class="grid grid-brands">
                    @foreach ($brands as $brand)
                        <a class="card brand-tile" href="{{ lroute('products.brand', $brand) }}">
                            @if ($brand->logo)
                                <img src="{{ thumb_url($brand->logo, 320) }}" alt="{{ $brand->name }}" loading="lazy">
                            @else
                                {{ $brand->name }}
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @include('partials.cta-band')
@endsection
