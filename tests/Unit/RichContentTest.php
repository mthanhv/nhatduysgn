<?php

namespace Tests\Unit;

use App\Services\ContentBlock;
use App\Services\RichContent;
use PHPUnit\Framework\TestCase;

class RichContentTest extends TestCase
{
    public function test_reads_the_video_id_from_usual_youtube_links(): void
    {
        foreach ([
            'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'https://youtube.com/watch?feature=share&v=dQw4w9WgXcQ&t=30',
            'https://youtu.be/dQw4w9WgXcQ?si=abc',
            'https://www.youtube.com/shorts/dQw4w9WgXcQ',
            'https://m.youtube.com/embed/dQw4w9WgXcQ',
            'youtube.com/live/dQw4w9WgXcQ',
        ] as $url) {
            $this->assertSame('dQw4w9WgXcQ', RichContent::youtubeId($url), $url);
        }

        $this->assertNull(RichContent::youtubeId('https://vimeo.com/123456'));
        $this->assertNull(RichContent::youtubeId('https://example.com/watch?v=dQw4w9WgXcQ'));
    }

    public function test_replaces_video_blocks_with_the_player_and_leaves_other_html_alone(): void
    {
        $html = '<p>Trước</p><div data-type="customBlock" data-config="{&quot;url&quot;:&quot;https://youtu.be/dQw4w9WgXcQ&quot;,&quot;title&quot;:&quot;Demo &amp; test&quot;}" data-id="youtube"></div><table><tr><td>Thông số</td></tr></table>';

        $rendered = RichContent::render($html);

        $this->assertStringContainsString('<p>Trước</p>', $rendered);
        $this->assertStringContainsString('src="https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ"', $rendered);
        $this->assertStringContainsString('title="Demo &amp; test"', $rendered);
        $this->assertStringContainsString('<table><tr><td>Thông số</td></tr></table>', $rendered);
        $this->assertStringNotContainsString('customBlock', $rendered);
    }

    public function test_a_youtube_link_alone_on_a_line_becomes_the_player(): void
    {
        $rendered = RichContent::render('<p>Xem video:</p><p>https://youtu.be/dQw4w9WgXcQ</p><p><a href="https://www.youtube.com/watch?v=aaaaaaaaaaa">https://www.youtube.com/watch?v=aaaaaaaaaaa</a></p>');

        $this->assertStringContainsString('<p>Xem video:</p>', $rendered);
        $this->assertStringContainsString('youtube-nocookie.com/embed/dQw4w9WgXcQ', $rendered);
        $this->assertStringContainsString('youtube-nocookie.com/embed/aaaaaaaaaaa', $rendered);
    }

    public function test_youtube_links_inside_a_sentence_stay_links(): void
    {
        $html = '<p>Xem <a href="https://youtu.be/dQw4w9WgXcQ">video hướng dẫn</a> trên kênh của hãng.</p><p><a href="https://youtu.be/dQw4w9WgXcQ">Xem video</a></p>';

        $this->assertSame($html, RichContent::render($html));
    }

    public function test_drops_unknown_or_broken_blocks(): void
    {
        $this->assertSame('<p>A</p>', RichContent::render('<p>A</p><div data-type="customBlock" data-config="{&quot;url&quot;:&quot;https://vimeo.com/1&quot;}" data-id="youtube"></div>'));
        $this->assertSame('<p>A</p>', RichContent::render('<p>A</p><div data-type="customBlock" data-config="{}" data-id="other"></div>'));
        $this->assertSame('<p>Không có khối</p>', RichContent::render('<p>Không có khối</p>'));
    }

    public function test_page_blocks_keep_videos_in_their_body(): void
    {
        $block = ContentBlock::parse('<h2>Câu chuyện</h2><p>Đoạn.</p><div data-type="customBlock" data-config="{&quot;url&quot;:&quot;https://youtu.be/dQw4w9WgXcQ&quot;}" data-id="youtube"></div>');

        $this->assertStringContainsString('youtube-nocookie.com/embed/dQw4w9WgXcQ', RichContent::render($block->body));
    }
}
