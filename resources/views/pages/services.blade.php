@extends('layouts.site')

@section('title', 'Dịch vụ kỹ thuật')
@section('description', setting('services_intro'))

@php
    $serviceIcons = ['wrench', 'valve', 'heat', 'ring', 'tube', 'pipe'];
@endphp

@section('content')
    <section class="page-hero page-hero--photo svc-hero">
        <img src="{{ media_url(setting('services_hero_image'), asset('images/services-hero.jpg')) }}" alt="">
        <div class="container">
            <x-breadcrumb :items="['Dịch vụ' => null]" />
            <div class="svc-hero__text">
                <span class="eyebrow eyebrow--light">Dịch vụ kỹ thuật tại hiện trường</span>
                <h1>Đúng thiết bị, đúng quy trình, ngay tại nhà máy của bạn</h1>
                <p>{{ setting('services_intro') }}</p>
                <div class="hero-actions">
                    <a class="btn btn-primary btn-lg" href="{{ route('contact', ['chu-de' => 'service', 'tieu-de' => 'Yêu cầu khảo sát dịch vụ kỹ thuật']) }}">Yêu cầu khảo sát <x-ui-icon name="arrow-right" :size="18" /></a>
                    <a class="btn btn-ghost-light btn-lg" href="{{ tel_href(setting('phone')) }}"><x-ui-icon name="phone" :size="18" /> {{ setting('phone') }}</a>
                </div>
            </div>
        </div>
    </section>

    @if ($services->isNotEmpty())
        <div class="container">
            <nav class="svc-overview" aria-label="Danh sách dịch vụ">
                @foreach ($services as $service)
                    <a class="svc-overview__item" href="#{{ $service->slug }}">
                        <span class="svc-overview__top">
                            <span class="icon-tile icon-tile--accent"><x-ui-icon :name="$serviceIcons[$loop->index % count($serviceIcons)]" :size="24" :stroke="1.8" /></span>
                            <span class="svc-overview__num">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        </span>
                        <strong>{{ $service->title }}</strong>
                        @if ($service->summary)<span class="svc-overview__summary">{{ $service->summary }}</span>@endif
                        <span class="svc-overview__more">Chi tiết <x-ui-icon name="arrow-right" :size="15" style="transform:rotate(90deg)" /></span>
                    </a>
                @endforeach
            </nav>
        </div>
    @endif

    <section class="container svc-details">
        @forelse ($services as $service)
            <article id="{{ $service->slug }}" @class(['svc-detail', 'svc-detail--reverse' => $loop->even])>
                <div class="svc-detail__media">
                    @if ($service->image)
                        <img src="{{ media_url($service->image) }}" alt="{{ $service->title }}" loading="lazy">
                    @else
                        <x-ui-icon :name="$serviceIcons[$loop->index % count($serviceIcons)]" :size="96" :stroke="1.2" class="svc-detail__placeholder" />
                    @endif
                    <span class="svc-detail__badge">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                </div>
                <div class="svc-detail__text">
                    <span class="eyebrow">Dịch vụ {{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                    <h2>{{ $service->title }}</h2>
                    @if ($service->summary)<p class="svc-detail__lead">{{ $service->summary }}</p>@endif
                    @if ($service->description)<div class="prose svc-detail__prose">{!! $service->description !!}</div>@endif

                    @if ($brandsByService[$service->id]->isNotEmpty() || $service->equipment)
                        <div class="svc-detail__block">
                            <h3>Thiết bị sử dụng</h3>
                            <div class="svc-brands">
                                @forelse ($brandsByService[$service->id] as $brand)
                                    <a class="svc-brand" href="{{ route('products.brand', $brand) }}" title="Sản phẩm {{ $brand->name }}">
                                        @if ($brand->logo)<img src="{{ media_url($brand->logo) }}" alt="{{ $brand->name }}" loading="lazy">@else{{ $brand->name }}@endif
                                    </a>
                                @empty
                                    @foreach ($service->equipment as $equipment)<span class="tag">{{ $equipment }}</span>@endforeach
                                @endforelse
                            </div>
                        </div>
                    @endif

                    @if ($productsByService[$service->id]->isNotEmpty())
                        <div class="svc-detail__block">
                            <h3>Thiết bị liên quan</h3>
                            <ul class="svc-products">
                                @foreach ($productsByService[$service->id] as $product)
                                    <li>
                                        <a href="{{ route('products.show', $product) }}">
                                            <span class="svc-products__thumb"><img src="{{ media_url($product->image) }}" alt="" loading="lazy"></span>
                                            <span class="svc-products__name">{{ $product->name }}</span>
                                            <x-ui-icon name="chevron-right" :size="16" />
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="cta-row">
                        <a class="btn btn-primary" style="flex:0 1 auto" href="{{ route('contact', ['chu-de' => 'service', 'tieu-de' => $service->title]) }}">Liên hệ về dịch vụ này</a>
                        <a class="btn btn-outline" style="flex:0 1 auto" href="{{ tel_href(setting('phone')) }}"><x-ui-icon name="phone" :size="16" /> Gọi tư vấn</a>
                    </div>
                </div>
            </article>
        @empty
            <p class="card empty">Nội dung dịch vụ đang được cập nhật.</p>
        @endforelse
    </section>

    @if ($benefits = setting('service_benefits'))
        <section class="band-navy svc-benefits">
            <div class="container">
                <div class="section-head">
                    <div style="max-width:640px">
                        <span class="eyebrow eyebrow--light">Vì sao chọn {{ setting('brand_name') }}</span>
                        <h2 style="margin-top:10px">Một đối tác cho cả thiết bị, vật tư và kỹ thuật</h2>
                    </div>
                </div>
                <div class="svc-benefits__grid">
                    @foreach ($benefits as $benefit)
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

    @if ($steps = setting('service_steps'))
        <section class="section">
            <div class="container">
                <div class="section-head">
                    <div>
                        <span class="eyebrow">Quy trình</span>
                        <h2 style="margin-top:10px">Quy trình làm việc</h2>
                    </div>
                    <p class="muted" style="max-width:420px">Từ tiếp nhận yêu cầu đến bàn giao, mỗi bước đều có đầu mối kỹ thuật phụ trách.</p>
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
                        <span class="eyebrow">Dự án</span>
                        <h2 style="margin-top:10px">Dịch vụ đã triển khai</h2>
                    </div>
                    <a class="link-more" href="{{ route('projects.index') }}">Tất cả dự án →</a>
                </div>
                <div class="grid grid-320">
                    @foreach ($projects as $project)
                        <a class="card project-card" href="{{ route('projects.show', $project) }}">
                            <span class="cover">
                                @if ($project->cover)<img src="{{ media_url($project->cover) }}" alt="" loading="lazy">@endif
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

    @if ($faqs = setting('service_faqs'))
        <section class="section" style="padding-bottom:72px">
            <div class="container split" style="align-items:flex-start">
                <div class="split-text" style="flex-basis:340px">
                    <span class="eyebrow">Hỏi đáp</span>
                    <h2>Câu hỏi thường gặp</h2>
                    <p>Chưa thấy câu trả lời bạn cần? Gọi <a href="{{ tel_href(setting('phone')) }}"><strong>{{ setting('phone') }}</strong></a> hoặc gửi câu hỏi cho chúng tôi.</p>
                    <a class="btn btn-outline" style="align-self:flex-start" href="{{ route('contact', ['chu-de' => 'service']) }}">Gửi câu hỏi</a>
                </div>
                <div class="faq" style="flex:1.6 1 560px">
                    @foreach ($faqs as $faq)
                        <details class="faq__item" @if ($loop->first) open @endif>
                            <summary>{{ $faq['question'] }}<x-ui-icon name="chevron-right" :size="18" class="faq__chevron" /></summary>
                            <p>{{ $faq['answer'] }}</p>
                        </details>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @include('partials.cta-band', ['title' => 'Nhà máy của bạn sắp có đợt bảo trì?', 'text' => 'Liên hệ sớm để được khảo sát và chuẩn bị thiết bị, vật tư phù hợp.'])
@endsection
