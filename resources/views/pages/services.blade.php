@extends('layouts.site')

@section('title', __('Dịch vụ kỹ thuật'))
@php
    $hero = \App\Services\ContentBlock::parse(setting('services_hero_content'));
    $benefits = \App\Services\ContentBlock::parse(setting('service_benefits_content'))->sections;
    $steps = \App\Services\ContentBlock::parse(setting('service_steps_content'))->sections;
    $faqs = \App\Services\ContentBlock::parse(setting('service_faqs_content'))->sections;
    $serviceIcons = ['wrench', 'valve', 'heat', 'ring', 'tube', 'pipe'];
@endphp
@section('description', $hero->bodyText())

@push('head')
    <x-json-ld :data="\App\Services\Seo::services($services, collect($faqs)->map(fn (array $faq) => ['question' => $faq['title'], 'answer' => $faq['text']])->all())" />
@endpush

@section('content')
    <section class="page-hero page-hero--photo svc-hero">
        <img src="{{ thumb_url(setting('services_hero_image'), 1600, asset('images/services-hero.jpg')) }}" alt="">
        <div class="container">
            <x-breadcrumb :items="[__('Dịch vụ') => null]" />
            <div class="svc-hero__text">
                @if ($hero->eyebrow)<span class="eyebrow eyebrow--light">{{ $hero->eyebrow }}</span>@endif
                <h1>{{ $hero->title }}</h1>
                {!! $hero->body !!}
                <div class="hero-actions">
                    <a class="btn btn-primary btn-lg" href="{{ lroute('contact', ['chu-de' => 'service', 'tieu-de' => __('Yêu cầu khảo sát dịch vụ kỹ thuật')]) }}">{{ __('Yêu cầu khảo sát') }} <x-ui-icon name="arrow-right" :size="18" /></a>
                    <a class="btn btn-ghost-light btn-lg" href="{{ tel_href(setting('phone')) }}"><x-ui-icon name="phone" :size="18" /> {{ setting('phone') }}</a>
                </div>
            </div>
        </div>
    </section>

    @if ($services->isNotEmpty())
        <div class="container">
            <nav class="svc-overview" aria-label="{{ __('Danh sách dịch vụ') }}">
                @foreach ($services as $service)
                    <a class="svc-overview__item" href="#{{ $service->slug }}">
                        <span class="svc-overview__top">
                            <span class="icon-tile icon-tile--accent"><x-ui-icon :name="$serviceIcons[$loop->index % count($serviceIcons)]" :size="24" :stroke="1.8" /></span>
                            <span class="svc-overview__num">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        </span>
                        <strong>{{ $service->title }}</strong>
                        @if ($service->summary)<span class="svc-overview__summary">{{ $service->summary }}</span>@endif
                        <span class="svc-overview__more">{{ __('Chi tiết') }} <x-ui-icon name="arrow-right" :size="15" style="transform:rotate(90deg)" /></span>
                    </a>
                @endforeach
            </nav>
        </div>
    @endif

    <section class="container svc-details">
        @forelse ($services as $service)
            <article id="{{ $service->slug }}" @class(['svc-detail', 'svc-detail--reverse' => $loop->even])>
                @php($images = $service->images())
                {{-- Links open the full image without JavaScript; site.js shows them in the shared pop-up instead. --}}
                <div class="svc-detail__media" @if ($images->isNotEmpty()) data-lightbox="{{ $images->map(fn (string $image) => thumb_url($image, 1600))->toJson() }}" data-lightbox-title="{{ $service->title }}" @endif>
                    @if ($images->isNotEmpty())
                        <a class="svc-detail__open" href="{{ media_url($images->first()) }}" data-lightbox-index="0" aria-label="{{ __('Xem ảnh lớn') }}: {{ $service->title }}">
                            <img src="{{ thumb_url($images->first(), 960) }}" alt="{{ $service->title }}" loading="lazy">
                            <span class="svc-detail__zoom"><x-ui-icon name="eye" :size="16" /> {{ $images->count() > 1 ? __('Xem :count ảnh', ['count' => $images->count()]) : __('Xem ảnh lớn') }}</span>
                        </a>
                        @if ($images->count() > 1)
                            <ul class="svc-detail__thumbs">
                                @foreach ($images->take(5) as $image)
                                    <li>
                                        <a href="{{ media_url($image) }}" data-lightbox-index="{{ $loop->index }}" aria-label="{{ __('Ảnh') }} {{ $loop->iteration }} / {{ $images->count() }}">
                                            <img src="{{ thumb_url($image, 160) }}" alt="" loading="lazy">
                                            @if ($loop->last && $images->count() > 5)<span class="svc-detail__more">+{{ $images->count() - 5 }}</span>@endif
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    @else
                        <x-ui-icon :name="$serviceIcons[$loop->index % count($serviceIcons)]" :size="96" :stroke="1.2" class="svc-detail__placeholder" />
                    @endif
                    <span class="svc-detail__badge">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                </div>
                <div class="svc-detail__text">
                    <span class="eyebrow">{{ __('Dịch vụ') }} {{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                    <h2>{{ $service->title }}</h2>
                    @if ($service->summary)<p class="svc-detail__lead">{{ $service->summary }}</p>@endif
                    @if ($service->description)<div class="prose svc-detail__prose">{!! $service->description !!}</div>@endif

                    <div class="cta-row">
                        <a class="btn btn-primary" style="flex:0 1 auto" href="{{ lroute('contact', ['chu-de' => 'service', 'tieu-de' => $service->title]) }}">{{ __('Liên hệ về dịch vụ này') }}</a>
                        <a class="btn btn-outline" style="flex:0 1 auto" href="{{ tel_href(setting('phone')) }}"><x-ui-icon name="phone" :size="16" /> {{ __('Gọi tư vấn') }}</a>
                    </div>
                </div>
            </article>
        @empty
            <p class="card empty">{{ __('Nội dung dịch vụ đang được cập nhật.') }}</p>
        @endforelse
    </section>

    @if ($benefits)
        <section class="band-navy svc-benefits">
            <div class="container">
                <div class="section-head">
                    <div style="max-width:640px">
                        <span class="eyebrow eyebrow--light">{{ __('Vì sao chọn :name', ['name' => setting('brand_name')]) }}</span>
                        <h2 style="margin-top:10px">{{ __('Một đối tác cho cả thiết bị, vật tư và kỹ thuật') }}</h2>
                    </div>
                </div>
                <div class="svc-benefits__grid">
                    @foreach (array_slice($benefits, 0, 6) as $benefit)
                        <div class="svc-benefit">
                            <span class="svc-benefit__icon"><x-ui-icon :name="['wrench', 'shield', 'check', 'box', 'handshake', 'clock'][$loop->index % 6]" :size="22" :stroke="1.9" /></span>
                            <h3>{{ $benefit['title'] }}</h3>
                            <p>{{ $benefit['text'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($steps)
        <section class="section">
            <div class="container">
                <div class="section-head">
                    <div>
                        <span class="eyebrow">{{ __('Quy trình') }}</span>
                        <h2 style="margin-top:10px">{{ __('Quy trình làm việc') }}</h2>
                    </div>
                    <p class="muted" style="max-width:420px">{{ __('Từ tiếp nhận yêu cầu đến bàn giao, mỗi bước đều có đầu mối kỹ thuật phụ trách.') }}</p>
                </div>
                <ol class="svc-timeline">
                    @foreach ($steps as $step)
                        <li>
                            <span class="svc-timeline__dot">{{ $loop->iteration }}</span>
                            <strong>{{ $step['title'] }}</strong>
                            <span>{{ $step['text'] }}</span>
                        </li>
                    @endforeach
                </ol>
            </div>
        </section>
    @endif

    @if ($projects->isNotEmpty())
        <section class="section">
            <div class="container">
                <div class="section-head">
                    <div>
                        <span class="eyebrow">{{ __('Dự án') }}</span>
                        <h2 style="margin-top:10px">{{ __('Dịch vụ đã triển khai') }}</h2>
                    </div>
                    <a class="link-more" href="{{ lroute('projects.index') }}">{{ __('Tất cả dự án') }} →</a>
                </div>
                <div class="grid grid-320">
                    @foreach ($projects as $project)
                        <a class="card project-card" href="{{ lroute('projects.show', $project) }}">
                            <span class="cover">
                                @if ($project->cover)<img src="{{ thumb_url($project->cover, 640) }}" alt="" loading="lazy">@endif
                                @if ($project->industry)<span class="label">{{ $project->industry }}</span>@endif
                            </span>
                            <span class="body">
                                <h3>{{ $project->title }}</h3>
                                <span class="muted" style="font-size:14px">{{ collect([$project->client, $project->year])->filter()->implode(' · ') }}</span>
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($faqs)
        <section class="section" style="padding-bottom:72px">
            <div class="container split" style="align-items:flex-start">
                <div class="split-text" style="flex-basis:340px">
                    <span class="eyebrow">{{ __('Hỏi đáp') }}</span>
                    <h2>{{ __('Câu hỏi thường gặp') }}</h2>
                    <p>{{ __('Chưa thấy câu trả lời bạn cần? Gọi') }} <a href="{{ tel_href(setting('phone')) }}"><strong>{{ setting('phone') }}</strong></a> {{ __('hoặc gửi câu hỏi cho chúng tôi.') }}</p>
                    <a class="btn btn-outline" style="align-self:flex-start" href="{{ lroute('contact', ['chu-de' => 'service']) }}">{{ __('Gửi câu hỏi') }}</a>
                </div>
                <div class="faq" style="flex:1.6 1 560px">
                    @foreach ($faqs as $faq)
                        <details class="faq__item" @if ($loop->first) open @endif>
                            <summary>{{ $faq['title'] }}<x-ui-icon name="chevron-right" :size="18" class="faq__chevron" /></summary>
                            <p>{{ $faq['text'] }}</p>
                        </details>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @include('partials.cta-band', ['title' => __('Nhà máy của bạn sắp có đợt bảo trì?'), 'text' => __('Liên hệ sớm để được khảo sát và chuẩn bị thiết bị, vật tư phù hợp.')])
    @include('partials.lightbox')
@endsection
