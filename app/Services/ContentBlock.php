<?php

namespace App\Services;

use DOMDocument;
use DOMElement;
use DOMNode;

/**
 * Reads a page block that editors write in a single rich-text field and splits it into the parts the page lays out:
 *
 * - paragraphs before the main heading → `eyebrow` (the small line above the title)
 * - the first Heading (H1/H2) → `title`
 * - other paragraphs, and embedded blocks such as YouTube videos → `body` (HTML)
 * - bullet / numbered list items → `items` (bold start = item title, the rest = item text)
 * - each small heading (H3/H4) with the paragraphs under it → `sections`
 */
final class ContentBlock
{
    /**
     * @param  array<int, array{title: ?string, text: string}>  $items
     * @param  array<int, array{title: string, text: string}>  $sections
     */
    private function __construct(
        public readonly ?string $eyebrow,
        public readonly ?string $title,
        public readonly string $body,
        public readonly array $items,
        public readonly array $sections,
    ) {}

    public static function parse(?string $html): self
    {
        $nodes = self::topLevelNodes((string) $html);
        $headingIndex = null;

        foreach ($nodes as $index => $node) {
            if (in_array($node->nodeName, ['h1', 'h2'], true)) {
                $headingIndex = $index;
                break;
            }
        }

        $eyebrow = $title = null;
        $body = '';
        $items = $sections = [];

        foreach ($nodes as $index => $node) {
            $text = self::text($node);

            if ($headingIndex !== null && $index < $headingIndex) {
                $eyebrow = $text !== '' ? trim(($eyebrow ?? '').' '.$text) : $eyebrow;
            } elseif ($index === $headingIndex) {
                $title = $text;
            } elseif (in_array($node->nodeName, ['h3', 'h4', 'h5', 'h6'], true)) {
                $sections[] = ['title' => $text, 'text' => ''];
            } elseif (in_array($node->nodeName, ['ul', 'ol'], true)) {
                array_push($items, ...self::listItems($node));
            } elseif ($sections !== [] && $node->nodeName === 'p') {
                $last = array_key_last($sections);
                $sections[$last]['text'] = trim($sections[$last]['text'].' '.$text);
            } elseif ($text !== '' || $node->nodeName === 'img' || ($node instanceof DOMElement && $node->getAttribute('data-type') === 'customBlock')) {
                $body .= $node->ownerDocument->saveHTML($node);
            }
        }

        return new self($eyebrow ?: null, $title ?: null, $body, $items, $sections);
    }

    /**
     * The body as plain text, for meta descriptions.
     */
    public function bodyText(): string
    {
        return trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags(str_replace('</p>', ' </p>', $this->body)), ENT_QUOTES | ENT_HTML5, 'UTF-8')) ?? '');
    }

    /**
     * @return array<int, DOMNode>
     */
    private static function topLevelNodes(string $html): array
    {
        if (trim($html) === '') {
            return [];
        }

        $document = new DOMDocument;
        // The meta tag makes libxml read the fragment as UTF-8.
        @$document->loadHTML('<html><head><meta charset="utf-8"></head><body>'.$html.'</body></html>');
        $body = $document->getElementsByTagName('body')->item(0);

        return array_values(array_filter(
            iterator_to_array($body?->childNodes ?? []),
            fn (DOMNode $node) => $node instanceof DOMElement || trim($node->textContent) !== '',
        ));
    }

    /**
     * @return array<int, array{title: ?string, text: string}>
     */
    private static function listItems(DOMNode $list): array
    {
        $items = [];

        foreach ($list->childNodes as $item) {
            if ($item->nodeName !== 'li') {
                continue;
            }

            $text = self::text($item);
            $bold = null;

            foreach (['strong', 'b'] as $tag) {
                $element = $item instanceof DOMElement ? $item->getElementsByTagName($tag)->item(0) : null;
                if ($element !== null && self::text($element) !== '') {
                    $bold = self::text($element);
                    break;
                }
            }

            if ($bold !== null && str_starts_with($text, $bold)) {
                // "**2011** năm thành lập" / "**Hàng chính hãng** — từ các nước G7" → title + text.
                $items[] = ['title' => $bold, 'text' => trim(ltrim(mb_substr($text, mb_strlen($bold)), " \t:–—-"))];
            } elseif ($text !== '') {
                $items[] = ['title' => null, 'text' => $text];
            }
        }

        return $items;
    }

    private static function text(DOMNode $node): string
    {
        return trim(preg_replace('/\s+/u', ' ', $node->textContent) ?? '');
    }
}
