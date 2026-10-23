@extends('layouts.site')

@section('title', $post->meta_title ?: $post->title)
@section('description', $post->meta_description ?: ($post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags((string) $post->content), 160)))
@if ($post->cover)
    @section('og_image', media_url($post->cover))
@endif

@push('head')
    <script type="application/ld+json">{!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'Article',
        'headline' => $post->title,
        'datePublished' => $post->published_at?->toAtomString(),
        'dateModified' => $post->updated_at?->toAtomString(),
        'image' => $post->cover ? media_url($post->cover) : null,
        'publisher' => ['@type' => 'Organization', 'name' => setting('company_name')],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
@endpush

@section('content')
    <div class="container article-head">
        <x-breadcrumb :items="['Tin tức' => route('posts.index'), $post->title => null]" />
        <h1>{{ $post->title }}</h1>
        <div class="muted" style="font-size:14px">Đăng ngày {{ $post->published_at?->format('d/m/Y') }} · {{ setting('brand_name') }}</div>
    </div>
    @if ($post->cover)
        <div class="container"><figure class="article-cover" style="margin:0"><img src="{{ media_url($post->cover) }}" alt="{{ $post->title }}"></figure></div>
    @endif
    <div class="container article-layout">
        <article>
            @if ($post->excerpt)<p style="font-size:20px;line-height:1.7;margin-bottom:24px;font-weight:500">{{ $post->excerpt }}</p>@endif
            <div class="prose">{!! $post->content !!}</div>
            <p style="margin-top:32px"><a class="link-more" href="{{ route('posts.index') }}">← Quay lại Tin tức</a></p>
        </article>
        <aside>
            @if ($featuredProduct)
                <x-product-card :product="$featuredProduct" />
            @endif
            <div class="service-note" style="flex-direction:column;align-items:flex-start;padding:24px">
                <strong style="font-size:18px">Cần tư vấn kỹ thuật?</strong>
                <p>Đội ngũ {{ setting('brand_name') }} sẵn sàng hỗ trợ.</p>
                <a class="btn btn-white" href="{{ route('contact') }}">Liên hệ</a>
            </div>
        </aside>
    </div>
    @if ($related->isNotEmpty())
        <section style="background:#fff;border-top:1px solid var(--line)">
            <div class="container" style="padding-top:48px;padding-bottom:64px">
                <div class="section-head"><h2>Bài viết khác</h2></div>
                <div class="grid grid-320">
                    @foreach ($related as $item)
                        <a href="{{ route('posts.show', $item) }}" style="display:flex;gap:16px;align-items:center">
                            <span style="width:140px;height:100px;flex:none;border-radius:8px;overflow:hidden;background:#dde3e8">@if ($item->cover)<img src="{{ media_url($item->cover) }}" alt="" style="width:100%;height:100%;object-fit:cover" loading="lazy">@endif</span>
                            <span style="display:flex;flex-direction:column;gap:6px"><span class="muted" style="font-size:12px">{{ $item->published_at?->format('d/m/Y') }}</span><strong style="line-height:1.4">{{ $item->title }}</strong></span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
