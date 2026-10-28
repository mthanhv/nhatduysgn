<?php

namespace App\Services;

/**
 * Turns editor HTML into page HTML by replacing YouTube videos with the player, and leaving everything else
 * (including HTML imported from the old site) untouched. A video is either the editor's custom block
 * (<div data-type="customBlock" data-id="youtube" data-config='{"url": …}'>) or a paragraph holding nothing but a
 * YouTube link, typed or pasted on its own line.
 */
final class RichContent
{
    public const YOUTUBE_BLOCK = 'youtube';

    public static function render(?string $html): string
    {
        $html = (string) $html;

        if (! str_contains($html, 'data-type="customBlock"') && ! str_contains($html, 'youtu')) {
            return $html;
        }

        // A paragraph that is only a YouTube link (as text or as a link) becomes the player.
        $html = preg_replace_callback('#<p\b[^>]*>(.*?)</p>#is', function (array $match): string {
            $text = trim(html_entity_decode(strip_tags(str_ireplace(['<br>', '<br/>', '<br />'], '', $match[1])), ENT_QUOTES | ENT_HTML5, 'UTF-8'));

            return self::youtubeId($text) !== null ? (string) self::youtubeEmbed($text) : $match[0];
        }, $html) ?? $html;

        return preg_replace_callback('#<div\b[^>]*\bdata-type="customBlock"[^>]*>\s*</div>#i', function (array $match): string {
            $attributes = self::attributes($match[0]);

            if (($attributes['data-id'] ?? null) !== self::YOUTUBE_BLOCK) {
                return '';
            }

            $config = json_decode(html_entity_decode($attributes['data-config'] ?? '', ENT_QUOTES | ENT_HTML5), true) ?: [];

            return self::youtubeEmbed((string) ($config['url'] ?? ''), $config['title'] ?? null) ?? '';
        }, $html) ?? $html;
    }

    /**
     * Video ID from any usual YouTube link: watch?v=, youtu.be/, embed/, shorts/, live/.
     */
    public static function youtubeId(string $url): ?string
    {
        $pattern = '#^(?:https?://)?(?:www\.|m\.)?(?:youtube\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/|live/|v/)|youtu\.be/|youtube-nocookie\.com/embed/)([A-Za-z0-9_-]{11})(?:[?&\#/].*)?$#';

        return preg_match($pattern, trim($url), $match) ? $match[1] : null;
    }

    /**
     * Responsive privacy-enhanced player (youtube-nocookie.com sets no cookies until the visitor plays the video).
     */
    public static function youtubeEmbed(string $url, ?string $title = null): ?string
    {
        $id = self::youtubeId($url);

        if ($id === null) {
            return null;
        }

        $title = e(filled($title) ? $title : 'Video YouTube');

        return '<figure class="video-embed"><iframe src="https://www.youtube-nocookie.com/embed/'.$id.'" title="'.$title.'" loading="lazy" '
            .'allow="accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" '
            .'referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe></figure>';
    }

    /**
     * @return array<string, string>
     */
    private static function attributes(string $tag): array
    {
        preg_match_all('#([a-z-]+)=("([^"]*)"|\'([^\']*)\')#i', $tag, $matches, PREG_SET_ORDER);

        return collect($matches)->mapWithKeys(fn (array $match) => [strtolower($match[1]) => $match[3] !== '' ? $match[3] : ($match[4] ?? '')])->all();
    }
}
