@extends('layouts.site')

@section('title', $term !== '' ? __('Tìm tin tức').': '.$term : __('Tin tức'))
@section('description', __('Tin tức ngành công nghiệp, năng lượng và hoạt động của :company.', ['company' => setting('company_name')]))
@if ($term !== '')
    @section('robots', 'noindex, follow')
@endif

@section('content')
    <section class="page-hero page-hero--grid">
        <div class="container news-hero">
            <x-breadcrumb :items="[__('Tin tức') => null]" />
            <div class="news-hero__row">
                <div class="news-hero__text">
                    <span class="eyebrow eyebrow--light">{{ __('Tin tức & kiến thức') }}</span>
                    <h1>{{ __('Tin tức') }}</h1>
                    <p>{{ __('Cập nhật ngành dầu khí, năng lượng, bảo trì công nghiệp và hoạt động của :name.', ['name' => setting('brand_name')]) }}</p>
                </div>
                <div class="news-hero__count">
                    <strong>{{ $total }}</strong>
                    <span>{{ __('bài viết') }}</span>
                </div>
            </div>
        </div>
    </section>

    @if ($featured)
        <section class="container news-featured-wrap">
            <article class="news-featured">
                <a class="news-featured__media" href="{{ lroute('posts.show', $featured) }}" tabindex="-1" aria-hidden="true">
                    @if ($featured->cover)
                        <img src="{{ thumb_url($featured->cover, 960) }}" alt="" fetchpriority="high">
                    @endif
                    <span class="news-badge">{{ __('Mới nhất') }}</span>
                </a>
                <div class="news-featured__body">
                    @include('posts.partials.meta', ['post' => $featured])
                    <h2><a href="{{ lroute('posts.show', $featured) }}">{{ $featured->title }}</a></h2>
                    @if ($featured->excerpt)
                        <p class="news-featured__excerpt">{{ $featured->excerpt }}</p>
                    @endif
                    <a class="btn btn-primary" href="{{ lroute('posts.show', $featured) }}">{{ __('Đọc bài viết') }} <x-ui-icon name="arrow-right" :size="18" /></a>
                </div>
            </article>
        </section>
    @endif

    <div class="container news-layout">
        <section class="news-main" aria-labelledby="news-list-heading">
            <div class="news-main__head">
                <h2 id="news-list-heading">{{ $term !== '' ? __('Kết quả cho “:term”', ['term' => $term]) : ($featured ? __('Bài viết khác') : __('Tất cả bài viết')) }}</h2>
                @if ($term !== '')
                    <a class="link-more" href="{{ lroute('posts.index') }}">{{ __('Xóa tìm kiếm') }}</a>
                @endif
            </div>

            @if ($gridPosts->isEmpty() && ! $featured)
                <div class="card empty news-empty">
                    <x-ui-icon name="search" :size="36" :stroke="1.6" />
                    @if ($term !== '')
                        <p>{{ __('Không tìm thấy bài viết phù hợp với “:term”.', ['term' => $term]) }}</p>
                        <a class="btn btn-outline" href="{{ lroute('posts.index') }}">{{ __('Xem tất cả bài viết') }}</a>
                    @else
                        <p>{{ __('Chưa có bài viết nào. Vui lòng quay lại sau.') }}</p>
                    @endif
                </div>
            @elseif ($gridPosts->isEmpty())
                <p class="muted">{{ __('Đây là bài viết duy nhất hiện có.') }}</p>
            @else
                <div class="news-grid">
                    @foreach ($gridPosts as $post)
                        <article class="card news-card">
                            <a class="news-card__media" href="{{ lroute('posts.show', $post) }}" tabindex="-1" aria-hidden="true">
                                @if ($post->cover)
                                    <img src="{{ thumb_url($post->cover, 640) }}" alt="" loading="lazy">
                                @else
                                    <span class="news-card__placeholder"><x-logo :width="56" /></span>
                                @endif
                            </a>
                            <div class="news-card__body">
                                @include('posts.partials.meta', ['post' => $post])
                                <h3><a href="{{ lroute('posts.show', $post) }}">{{ $post->title }}</a></h3>
                                @if ($post->excerpt)
                                    <p>{{ $post->excerpt }}</p>
                                @endif
                                <a class="news-card__more" href="{{ lroute('posts.show', $post) }}">{{ __('Đọc tiếp') }} <x-ui-icon name="arrow-right" :size="16" /></a>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif

            {{ $posts->links('partials.pagination') }}
        </section>

        <aside class="news-aside" aria-label="{{ __('Thanh bên') }}">
            <div class="card news-widget">
                <h2>{{ __('Tìm bài viết') }}</h2>
                <form class="search-form" action="{{ lroute('posts.index') }}" method="get" role="search">
                    <label for="news-search" class="sr-only">{{ __('Tìm bài viết') }}</label>
                    <input id="news-search" type="search" name="q" value="{{ $term }}" placeholder="{{ __('Nhập từ khóa…') }}" style="height:46px">
                    <button type="submit" aria-label="{{ __('Tìm') }}" style="height:46px;padding:0 14px"><x-ui-icon name="search" /></button>
                </form>
            </div>

            @if ($term === '' && $popular->count() > 1)
                <div class="card news-widget">
                    <h2>{{ __('Đọc nhiều nhất') }}</h2>
                    <ol class="popular-list">
                        @foreach ($popular as $item)
                            <li>
                                <span class="popular-list__n">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                <span>
                                    <a href="{{ lroute('posts.show', $item) }}">{{ $item->title }}</a>
                                    <small>{{ $item->published_at?->format('d/m/Y') }}</small>
                                </span>
                            </li>
                        @endforeach
                    </ol>
                </div>
            @endif

            <div class="news-widget news-widget--cta">
                <span class="icon-tile"><x-ui-icon name="phone" :size="22" :stroke="1.9" /></span>
                <h2>{{ __('Cần tư vấn kỹ thuật?') }}</h2>
                <p>{{ __('Đội ngũ kỹ sư :name sẵn sàng hỗ trợ chọn thiết bị và giải pháp bảo trì.', ['name' => setting('brand_name')]) }}</p>
                <a class="btn btn-white" href="{{ lroute('contact') }}">{{ __('Gửi liên hệ') }}</a>
                <a class="news-widget__phone" href="{{ tel_href(setting('phone')) }}">{{ setting('phone') }}</a>
            </div>
        </aside>
    </div>

    @include('partials.cta-band')
@endsection
