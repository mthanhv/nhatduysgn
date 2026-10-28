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
    // A record page whose English version is not filled in yet (@section('untranslated', '1')) is not offered to
    // search engines in English: no hreflang pair, and noindex on its /en copy.
    $locale = app()->getLocale();
    $untranslated = trim($__env->yieldContent('untranslated')) === '1';
    $robots = app()->environment('production')
        ? (trim($__env->yieldContent('robots')) ?: ($locale === 'en' && $untranslated ? 'noindex, follow' : 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1'))
        : 'noindex, nofollow';
    // Current page check for both languages (English route names carry an "en." prefix).
    $isRoute = fn (string|array $patterns): bool => request()->routeIs(...collect((array) $patterns)->flatMap(fn (string $pattern) => [$pattern, 'en.'.$pattern])->all());
    $nav = [
        ['route' => 'home', 'match' => 'home', 'label' => __('Trang chủ')],
        ['route' => 'about', 'match' => 'about', 'label' => __('Giới thiệu')],
        // 'menu' items open a pop-up (partials/nav-{menu}) listing these entries; with none they stay a plain link.
        ['route' => 'products.index', 'match' => ['products.index', 'products.category', 'products.show'], 'label' => __('Sản phẩm'), 'menu' => 'products', 'entries' => $navCategories],
        ['route' => 'products.index', 'match' => 'products.brand', 'label' => __('Thương hiệu'), 'menu' => 'brands', 'entries' => $navBrands],
        ['route' => 'services', 'match' => 'services', 'label' => __('Dịch vụ')],
        ['route' => 'projects.index', 'match' => 'projects.*', 'label' => __('Dự án')],
        ['route' => 'posts.index', 'match' => 'posts.*', 'label' => __('Tin tức')],
    ];
@endphp
<!doctype html>
<html lang="{{ $locale }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $fullTitle }}</title>
    <meta name="description" content="{{ $description }}">
    <meta name="robots" content="{{ $robots }}">
    <link rel="canonical" href="{{ $canonical }}">
    @unless ($untranslated)
        <link rel="alternate" hreflang="vi" href="{{ locale_url('vi') }}">
        <link rel="alternate" hreflang="en" href="{{ locale_url('en') }}">
        <link rel="alternate" hreflang="x-default" href="{{ locale_url('vi') }}">
    @endunless
    <link rel="alternate" type="text/plain" title="{{ __('Thông tin cho AI') }}" href="{{ url('/llms.txt') }}">
    <meta property="og:type" content="{{ $ogType }}">
    <meta property="og:site_name" content="{{ setting('company_name') }}">
    <meta property="og:title" content="{{ $fullTitle }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:image:alt" content="{{ $pageTitle !== '' ? $pageTitle : setting('brand_name') }}">
    <meta property="og:locale" content="{{ $locale === 'en' ? 'en_US' : 'vi_VN' }}">
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
<a class="skip-link" href="#main">{{ __('Bỏ qua đến nội dung chính') }}</a>

<div class="topbar">
    <div class="container">
        <div style="display:flex;flex-wrap:wrap;gap:6px 24px">
            <span>{{ __('Điện thoại') }}: <a href="{{ tel_href(setting('phone')) }}"><strong>{{ setting('phone') }}</strong></a></span>
            <a href="mailto:{{ setting('email') }}">{{ setting('email') }}</a>
        </div>
        <div class="topbar-end">
            <span class="topbar-hours">{{ setting('working_hours') }}</span>
            <nav class="lang-switch" aria-label="{{ __('Ngôn ngữ') }}">
                <a href="{{ locale_url('vi') }}" hreflang="vi" lang="vi" @if ($locale === 'vi') aria-current="true" @endif>VI</a>
                <a href="{{ locale_url('en') }}" hreflang="en" lang="en" @if ($locale === 'en') aria-current="true" @endif>EN</a>
            </nav>
        </div>
    </div>
</div>

<header class="site-header">
    <div class="container">
        <a class="brand" href="{{ lroute('home') }}">
            <x-logo />
            <span class="brand-text">
                <span class="brand-name">{{ mb_strtoupper(setting('brand_name')) }}</span>
                <span class="brand-tagline">{{ setting('tagline') }}</span>
            </span>
        </a>
        <nav id="main-nav" class="main-nav" aria-label="{{ __('Chính') }}">
            @foreach ($nav as $item)
                @if (isset($item['menu']) && $item['entries']->isNotEmpty())
                    {{-- A <details> pop-up opens without JavaScript; site.js only closes it on an outside click or Escape. --}}
                    <details class="nav-menu nav-menu--{{ $item['menu'] }}" data-nav-menu>
                        <summary @class(['is-current' => $isRoute($item['match'])])>{{ $item['label'] }} <x-ui-icon name="chevron-right" :size="16" class="nav-menu__toggle" /></summary>
                        <div class="nav-menu__panel">
                            @include('partials.nav-'.$item['menu'], ['entries' => $item['entries']])
                        </div>
                    </details>
                @else
                    <a href="{{ lroute($item['route']) }}" @if ($isRoute($item['match'])) aria-current="page" @endif>{{ $item['label'] }}</a>
                @endif
            @endforeach
        </nav>
        <div class="header-actions">
            <button type="button" class="icon-btn" data-toggle aria-controls="search-panel" aria-expanded="false" data-focus="input" aria-label="{{ __('Tìm sản phẩm') }}">
                <x-ui-icon name="search" />
            </button>
            <a class="btn btn-primary btn-contact" href="{{ lroute('contact') }}" @if ($isRoute('contact')) aria-current="page" @endif>{{ __('Liên hệ') }}</a>
            <button type="button" class="icon-btn menu-toggle" data-toggle aria-controls="main-nav" aria-expanded="false" aria-label="{{ __('Mở menu') }}">
                <x-ui-icon name="menu" />
            </button>
        </div>
    </div>
    <div id="search-panel" class="search-panel" hidden>
        <div class="container">
            <form class="search-form" action="{{ lroute('products.index') }}" method="get" role="search" style="width:100%">
                <label for="header-search" class="sr-only">{{ __('Tìm sản phẩm') }}</label>
                <input id="header-search" type="search" name="q" placeholder="{{ __('Tìm theo tên, mã sản phẩm hoặc thương hiệu — vd. Loctite 243, CX-5') }}" value="{{ request('q') }}">
                <button type="submit"><x-ui-icon name="search" /> {{ __('Tìm') }}</button>
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
            <p>{{ __('Trụ sở') }}: {{ setting('address') }}</p>
            @if (setting('office_address'))<p>{{ __('VPĐD') }}: {{ setting('office_address') }}</p>@endif
            <p>{{ __('MST') }}: {{ setting('tax_code') }}</p>
        </div>
        <div>
            <h3>{{ __('Liên hệ') }}</h3>
            <ul>
                <li>{{ __('Điện thoại') }}: <a href="{{ tel_href(setting('phone')) }}">{{ setting('phone') }}</a></li>
                @if (setting('fax'))<li>Fax: {{ setting('fax') }}</li>@endif
                <li><a href="mailto:{{ setting('email') }}">{{ setting('email') }}</a></li>
                <li>{{ setting('working_hours') }}</li>
            </ul>
        </div>
        <div>
            <h3>{{ __('Sản phẩm') }}</h3>
            <ul>
                @foreach ($footerCategories as $footerCategory)
                    <li><a href="{{ lroute('products.category', $footerCategory) }}">{{ $footerCategory->name }}</a></li>
                @endforeach
            </ul>
        </div>
        <div>
            <h3>{{ __('Công ty') }}</h3>
            <ul>
                <li><a href="{{ lroute('about') }}">{{ __('Giới thiệu') }}</a></li>
                <li><a href="{{ lroute('services') }}">{{ __('Dịch vụ') }}</a></li>
                <li><a href="{{ lroute('projects.index') }}">{{ __('Dự án') }}</a></li>
                <li><a href="{{ lroute('posts.index') }}">{{ __('Tin tức') }}</a></li>
                <li><a href="{{ lroute('contact') }}">{{ __('Liên hệ') }}</a></li>
            </ul>
        </div>
    </div>
    <div class="container bottom">© {{ now()->year }} {{ setting('company_name') }}. {{ __('Bảo lưu mọi quyền.') }}</div>
</footer>

<nav class="mobile-bar" aria-label="{{ __('Liên hệ nhanh') }}">
    <a class="btn btn-outline" href="{{ tel_href(setting('phone')) }}"><x-ui-icon name="phone" :size="18" /> {{ __('Gọi') }}</a>
    @if (setting('zalo'))
        <a class="btn btn-zalo" href="https://zalo.me/{{ preg_replace('/\D/', '', setting('zalo')) }}" rel="noopener" target="_blank">Zalo</a>
    @else
        <a class="btn btn-outline" href="mailto:{{ setting('email') }}"><x-ui-icon name="mail" :size="18" /> Email</a>
    @endif
    <a class="btn btn-primary" href="{{ lroute('contact') }}">{{ __('Liên hệ') }}</a>
</nav>

<script src="{{ asset('js/site.js') }}?v={{ filemtime(public_path('js/site.js')) }}" defer></script>
</body>
</html>
