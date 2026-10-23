<?php

namespace Tests\Unit;

use App\Services\LegacyHtml;
use PHPUnit\Framework\TestCase;

class LegacyHtmlTest extends TestCase
{
    private LegacyHtml $html;

    protected function setUp(): void
    {
        parent::setUp();

        $this->html = new LegacyHtml;
    }

    public function test_removes_scripts_event_handlers_and_javascript_urls(): void
    {
        $clean = $this->html->clean('<p onclick="x()">Xin chào<script>alert(1)</script></p><a href="javascript:alert(1)">bấm</a><form><input name="a"></form>');

        $this->assertSame('<p>Xin chào</p><a>bấm</a>', $clean);
    }

    public function test_drops_legacy_presentation_markup(): void
    {
        $clean = $this->html->clean('<p style="color:#ff0000" class="x"><font size="5"><span style="font-weight:bold">Thông số</span></font></p><p>&nbsp;</p>');

        $this->assertSame('<p>Thông số</p>', $clean);
    }

    public function test_rewrites_old_upload_urls_and_old_domain_links(): void
    {
        $clean = $this->html->clean('<img src="/media/Klueber/a.png"><img src="http://www.nhatduysgn.com/files/products/b.jpg"><a href="https://nhatduysgn.com/p12-keo.html">x</a><a href="https://example.com/x">y</a>');

        $this->assertStringContainsString('src="/uploads/legacy/media/Klueber/a.png"', $clean);
        $this->assertStringContainsString('src="/uploads/legacy/files/products/b.jpg"', $clean);
        $this->assertStringContainsString('href="/p12-keo.html"', $clean);
        $this->assertStringContainsString('href="https://example.com/x"', $clean);
    }

    public function test_keeps_tables_and_trusted_video_embeds_only(): void
    {
        $clean = $this->html->clean('<table><tr><td colspan="2">CX-5</td></tr></table><iframe src="https://www.youtube.com/embed/abc"></iframe><iframe src="https://evil.example/x"></iframe>');

        $this->assertStringContainsString('<td colspan="2">CX-5</td>', $clean);
        $this->assertStringContainsString('youtube.com/embed/abc', $clean);
        $this->assertStringNotContainsString('evil.example', $clean);
    }

    public function test_empty_content_becomes_null(): void
    {
        $this->assertNull($this->html->clean('<p>&nbsp;</p>'));
        $this->assertNull($this->html->clean(null));
    }
}
