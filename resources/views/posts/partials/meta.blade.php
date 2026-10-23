<div class="news-meta">
    @if ($post->published_at)
        <span><x-ui-icon name="calendar" :size="15" /> <time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->format('d/m/Y') }}</time></span>
    @endif
    <span><x-ui-icon name="clock" :size="15" /> {{ $post->reading_minutes }} phút đọc</span>
</div>
