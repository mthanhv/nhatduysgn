@extends('layouts.site')

@section('title', __('Giới thiệu'))
@php
    $hero = \App\Services\ContentBlock::parse(setting('about_hero_content'));
    $intro = \App\Services\ContentBlock::parse(setting('about_intro_content'));
    $stats = \App\Services\ContentBlock::parse(setting('home_stats_content'))->items;
    $industries = \App\Services\ContentBlock::parse(setting('industries_content'))->items;
    $pillars = \App\Services\ContentBlock::parse(setting('about_pillars_content'))->sections;
    $values = \App\Services\ContentBlock::parse(setting('about_values_content'))->sections;
    // Industry icons follow the industry's name, in either language.
    $industryIcon = fn (string $name): string => collect([
        'oil' => ['dầu', 'khí', 'oil', 'gas', 'petro'], 'bolt' => ['điện', 'power', 'energy', 'năng lượng'], 'flask' => ['hóa', 'chemi'],
        'food' => ['thực phẩm', 'food', 'beverage'], 'wrench' => ['cơ khí', 'mechanic'],
    ])->search(fn (array $words) => \Illuminate\Support\Str::contains(mb_strtolower($name), $words)) ?: 'factory';
@endphp
@section('description', $hero->bodyText())

@section('content')
    <section class="page-hero page-hero--photo">
        <img src="{{ thumb_url(setting('about_hero_image'), 1600, asset('images/about-hero.jpg')) }}" alt="">
        <div class="container">
            <x-breadcrumb :items="[__('Giới thiệu') => null]" />
            <div style="display:flex;flex-direction:column;gap:18px;max-width:680px">
                <span class="eyebrow eyebrow--light">{{ __('Về :name', ['name' => setting('brand_name')]) }}</span>
                <h1>{{ $hero->title }}</h1>
                {!! $hero->body !!}
            </div>
        </div>
    </section>

    @if ($stats)
        <div class="container">
            <div class="facts-card">
                @foreach (array_slice($stats, 0, 4) as $stat)
                    <div><strong>{{ $stat['title'] }}</strong><span>{{ $stat['text'] }}</span></div>
                @endforeach
            </div>
        </div>
    @endif

    <section class="section" style="padding-top:88px">
        <div class="container split" style="align-items:flex-start">
            <div class="split-text" style="flex-basis:380px">
                <span class="eyebrow">{{ __('Chúng tôi là ai') }}</span>
                <h2>{{ $intro->title }}</h2>
            </div>
            <div class="prose" style="flex:1.4 1 520px">{!! $intro->body !!}@if ($intro->items)<ul>@foreach ($intro->items as $item)<li>@if ($item['title'])<strong>{{ $item['title'] }}</strong> @endif{{ $item['text'] }}</li>@endforeach</ul>@endif</div>
        </div>
    </section>

    @if ($pillars)
        <section class="section section--tight">
            <div class="container pillars">
                @foreach ($pillars as $pillar)
                    {{-- "Bảo trì (Maintenance)": the big letter comes from the word in brackets, else from the title. --}}
                    @php
                        [$pillarTitle, $pillarWord] = preg_match('/^(.*?)\s*\(([^)]+)\)\s*$/u', $pillar['title'], $match) ? [$match[1], $match[2]] : [$pillar['title'], null];
                    @endphp
                    <div>
                        <span class="letter"><strong>{{ mb_strtoupper(mb_substr($pillarWord ?? $pillarTitle, 0, 1)) }}</strong>@if ($pillarWord)<span>{{ $pillarWord }}</span>@endif</span>
                        <h3>{{ $pillarTitle }}</h3>
                        <p>{{ $pillar['text'] }}</p>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    @if ($brands->isNotEmpty())
        <section class="section">
            <div class="container">
                <div class="section-head">
                    <div style="max-width:640px">
                        <span class="eyebrow">{{ __('Đối tác') }}</span>
                        <h2 style="margin-top:10px">{{ __('Thương hiệu quốc tế chúng tôi đại diện và phân phối') }}</h2>
                    </div>
                    <p class="muted" style="max-width:420px">{{ __('Xuất xứ') }}: {{ setting('about_origins') }}</p>
                </div>
                <div class="logo-wall">
                    @foreach ($brands as $brand)
                        <a href="{{ lroute('products.brand', $brand) }}">
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
                        <span class="eyebrow">{{ __('Khách hàng') }}</span>
                        <h2 style="margin-top:10px">{{ __('Khách hàng tiêu biểu') }}</h2>
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

    @if ($industries)
        <section class="section">
            <div class="container">
                <div class="section-head"><h2>{{ __('Ngành công nghiệp chúng tôi phục vụ') }}</h2></div>
                <div class="grid grid-brands">
                    @foreach ($industries as $industry)
                        <div class="card cat-card" style="min-height:0">
                            @php
                                $industryName = trim(($industry['title'] ? $industry['title'].' ' : '').$industry['text']);
                            @endphp
                            <span class="icon-tile"><x-ui-icon :name="$industryIcon($industryName)" :size="24" :stroke="1.8" /></span>
                            <h3>{{ $industryName }}</h3>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($values)
        <section class="section">
            <div class="container split" style="align-items:flex-start">
                <div class="split-text" style="flex-basis:340px">
                    <span class="eyebrow">{{ __('Cam kết') }}</span>
                    <h2>{{ __('Đối tác lâu dài và đáng tin cậy') }}</h2>
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
                        <span class="eyebrow">{{ __('Pháp lý & ủy quyền') }}</span>
                        <h2 style="margin-top:10px">{{ __('Chứng nhận') }}</h2>
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
                    <h2>{{ __('Trở thành đối tác của :name', ['name' => setting('brand_name')]) }}</h2>
                    <p>{{ __('Trao đổi với chúng tôi về nhu cầu thiết bị, vật tư và dịch vụ bảo trì của nhà máy.') }}</p>
                </div>
                <div class="cta-actions">
                    <a class="btn btn-white btn-lg" href="{{ lroute('contact') }}">{{ __('Liên hệ với chúng tôi') }}</a>
                    <a class="btn btn-ghost-light btn-lg" href="{{ lroute('services') }}">{{ __('Xem dịch vụ') }}</a>
                </div>
            </div>
        </div>
    </section>
@endsection
