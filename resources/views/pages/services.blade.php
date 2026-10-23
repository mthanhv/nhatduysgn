@extends('layouts.site')

@section('title', 'Dịch vụ kỹ thuật')
@section('description', setting('services_intro'))

@section('content')
    <section class="page-hero page-hero--navy">
        <div class="container">
            <x-breadcrumb :items="['Dịch vụ' => null]" />
            <h1>Dịch vụ kỹ thuật tại hiện trường</h1>
            <p>{{ setting('services_intro') }}</p>
        </div>
    </section>

    <section class="section" style="padding-top:56px">
        <div class="container" style="display:flex;flex-direction:column;gap:24px">
            @forelse ($services as $service)
                <article id="{{ $service->slug }}" class="card service-row">
                    <div class="media">
                        @if ($service->image)
                            <img src="{{ media_url($service->image) }}" alt="{{ $service->title }}" loading="lazy">
                        @else
                            <x-ui-icon name="wrench" :size="64" :stroke="1.4" style="color:#9aa5b0" />
                        @endif
                    </div>
                    <div class="text">
                        <span class="mono" style="color:var(--accent)">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        <h2>{{ $service->title }}</h2>
                        @if ($service->summary)<p class="muted">{{ $service->summary }}</p>@endif
                        @if ($service->description)<div class="prose">{!! $service->description !!}</div>@endif
                        @if ($service->equipment)
                            <div style="display:flex;flex-wrap:wrap;gap:8px;align-items:center">
                                <span class="eyebrow" style="color:var(--muted)">Thiết bị sử dụng</span>
                                @foreach ($service->equipment as $equipment)<span class="tag">{{ $equipment }}</span>@endforeach
                            </div>
                        @endif
                        <div class="cta-row" style="margin-top:8px">
                            <a class="btn btn-primary" style="flex:0 1 auto" href="{{ route('contact', ['chu-de' => 'service', 'tieu-de' => $service->title]) }}">Liên hệ về dịch vụ này</a>
                        </div>
                    </div>
                </article>
            @empty
                <p class="card empty">Nội dung dịch vụ đang được cập nhật.</p>
            @endforelse
        </div>
    </section>

    @if ($steps = setting('service_steps'))
        <section class="section" style="padding-bottom:72px">
            <div class="container">
                <div class="section-head"><h2>Quy trình làm việc</h2></div>
                <ol class="steps">
                    @foreach ($steps as $step)
                        <li class="card">
                            <span class="step-num">{{ $loop->iteration }}</span>
                            <strong style="font-size:17px">{{ $step['title'] }}</strong>
                            <span class="muted" style="font-size:14px">{{ $step['text'] }}</span>
                        </li>
                    @endforeach
                </ol>
            </div>
        </section>
    @endif

    @include('partials.cta-band', ['title' => 'Nhà máy của bạn sắp có đợt bảo trì?', 'text' => 'Liên hệ để được khảo sát và tư vấn phương án.'])
@endsection
