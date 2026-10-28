@extends('layouts.site')
@unless ($post->isTranslatedInto('en'))
    @section('untranslated', '1')
@endunless

@section('title', $post->ownValue('meta_title') ?: $post->title)
@section('description', \App\Services\Seo::postDescription($post))
@section('og_type', 'article')
@if ($post->cover)
    @section('og_image', media_url($post->cover))
@endif

@push('head')
    <meta property="article:published_time" content="{{ $post->published_at?->toAtomString() }}">
    <meta property="article:modified_time" content="{{ $post->updated_at?->toAtomString() }}">
    <x-json-ld :data="\App\Services\Seo::article($post)" />
@endpush

@section('content')
    <div class="container article-head">
        <x-breadcrumb :items="[__('Tin tức') => lroute('posts.index'), $post->title => null]" />
        <h1>{{ $post->title }}</h1>
        @include('posts.partials.meta', ['post' => $post])
    </div>
    @if ($post->cover)
        <div class="container"><figure class="article-cover" style="margin:0"><img src="{{ thumb_url($post->cover, 1280) }}" alt="{{ $post->title }}"></figure></div>
    @endif
    <div class="container article-layout">
        <article>
            @if ($post->has_distinct_excerpt)<p class="article-lead">{{ $post->excerpt }}</p>@endif
            <div class="prose">{!! $post->content !!}</div>
            <div class="share">
                <span>{{ __('Chia sẻ') }}:</span>
                <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}" target="_blank" rel="noopener">Facebook</a>
                <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode(url()->current()) }}" target="_blank" rel="noopener">LinkedIn</a>
                <a href="mailto:?subject={{ rawurlencode($post->title) }}&body={{ rawurlencode(url()->current()) }}"><x-ui-icon name="mail" :size="16" /> Email</a>
                <button type="button" data-copy-link="{{ url()->current() }}"><x-ui-icon name="link" :size="16" /> <span data-copy-label>{{ __('Sao chép liên kết') }}</span></button>
            </div>

            @if ($newer || $older)
                <nav class="post-nav" aria-label="{{ __('Bài viết trước và sau') }}">
                    @if ($older)
                        <a class="card" href="{{ lroute('posts.show', $older) }}" rel="prev"><small><x-ui-icon name="arrow-left" :size="14" /> {{ __('Bài trước') }}</small><strong>{{ $older->title }}</strong></a>
                    @endif
                    @if ($newer)
                        <a class="card next" href="{{ lroute('posts.show', $newer) }}" rel="next"><small>{{ __('Bài sau') }} <x-ui-icon name="arrow-right" :size="14" /></small><strong>{{ $newer->title }}</strong></a>
                    @endif
                </nav>
            @endif

            <p style="margin-top:28px"><a class="link-more" href="{{ lroute('posts.index') }}">← {{ __('Quay lại Tin tức') }}</a></p>
        </article>
        <aside class="news-aside" aria-label="{{ __('Thanh bên') }}">
            @if ($related->isNotEmpty())
                <div class="card news-widget">
                    <h2>{{ __('Bài viết khác') }}</h2>
                    <ol class="popular-list">
                        @foreach ($related as $item)
                            <li>
                                <span class="popular-list__n">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                <span>
                                    <a href="{{ lroute('posts.show', $item) }}">{{ $item->title }}</a>
                                    <small>{{ $item->published_at?->format('d/m/Y') }}</small>
                                </span>
                            </li>
                        @endforeach
                    </ol>
                    <a class="link-more" style="font-size:14px" href="{{ lroute('posts.index') }}">{{ __('Tất cả tin tức') }} →</a>
                </div>
            @endif
            <div class="news-widget news-widget--cta">
                <span class="icon-tile"><x-ui-icon name="phone" :size="22" :stroke="1.9" /></span>
                <h2>{{ __('Cần tư vấn kỹ thuật?') }}</h2>
                <p>{{ __('Đội ngũ kỹ sư :name sẵn sàng hỗ trợ chọn thiết bị và giải pháp bảo trì.', ['name' => setting('brand_name')]) }}</p>
                <a class="btn btn-white" href="{{ lroute('contact', ['chu-de' => 'product', 'tieu-de' => __('Liên quan bài viết').': '.$post->title]) }}">{{ __('Gửi liên hệ') }}</a>
                <a class="news-widget__phone" href="{{ tel_href(setting('phone')) }}">{{ setting('phone') }}</a>
            </div>
        </aside>
    </div>
@endsection
