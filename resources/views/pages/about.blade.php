@extends('layouts.site')

@section('title', 'Giới thiệu')
@section('description', setting('about_hero_text'))

@section('content')
    <section class="page-hero page-hero--photo">
        <img src="{{ thumb_url(setting('about_hero_image'), 1600, asset('images/about-hero.jpg')) }}" alt="">
        <div class="container">
            <x-breadcrumb :items="['Giới thiệu' => null]" />
            <div style="display:flex;flex-direction:column;gap:18px;max-width:680px">
                <span class="eyebrow eyebrow--light">Về {{ setting('brand_name') }}</span>
                <h1>{{ setting('about_hero_title') }}</h1>
                <p>{{ setting('about_hero_text') }}</p>
            </div>
        </div>
    </section>

    @if ($stats = setting('home_stats'))
        <div class="container">
            <div class="facts-card">
                @foreach ($stats as $stat)
                    <div><strong>{{ $stat['value'] }}</strong><span>{{ $stat['label'] }}</span></div>
                @endforeach
            </div>
        </div>
    @endif

    <section class="section" style="padding-top:88px">
        <div class="container split" style="align-items:flex-start">
            <div class="split-text" style="flex-basis:380px">
                <span class="eyebrow">Chúng tôi là ai</span>
                <h2>{{ setting('about_intro_title') }}</h2>
            </div>
            <div class="prose" style="flex:1.4 1 520px">{!! setting('about_intro_body') !!}</div>
        </div>
    </section>

    @if ($pillars = setting('about_pillars'))
        <section class="section section--tight">
            <div class="container pillars">
                @foreach ($pillars as $pillar)
                    <div>
                        <span class="letter"><strong>{{ $pillar['letter'] }}</strong><span>{{ $pillar['en'] }}</span></span>
                        <h3>{{ $pillar['title'] }}</h3>
                        <p>{{ $pillar['text'] }}</p>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    @if (setting('about_spotlight_title'))
        <section class="section section--tight">
            <div class="container">
                <div class="spotlight">
                    <div class="logo-side">
                        <img src="{{ thumb_url(setting('about_spotlight_image'), 640, asset('images/jbe-logo.jpg')) }}" alt="Logo đối tác">
                        <span>{{ setting('about_spotlight_country') }}</span>
                    </div>
                    <div class="text-side">
                        <span class="eyebrow eyebrow--light">{{ setting('about_spotlight_label') }}</span>
                        <h2>{{ setting('about_spotlight_title') }}</h2>
                        <p>{{ setting('about_spotlight_text') }}</p>
                        <a class="underline" href="{{ route('products.index') }}">Xem sản phẩm →</a>
                    </div>
                </div>
            </div>
        </section>
    @endif

    @if ($brands->isNotEmpty())
        <section class="section">
            <div class="container">
                <div class="section-head">
                    <div style="max-width:640px">
                        <span class="eyebrow">Đối tác</span>
                        <h2 style="margin-top:10px">Thương hiệu quốc tế chúng tôi đại diện và phân phối</h2>
                    </div>
                    <p class="muted" style="max-width:420px">Xuất xứ: {{ setting('about_origins') }}</p>
                </div>
                <div class="logo-wall">
                    @foreach ($brands as $brand)
                        <a href="{{ route('products.brand', $brand) }}">
                            @if ($brand->logo)<img src="{{ thumb_url($brand->logo, 320) }}" alt="{{ $brand->name }}" loading="lazy">@else{{ $brand->name }}@endif
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($clients->isNotEmpty())
        <section class="section">
            <div class="container">
                <div class="section-head">
                    <div>
                        <span class="eyebrow">Khách hàng</span>
                        <h2 style="margin-top:10px">Khách hàng tiêu biểu</h2>
                    </div>
                </div>
                <div class="logo-wall">
                    @foreach ($clients as $client)
                        <span>@if ($client->logo)<img src="{{ thumb_url($client->logo, 320) }}" alt="{{ $client->name }}" loading="lazy">@else{{ $client->name }}@endif</span>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($industries = setting('industries'))
        <section class="section">
            <div class="container">
                <div class="section-head"><h2>Ngành công nghiệp chúng tôi phục vụ</h2></div>
                <div class="grid grid-brands">
                    @foreach ($industries as $industry)
                        <div class="card cat-card" style="min-height:0">
                            <span class="icon-tile"><x-ui-icon :name="$industry['icon'] ?? 'factory'" :size="24" :stroke="1.8" /></span>
                            <h3>{{ $industry['name'] }}</h3>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($values = setting('about_values'))
        <section class="section">
            <div class="container split" style="align-items:flex-start">
                <div class="split-text" style="flex-basis:340px">
                    <span class="eyebrow">Cam kết</span>
                    <h2>Đối tác lâu dài và đáng tin cậy</h2>
                </div>
                <ol class="numbered-list" style="flex:1.6 1 560px">
                    @foreach ($values as $value)
                        <li>
                            <span class="n">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                            <span><h3>{{ $value['title'] }}</h3><p>{{ $value['text'] }}</p></span>
                        </li>
                    @endforeach
                </ol>
            </div>
        </section>
    @endif

    @if ($certificates->isNotEmpty())
        <section class="section" id="chung-nhan">
            <div class="container">
                <div class="section-head">
                    <div>
                        <span class="eyebrow">Pháp lý &amp; ủy quyền</span>
                        <h2 style="margin-top:10px">Chứng nhận</h2>
                    </div>
                </div>
                <div class="grid grid-250">
                    @foreach ($certificates as $certificate)
                        <a class="cert" href="{{ media_url($certificate->file ?: $certificate->image) }}" target="_blank" rel="noopener">
                            <span class="paper">@if ($certificate->image)<img src="{{ thumb_url($certificate->image, 640) }}" alt="" loading="lazy">@endif</span>
                            <strong>{{ $certificate->title }}</strong>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section class="section" style="padding-bottom:72px">
        <div class="container">
            <div class="cta-card">
                <div>
                    <h2>Trở thành đối tác của {{ setting('brand_name') }}</h2>
                    <p>Trao đổi với chúng tôi về nhu cầu thiết bị, vật tư và dịch vụ bảo trì của nhà máy.</p>
                </div>
                <div class="cta-actions">
                    <a class="btn btn-white btn-lg" href="{{ route('contact') }}">Liên hệ với chúng tôi</a>
                    <a class="btn btn-ghost-light btn-lg" href="{{ route('services') }}">Xem dịch vụ</a>
                </div>
            </div>
        </div>
    </section>
@endsection
