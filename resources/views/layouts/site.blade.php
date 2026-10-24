@php
    $pageTitle = trim($__env->yieldContent('title'));
    $fullTitle = $pageTitle !== '' ? $pageTitle.' | '.setting('brand_name') : setting('seo_title');
    $description = trim($__env->yieldContent('description')) ?: setting('seo_description');
    $ogImage = trim($__env->yieldContent('og_image')) ?: asset('images/hero-ith-cx.jpg');
    $nav = [
        ['route' => 'home', 'match' => 'home', 'label' => 'Trang chủ'],
        ['route' => 'about', 'match' => 'about', 'label' => 'Giới thiệu'],
        ['route' => 'products.index', 'match' => 'products.*', 'label' => 'Sản phẩm'],
        ['route' => 'services', 'match' => 'services', 'label' => 'Dịch vụ'],
        ['route' => 'projects.index', 'match' => 'projects.*', 'label' => 'Dự án'],
        ['route' => 'posts.index', 'match' => 'posts.*', 'label' => 'Tin tức'],
    ];
@endphp
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $fullTitle }}</title>
    <meta name="description" content="{{ \Illuminate\Support\Str::limit(strip_tags($description), 160) }}">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ setting('company_name') }}">
    <meta property="og:title" content="{{ $fullTitle }}">
    <meta property="og:description" content="{{ \Illuminate\Support\Str::limit(strip_tags($description), 200) }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:locale" content="vi_VN">
    <meta name="theme-color" content="#0E1E2E">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800&family=IBM+Plex+Mono:wght@500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/site.css') }}?v={{ filemtime(public_path('css/site.css')) }}">
    <script type="application/ld+json">{!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => setting('company_name'),
        'url' => url('/'),
        'logo' => asset('favicon.svg'),
        'email' => setting('email'),
        'telephone' => setting('phone'),
        'foundingDate' => setting('founded_year'),
        'taxID' => setting('tax_code'),
        'address' => ['@type' => 'PostalAddress', 'streetAddress' => setting('address'), 'addressCountry' => 'VN'],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
    @stack('head')
</head>
<body>
<a class="skip-link" href="#main">Bỏ qua đến nội dung chính</a>

<div class="topbar">
    <div class="container">
        <div style="display:flex;flex-wrap:wrap;gap:6px 24px">
            <span>Điện thoại: <a href="{{ tel_href(setting('phone')) }}"><strong>{{ setting('phone') }}</strong></a></span>
            <a href="mailto:{{ setting('email') }}">{{ setting('email') }}</a>
        </div>
        <span class="topbar-hours">{{ setting('working_hours') }}</span>
    </div>
</div>

<header class="site-header">
    <div class="container">
        <a class="brand" href="{{ route('home') }}">
            <x-logo />
            <span class="brand-text">
                <span class="brand-name">{{ mb_strtoupper(setting('brand_name')) }}</span>
                <span class="brand-tagline">{{ setting('tagline') }}</span>
            </span>
        </a>
        <nav id="main-nav" class="main-nav" aria-label="Chính">
            @foreach ($nav as $item)
                <a href="{{ route($item['route']) }}" @if (request()->routeIs($item['match'])) aria-current="page" @endif>{{ $item['label'] }}</a>
            @endforeach
        </nav>
        <div class="header-actions">
            <button type="button" class="icon-btn" data-toggle aria-controls="search-panel" aria-expanded="false" data-focus="input" aria-label="Tìm sản phẩm">
                <x-ui-icon name="search" />
            </button>
            <a class="btn btn-primary btn-contact" href="{{ route('contact') }}" @if (request()->routeIs('contact')) aria-current="page" @endif>Liên hệ</a>
            <button type="button" class="icon-btn menu-toggle" data-toggle aria-controls="main-nav" aria-expanded="false" aria-label="Mở menu">
                <x-ui-icon name="menu" />
            </button>
        </div>
    </div>
    <div id="search-panel" class="search-panel" hidden>
        <div class="container">
            <form class="search-form" action="{{ route('products.index') }}" method="get" role="search" style="width:100%">
                <label for="header-search" class="sr-only">Tìm sản phẩm</label>
                <input id="header-search" type="search" name="q" placeholder="Tìm theo tên, mã sản phẩm hoặc thương hiệu — vd. Loctite 243, CX-5" value="{{ request('q') }}">
                <button type="submit"><x-ui-icon name="search" /> Tìm</button>
            </form>
        </div>
    </div>
</header>

<main id="main">
    @yield('content')
</main>

<footer class="site-footer">
    <div class="container cols">
        <div>
            <p class="company">{{ mb_strtoupper(setting('company_name')) }}</p>
            <p>Trụ sở: {{ setting('address') }}</p>
            @if (setting('office_address'))<p>VPĐD: {{ setting('office_address') }}</p>@endif
            <p>MST: {{ setting('tax_code') }}</p>
        </div>
        <div>
            <h3>Liên hệ</h3>
            <ul>
                <li>Điện thoại: <a href="{{ tel_href(setting('phone')) }}">{{ setting('phone') }}</a></li>
                @if (setting('fax'))<li>Fax: {{ setting('fax') }}</li>@endif
                <li><a href="mailto:{{ setting('email') }}">{{ setting('email') }}</a></li>
                <li>{{ setting('working_hours') }}</li>
            </ul>
        </div>
        <div>
            <h3>Sản phẩm</h3>
            <ul>
                @foreach ($footerCategories as $footerCategory)
                    <li><a href="{{ route('products.category', $footerCategory) }}">{{ $footerCategory->name }}</a></li>
                @endforeach
            </ul>
        </div>
        <div>
            <h3>Công ty</h3>
            <ul>
                <li><a href="{{ route('about') }}">Giới thiệu</a></li>
                <li><a href="{{ route('services') }}">Dịch vụ</a></li>
                <li><a href="{{ route('projects.index') }}">Dự án</a></li>
                <li><a href="{{ route('posts.index') }}">Tin tức</a></li>
                <li><a href="{{ route('contact') }}">Liên hệ</a></li>
            </ul>
        </div>
    </div>
    <div class="container bottom">© {{ now()->year }} {{ setting('company_name') }}. Bảo lưu mọi quyền.</div>
</footer>

<nav class="mobile-bar" aria-label="Liên hệ nhanh">
    <a class="btn btn-outline" href="{{ tel_href(setting('phone')) }}"><x-ui-icon name="phone" :size="18" /> Gọi</a>
    @if (setting('zalo'))
        <a class="btn btn-zalo" href="https://zalo.me/{{ preg_replace('/\D/', '', setting('zalo')) }}" rel="noopener" target="_blank">Zalo</a>
    @else
        <a class="btn btn-outline" href="mailto:{{ setting('email') }}"><x-ui-icon name="mail" :size="18" /> Email</a>
    @endif
    <a class="btn btn-primary" href="{{ route('contact') }}">Liên hệ</a>
</nav>

<script src="{{ asset('js/site.js') }}?v={{ filemtime(public_path('js/site.js')) }}" defer></script>
</body>
</html>
