@extends('layouts.site')

@section('title', 'Tin tức')

@section('content')
    <div class="container article-head" style="max-width:1080px">
        <x-breadcrumb :items="['Tin tức' => null]" />
        <h1>Tin tức</h1>
    </div>
    <section class="container" style="padding-bottom:72px">
        @if ($posts->isEmpty())
            <p class="card empty" style="max-width:1080px">Chưa có bài viết.</p>
        @else
            <div class="news-list" style="margin:0 auto">
                @foreach ($posts as $post)
                    <a class="card news-item" href="{{ route('posts.show', $post) }}">
                        <span class="cover">@if ($post->cover)<img src="{{ media_url($post->cover) }}" alt="" loading="lazy">@endif</span>
                        <span class="body">
                            <span class="muted" style="font-size:13px">{{ $post->published_at?->format('d/m/Y') }}</span>
                            <h2>{{ $post->title }}</h2>
                            @if ($post->excerpt)<span class="muted" style="font-size:15px;line-height:1.7">{{ $post->excerpt }}</span>@endif
                            <span class="link-more" style="font-size:14px">Đọc bài viết →</span>
                        </span>
                    </a>
                @endforeach
                {{ $posts->links('partials.pagination') }}
            </div>
        @endif
    </section>
@endsection
