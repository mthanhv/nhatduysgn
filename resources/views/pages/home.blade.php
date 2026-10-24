@extends('layouts.site')

@section('content')
    <section class="hero">
        <div class="container">
            <div class="hero-text">
                <span class="eyebrow eyebrow--pill" style="align-self:flex-start">{{ setting('home_hero_eyebrow') }}</span>
                <h1>{{ setting('home_hero_title') }}</h1>
                <p>{{ setting('home_hero_text') }}</p>
                <div class="hero-actions">
                    <a class="btn btn-primary btn-lg" href="{{ route('about') }}">Về {{ setting('brand_name') }} <x-ui-icon name="arrow-right" :size="18" /></a>
                    <a class="btn btn-outline btn-lg" href="{{ route('products.index') }}">Xem sản phẩm</a>
                </div>
            </div>
            <div class="hero-media">
                <img src="{{ media_url(setting('home_hero_image'), asset('images/hero-ith-cx.jpg')) }}" alt="{{ setting('home_hero_caption_title') }}" fetchpriority="high">
                @if (setting('home_hero_caption_title'))
                    <div class="hero-caption">
                        <small>{{ setting('home_hero_caption_brand') }}</small>
                        <strong>{{ setting('home_hero_caption_title') }}</strong>
                    </div>
                @endif
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container split">
            <div class="split-text">
                <span class="eyebrow">Về chúng tôi</span>
                <h2>{{ setting('home_about_title') }}</h2>
                <p>{{ setting('home_about_text') }}</p>
                <ul class="checklist">
                    @foreach (setting('home_about_points', []) as $point)
                        <li><x-ui-icon name="check" :size="22" /><span><strong>{{ $point['title'] }}</strong> — {{ $point['text'] }}</span></li>
                    @endforeach
                </ul>
                <a class="link-more" href="{{ route('about') }}">Đọc thêm về {{ setting('brand_name') }} →</a>
            </div>
            <div class="split-media">
                <img src="{{ media_url(setting('home_about_image'), asset('images/about-office.jpg')) }}" alt="" loading="lazy">
            </div>
        </div>
    </section>

    @if ($categories->isNotEmpty())
        <section class="section">
            <div class="container">
                <div class="section-head">
                    <div>
                        <h2>Sản phẩm</h2>
                        <p>Thiết bị và vật tư bảo trì theo từng nhóm ứng dụng.</p>
                    </div>
                    <a class="link-more" href="{{ route('products.index') }}">Xem tất cả nhóm sản phẩm →</a>
                </div>
                <div class="grid grid-280">
                    @foreach ($categories as $category)
                        <a class="card cat-card" href="{{ route('products.category', $category) }}">
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
                    <h2>Sản phẩm tiêu biểu</h2>
                    <a class="link-more" href="{{ route('products.index') }}">Xem danh mục →</a>
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
                        <h2>Dịch vụ kỹ thuật tại hiện trường</h2>
                        <p>{{ setting('services_intro') }}</p>
                    </div>
                    <a href="{{ route('services') }}" style="font-weight:700">Tất cả dịch vụ →</a>
                </div>
                <div class="grid grid-280">
                    @foreach ($services as $service)
                        <a class="service-tile" href="{{ route('services') }}#{{ $service->slug }}">
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
                <div class="section-head"><h2>Thương hiệu đại diện &amp; phân phối</h2></div>
                <div class="grid grid-brands">
                    @foreach ($brands as $brand)
                        <a class="card brand-tile" href="{{ route('products.brand', $brand) }}">
                            @if ($brand->logo)
                                <img src="{{ media_url($brand->logo) }}" alt="{{ $brand->name }}" loading="lazy">
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
