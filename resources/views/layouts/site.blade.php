@php
    // @section('x', $value) escapes $value; decode once so {{ }} below doesn't show "&amp;" in titles.
    $section = fn (string $name) => html_entity_decode(trim($__env->yieldContent($name)), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $pageTitle = $section('title');
    $fullTitle = $pageTitle !== '' ? $pageTitle.' | '.setting('brand_name') : setting('seo_title');
    $description = \App\Services\Seo::text($section('description') ?: setting('seo_description'));
    $ogImage = \App\Services\Seo::absoluteUrl($section('og_image') ?: asset('images/hero-ith-cx.jpg'));
    $ogType = trim($__env->yieldContent('og_type')) ?: 'website';
    // Paginated pages are canonical to themselves; every other query string (filters, tracking) is dropped.
    $page = (int) request()->query('page', 1);
    $canonical = $section('canonical') ?: url()->current().($page > 1 ? '?page='.$page : '');
    $robots = app()->environment('production')
        ? (trim($__env->yieldContent('robots')) ?: 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1')
        : 'noindex, nofollow';
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
    <meta name="description" content="{{ $description }}">
    <meta name="robots" content="{{ $robots }}">
    <link rel="canonical" href="{{ $canonical }}">
    <link rel="alternate" type="text/plain" title="Thông tin cho AI" href="{{ url('/llms.txt') }}">
    <meta property="og:type" content="{{ $ogType }}">
    <meta property="og:site_name" content="{{ setting('company_name') }}">
    <meta property="og:title" content="{{ $fullTitle }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:image:alt" content="{{ $pageTitle !== '' ? $pageTitle : setting('brand_name') }}">
    <meta property="og:locale" content="vi_VN">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $fullTitle }}">
    <meta name="twitter:description" content="{{ $description }}">
    <meta name="twitter:image" content="{{ $ogImage }}">
    @if (setting('google_site_verification'))<meta name="google-site-verification" content="{{ setting('google_site_verification') }}">@endif
    @if (setting('bing_site_verification'))<meta name="msvalidate.01" content="{{ setting('bing_site_verification') }}">@endif
    <meta name="theme-color" content="#0E1E2E">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="32x32">
    <link rel="apple-touch-icon" href="{{ asset('images/logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800&family=IBM+Plex+Mono:wght@500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/site.css') }}?v={{ filemtime(public_path('css/site.css')) }}">
    <x-json-ld :data="[\App\Services\Seo::organization(), \App\Services\Seo::website()]" />
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
