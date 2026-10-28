<?php

namespace Tests\Unit;

use App\Services\ContentBlock;
use PHPUnit\Framework\TestCase;

class ContentBlockTest extends TestCase
{
    public function test_splits_a_hero_into_small_line_title_and_text(): void
    {
        $block = ContentBlock::parse('<p>Thành lập 2011</p><h2>Thiết bị &amp; giải pháp</h2><p>Đoạn giới thiệu.</p><p>Đoạn hai.</p>');

        $this->assertSame('Thành lập 2011', $block->eyebrow);
        $this->assertSame('Thiết bị & giải pháp', $block->title);
        $this->assertSame('<p>Đoạn giới thiệu.</p><p>Đoạn hai.</p>', $block->body);
        $this->assertSame('Đoạn giới thiệu. Đoạn hai.', $block->bodyText());
    }

    public function test_paragraphs_without_a_heading_are_body_not_a_small_line(): void
    {
        $block = ContentBlock::parse('<p>Chỉ có một đoạn.</p>');

        $this->assertNull($block->eyebrow);
        $this->assertNull($block->title);
        $this->assertSame('<p>Chỉ có một đoạn.</p>', $block->body);
    }

    public function test_list_items_split_their_bold_start_from_the_rest(): void
    {
        $block = ContentBlock::parse('<ul><li><p><strong>2011</strong> năm thành lập</p></li><li><strong>Hàng chính hãng</strong> — từ G7</li><li>Dầu khí</li></ul>');

        $this->assertSame([
            ['title' => '2011', 'text' => 'năm thành lập'],
            ['title' => 'Hàng chính hãng', 'text' => 'từ G7'],
            ['title' => null, 'text' => 'Dầu khí'],
        ], $block->items);
    }

    public function test_small_headings_with_their_paragraphs_become_sections(): void
    {
        $block = ContentBlock::parse('<h3>Bảo trì (Maintenance)</h3><p>Dầu mỡ, keo.</p><h3>Sửa chữa</h3><p>Rà van.</p><p>Seal cơ khí.</p>');

        $this->assertSame([
            ['title' => 'Bảo trì (Maintenance)', 'text' => 'Dầu mỡ, keo.'],
            ['title' => 'Sửa chữa', 'text' => 'Rà van. Seal cơ khí.'],
        ], $block->sections);
    }

    public function test_empty_content_has_no_parts(): void
    {
        $block = ContentBlock::parse(null);

        $this->assertNull($block->title);
        $this->assertSame('', $block->body);
        $this->assertSame([], $block->items);
    }
}
