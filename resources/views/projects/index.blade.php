@extends('layouts.site')

@section('title', __('Dự án'))
@section('description', __('Dự án tiêu biểu của :company tại các nhà máy dầu khí, điện, đạm, hóa chất và thực phẩm.', ['company' => setting('company_name')]))

@section('content')
    <section class="page-hero page-hero--photo" style="min-height:380px">
        <img src="{{ asset('images/about-hero.jpg') }}" alt="">
        <div class="container" style="padding-bottom:48px">
            <x-breadcrumb :items="[__('Dự án') => null]" />
            <div style="display:flex;flex-direction:column;gap:14px;max-width:760px">
                <h1>{{ __('Dự án tiêu biểu') }}</h1>
                <p>{{ __('Thiết bị và dịch vụ kỹ thuật của :name tại các nhà máy dầu khí, điện, đạm, hóa chất và thực phẩm.', ['name' => setting('brand_name')]) }}</p>
            </div>
        </div>
    </section>

    <section class="section" style="padding-top:32px;padding-bottom:64px">
        <div class="container" style="display:flex;flex-direction:column;gap:24px">
            @if ($industries->count() > 1)
                <nav class="chips" aria-label="{{ __('Lọc theo ngành') }}">
                    <a href="{{ lroute('projects.index') }}" @if (! $currentIndustry) aria-current="page" @endif>{{ __('Tất cả') }}</a>
                    @foreach ($industries as $industry)
                        <a href="{{ lroute('projects.index', ['nganh' => $industry]) }}" @if ($currentIndustry === $industry) aria-current="page" @endif>{{ $industry }}</a>
                    @endforeach
                </nav>
            @endif

            @if ($projects->isEmpty())
                <p class="card empty">{{ __('Nội dung dự án đang được cập nhật.') }}</p>
            @else
                <div class="grid grid-320">
                    @foreach ($projects as $project)
                        <article class="card project-card">
                            <a class="cover" href="{{ lroute('projects.show', $project) }}" tabindex="-1" aria-hidden="true">
                                @if ($project->cover)<img src="{{ thumb_url($project->cover, 640) }}" alt="" loading="lazy">@endif
                                @if ($project->industry)<span class="label">{{ $project->industry }}</span>@endif
                            </a>
                            <div class="body">
                                <h3><a href="{{ lroute('projects.show', $project) }}">{{ $project->title }}</a></h3>
                                <span class="muted" style="font-size:14px">{{ collect([$project->client, $project->year])->filter()->implode(' · ') }}</span>
                                @if ($project->tags)
                                    <div style="display:flex;flex-wrap:wrap;gap:6px">@foreach ($project->tags as $tag)<span class="pill">{{ $tag }}</span>@endforeach</div>
                                @endif
                                <a class="link-more" style="margin-top:auto;padding-top:8px;font-size:14px" href="{{ lroute('projects.show', $project) }}">{{ __('Xem dự án') }} →</a>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    @include('partials.cta-band', ['title' => __('Nhà máy của bạn có dự án bảo trì sắp tới?'), 'text' => __('Chia sẻ phạm vi công việc, đội ngũ kỹ thuật sẽ khảo sát và đề xuất giải pháp.')])
@endsection
