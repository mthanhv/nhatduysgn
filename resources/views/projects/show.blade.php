@extends('layouts.site')

@section('title', $project->meta_title ?: $project->title)
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
        <x-breadcrumb :items="['Dự án' => route('projects.index'), $project->title => null]" />
        @if ($project->industry)<span class="eyebrow">{{ $project->industry }}</span>@endif
        <h1>{{ $project->title }}</h1>
        <div class="muted">{{ collect([$project->client, $project->year])->filter()->implode(' · ') }}</div>
    </div>
    @if ($project->cover)
        <div class="container"><figure class="article-cover" style="margin:0"><img src="{{ thumb_url($project->cover, 1280) }}" alt="{{ $project->title }}"></figure></div>
    @endif
    <div class="container article-layout">
        <article>
            @if ($project->summary)<p style="font-size:20px;line-height:1.7;margin-bottom:24px">{{ $project->summary }}</p>@endif
            <div class="prose">{!! $project->content !!}</div>
            @if ($project->tags)
                <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:28px">@foreach ($project->tags as $tag)<span class="pill">{{ $tag }}</span>@endforeach</div>
            @endif
        </article>
        <aside>
            <div class="service-note" style="flex-direction:column;align-items:flex-start;padding:24px">
                <strong style="font-size:18px">Cần giải pháp tương tự?</strong>
                <p>Đội ngũ kỹ thuật {{ setting('brand_name') }} sẵn sàng khảo sát và tư vấn.</p>
                <a class="btn btn-white" href="{{ route('contact', ['chu-de' => 'service', 'tieu-de' => 'Dự án tương tự: '.$project->title]) }}">Liên hệ tư vấn</a>
            </div>
        </aside>
    </div>
    @if ($others->isNotEmpty())
        <section class="container" style="padding-bottom:72px">
            <div class="section-head"><h2>Dự án khác</h2></div>
            <div class="grid grid-320">
                @foreach ($others as $other)
                    <a class="card post-card" href="{{ route('projects.show', $other) }}">
                        <span class="cover">@if ($other->cover)<img src="{{ thumb_url($other->cover, 640) }}" alt="" loading="lazy">@endif</span>
                        <span class="body"><span class="meta">{{ $other->industry }}</span><h3>{{ $other->title }}</h3></span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif
@endsection
