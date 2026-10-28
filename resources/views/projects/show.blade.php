@extends('layouts.site')
@unless ($project->isTranslatedInto('en'))
    @section('untranslated', '1')
@endunless

@section('title', $project->ownValue('meta_title') ?: $project->title)
@section('description', \App\Services\Seo::projectDescription($project))
@section('og_type', 'article')

@push('head')
    <x-json-ld :data="\App\Services\Seo::project($project)" />
@endpush
@if ($project->cover)
    @section('og_image', media_url($project->cover))
@endif

@section('content')
    <div class="container article-head">
        <x-breadcrumb :items="[__('Dự án') => lroute('projects.index'), $project->title => null]" />
        @if ($project->industry)<span class="eyebrow">{{ $project->industry }}</span>@endif
        <h1>{{ $project->title }}</h1>
        <div class="muted">{{ collect([$project->client, $project->year])->filter()->implode(' · ') }}</div>
    </div>
    @php($images = $project->images())
    {{-- Links open the full image without JavaScript; site.js shows them in the shared pop-up instead. --}}
    <div @if ($images->isNotEmpty()) data-lightbox="{{ $images->map(fn (string $image) => thumb_url($image, 1600))->toJson() }}" data-lightbox-title="{{ $project->title }}" @endif>
    @if ($project->cover)
        <div class="container"><figure class="article-cover" style="margin:0"><a class="article-cover__open" href="{{ media_url($project->cover) }}" data-lightbox-index="0" aria-label="{{ __('Xem ảnh lớn') }}"><img src="{{ thumb_url($project->cover, 1280) }}" alt="{{ $project->title }}"></a></figure></div>
    @endif
    <div class="container article-layout">
        <article>
            @if ($project->summary)<p style="font-size:20px;line-height:1.7;margin-bottom:24px">{{ $project->summary }}</p>@endif
            <div class="prose">{!! $project->content !!}</div>
            @if ($images->count() > 1)
                <section class="project-gallery" aria-labelledby="project-gallery-heading">
                    <h2 id="project-gallery-heading">{{ __('Hình ảnh dự án') }} <span class="muted">({{ $images->count() }})</span></h2>
                    <ul>
                        @foreach ($images as $image)
                            <li><a href="{{ media_url($image) }}" data-lightbox-index="{{ $loop->index }}" aria-label="{{ __('Ảnh') }} {{ $loop->iteration }} / {{ $images->count() }}"><img src="{{ thumb_url($image, 640) }}" alt="{{ $project->title }} – {{ __('ảnh') }} {{ $loop->iteration }}" loading="lazy"></a></li>
                        @endforeach
                    </ul>
                </section>
            @endif
            @if ($project->tags)
                <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:28px">@foreach ($project->tags as $tag)<span class="pill">{{ $tag }}</span>@endforeach</div>
            @endif
        </article>
        <aside>
            <div class="service-note" style="flex-direction:column;align-items:flex-start;padding:24px">
                <strong style="font-size:18px">{{ __('Cần giải pháp tương tự?') }}</strong>
                <p>{{ __('Đội ngũ kỹ thuật :name sẵn sàng khảo sát và tư vấn.', ['name' => setting('brand_name')]) }}</p>
                <a class="btn btn-white" href="{{ lroute('contact', ['chu-de' => 'service', 'tieu-de' => __('Dự án tương tự').': '.$project->title]) }}">{{ __('Liên hệ tư vấn') }}</a>
            </div>
        </aside>
    </div>
    </div>
    @if ($others->isNotEmpty())
        <section class="container" style="padding-bottom:72px">
            <div class="section-head"><h2>{{ __('Dự án khác') }}</h2></div>
            <div class="grid grid-320">
                @foreach ($others as $other)
                    <a class="card post-card" href="{{ lroute('projects.show', $other) }}">
                        <span class="cover">@if ($other->cover)<img src="{{ thumb_url($other->cover, 640) }}" alt="" loading="lazy">@endif</span>
                        <span class="body"><span class="meta">{{ $other->industry }}</span><h3>{{ $other->title }}</h3></span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif
    @includeWhen($images->isNotEmpty(), 'partials.lightbox')
@endsection
