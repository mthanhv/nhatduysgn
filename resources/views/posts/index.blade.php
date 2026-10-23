@extends('layouts.site')

@section('title', $term !== '' ? 'Tìm tin tức: '.$term : 'Tin tức')
@section('description', 'Tin tức ngành công nghiệp, năng lượng và hoạt động của '.setting('company_name').'.')

@section('content')
    <section class="page-hero page-hero--grid">
        <div class="container news-hero">
            <x-breadcrumb :items="['Tin tức' => null]" />
            <div class="news-hero__row">
                <div class="news-hero__text">
                    <span class="eyebrow eyebrow--light">Tin tức &amp; kiến thức</span>
                    <h1>Tin tức</h1>
                    <p>Cập nhật ngành dầu khí, năng lượng, bảo trì công nghiệp và hoạt động của {{ setting('brand_name') }}.</p>
                </div>
                <div class="news-hero__count">
                    <strong>{{ $total }}</strong>
                    <span>bài viết</span>
                </div>
            </div>
        </div>
    </section>

    @if ($featured)
        <section class="container news-featured-wrap">
            <article class="news-featured">
                <a class="news-featured__media" href="{{ route('posts.show', $featured) }}" tabindex="-1" aria-hidden="true">
                    @if ($featured->cover)
                        <img src="{{ media_url($featured->cover) }}" alt="" fetchpriority="high">
                    @endif
                    <span class="news-badge">Mới nhất</span>
                </a>
                <div class="news-featured__body">
                    @include('posts.partials.meta', ['post' => $featured])
                    <h2><a href="{{ route('posts.show', $featured) }}">{{ $featured->title }}</a></h2>
                    @if ($featured->excerpt)
                        <p class="news-featured__excerpt">{{ $featured->excerpt }}</p>
                    @endif
                    <a class="btn btn-primary" href="{{ route('posts.show', $featured) }}">Đọc bài viết <x-ui-icon name="arrow-right" :size="18" /></a>
                </div>
            </article>
        </section>
    @endif

    <div class="container news-layout">
        <section class="news-main" aria-labelledby="news-list-heading">
            <div class="news-main__head">
                <h2 id="news-list-heading">{{ $term !== '' ? 'Kết quả cho “'.$term.'”' : ($featured ? 'Bài viết khác' : 'Tất cả bài viết') }}</h2>
                @if ($term !== '')
                    <a class="link-more" href="{{ route('posts.index') }}">Xóa tìm kiếm</a>
                @endif
            </div>

            @if ($gridPosts->isEmpty() && ! $featured)
                <div class="card empty news-empty">
                    <x-ui-icon name="search" :size="36" :stroke="1.6" />
                    @if ($term !== '')
                        <p>Không tìm thấy bài viết phù hợp với “{{ $term }}”.</p>
                        <a class="btn btn-outline" href="{{ route('posts.index') }}">Xem tất cả bài viết</a>
                    @else
                        <p>Chưa có bài viết nào. Vui lòng quay lại sau.</p>
                    @endif
                </div>
            @elseif ($gridPosts->isEmpty())
                <p class="muted">Đây là bài viết duy nhất hiện có.</p>
            @else
                <div class="news-grid">
                    @foreach ($gridPosts as $post)
                        <article class="card news-card">
                            <a class="news-card__media" href="{{ route('posts.show', $post) }}" tabindex="-1" aria-hidden="true">
                                @if ($post->cover)
                                    <img src="{{ media_url($post->cover) }}" alt="" loading="lazy">
                                @else
                                    <span class="news-card__placeholder"><x-logo :width="56" /></span>
                                @endif
                            </a>
                            <div class="news-card__body">
                                @include('posts.partials.meta', ['post' => $post])
                                <h3><a href="{{ route('posts.show', $post) }}">{{ $post->title }}</a></h3>
                                @if ($post->excerpt)
                                    <p>{{ $post->excerpt }}</p>
                                @endif
                                <a class="news-card__more" href="{{ route('posts.show', $post) }}">Đọc tiếp <x-ui-icon name="arrow-right" :size="16" /></a>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif

            {{ $posts->links('partials.pagination') }}
        </section>

        <aside class="news-aside" aria-label="Thanh bên">
            <div class="card news-widget">
                <h2>Tìm bài viết</h2>
                <form class="search-form" action="{{ route('posts.index') }}" method="get" role="search">
                    <label for="news-search" class="sr-only">Tìm bài viết</label>
                    <input id="news-search" type="search" name="q" value="{{ $term }}" placeholder="Nhập từ khóa…" style="height:46px">
                    <button type="submit" aria-label="Tìm" style="height:46px;padding:0 14px"><x-ui-icon name="search" /></button>
                </form>
            </div>

            @if ($term === '' && $popular->count() > 1)
                <div class="card news-widget">
                    <h2>Đọc nhiều nhất</h2>
                    <ol class="popular-list">
                        @foreach ($popular as $item)
                            <li>
                                <span class="popular-list__n">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                <span>
                                    <a href="{{ route('posts.show', $item) }}">{{ $item->title }}</a>
                                    <small>{{ $item->published_at?->format('d/m/Y') }}</small>
                                </span>
                            </li>
                        @endforeach
                    </ol>
                </div>
            @endif

            <div class="news-widget news-widget--cta">
                <span class="icon-tile"><x-ui-icon name="phone" :size="22" :stroke="1.9" /></span>
                <h2>Cần tư vấn kỹ thuật?</h2>
                <p>Đội ngũ kỹ sư {{ setting('brand_name') }} sẵn sàng hỗ trợ chọn thiết bị và giải pháp bảo trì.</p>
                <a class="btn btn-white" href="{{ route('contact') }}">Gửi liên hệ</a>
                <a class="news-widget__phone" href="{{ tel_href(setting('phone')) }}">{{ setting('phone') }}</a>
            </div>
        </aside>
    </div>

    @include('partials.cta-band')
@endsection
