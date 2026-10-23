<?php

namespace App\Services;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;

/**
 * Cleans rich-text HTML coming from the old site before it is stored in the new database.
 *
 * - Removes anything executable (scripts, event handlers, javascript: URLs, forms, embeds).
 * - Drops inline presentation (style, class, <font>) so content follows the new design.
 * - Rewrites links/images that pointed at the old /files and /media folders to /uploads/legacy/...
 */
class LegacyHtml
{
    private const REMOVE_WITH_CONTENT = ['script', 'style', 'noscript', 'object', 'embed', 'applet', 'form', 'input', 'button', 'select', 'textarea', 'link', 'meta', 'base', 'svg', 'math'];

    private const UNWRAP = ['font', 'span', 'center', 'o:p'];

    private const ALLOWED_ATTRIBUTES = ['href', 'src', 'alt', 'title', 'colspan', 'rowspan', 'target', 'width', 'height', 'allowfullscreen', 'frameborder'];

    private const ALLOWED_IFRAME_HOSTS = ['www.youtube.com', 'youtube.com', 'www.youtube-nocookie.com', 'player.vimeo.com', 'www.google.com', 'maps.google.com'];

    /**
     * @param  array<int, string>  $siteHosts
     */
    public function __construct(
        private string $uploadsUrlPrefix = '/uploads/legacy',
        private array $siteHosts = ['nhatduysgn.com', 'www.nhatduysgn.com'],
    ) {}

    public function clean(?string $html): ?string
    {
        if (blank($html) || blank(trim(strip_tags($html, '<img><iframe><table>')))) {
            return null;
        }

        $document = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="UTF-8"><div id="legacy-root">'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $xpath = new DOMXPath($document);
        $root = $document->getElementById('legacy-root') ?? $document->documentElement;

        foreach (self::REMOVE_WITH_CONTENT as $tag) {
            foreach (iterator_to_array($xpath->query('.//'.$tag, $root)) as $node) {
                $node->parentNode?->removeChild($node);
            }
        }

        foreach (iterator_to_array($xpath->query('.//comment()', $root)) as $comment) {
            $comment->parentNode?->removeChild($comment);
        }

        foreach (iterator_to_array($xpath->query('.//iframe', $root)) as $iframe) {
            /** @var DOMElement $iframe */
            $host = parse_url($this->absolutize($iframe->getAttribute('src')), PHP_URL_HOST);

            if (! in_array($host, self::ALLOWED_IFRAME_HOSTS, true)) {
                $iframe->parentNode?->removeChild($iframe);
            }
        }

        foreach (self::UNWRAP as $tag) {
            foreach (iterator_to_array($xpath->query('.//*[local-name()="'.$tag.'"]', $root)) as $node) {
                $this->unwrap($node);
            }
        }

        foreach (iterator_to_array($xpath->query('.//*', $root)) as $element) {
            /** @var DOMElement $element */
            $this->cleanAttributes($element);
        }

        $this->removeEmptyParagraphs($xpath, $root);

        $output = '';
        foreach (iterator_to_array($root->childNodes) as $child) {
            $output .= $document->saveHTML($child);
        }

        $output = trim(preg_replace("/(\r?\n){3,}/", "\n\n", $output) ?? $output);

        return $output === '' ? null : $output;
    }

    /**
     * Maps an old upload URL (/files/..., /media/..., absolute on the old domain) to its new location; other URLs pass through.
     */
    public function rewriteUrl(string $url): string
    {
        $url = trim($url);
        $parts = parse_url($url);

        if ($parts === false) {
            return $url;
        }

        $host = strtolower($parts['host'] ?? '');

        if ($host !== '' && ! in_array($host, $this->siteHosts, true)) {
            return $url;
        }

        $path = '/'.ltrim($parts['path'] ?? '', '/');

        if (preg_match('#^/(?:app/webroot/)?(files|media)/(.+)$#i', $path, $matches)) {
            return $this->uploadsUrlPrefix.'/'.strtolower($matches[1]).'/'.$matches[2];
        }

        if ($host !== '') {
            // Other links to the old domain become site-relative so the legacy redirects can take over.
            return $path.(isset($parts['query']) ? '?'.$parts['query'] : '').(isset($parts['fragment']) ? '#'.$parts['fragment'] : '');
        }

        return $url;
    }

    private function cleanAttributes(DOMElement $element): void
    {
        foreach (iterator_to_array($element->attributes) as $attribute) {
            $name = strtolower($attribute->nodeName);

            if (! in_array($name, self::ALLOWED_ATTRIBUTES, true)) {
                $element->removeAttribute($attribute->nodeName);

                continue;
            }

            if (in_array($name, ['href', 'src'], true)) {
                $value = trim($attribute->nodeValue ?? '');

                if (preg_match('/^\s*(javascript|vbscript|data):/i', $value) && ! preg_match('#^data:image/(png|jpe?g|gif|webp);#i', $value)) {
                    $element->removeAttribute($attribute->nodeName);

                    continue;
                }

                $element->setAttribute($name, $this->rewriteUrl($value));
            }
        }

        if ($element->tagName === 'a' && $element->getAttribute('target') === '_blank') {
            $element->setAttribute('rel', 'noopener');
        }

        if ($element->tagName === 'img') {
            $element->setAttribute('loading', 'lazy');
        }
    }

    private function unwrap(DOMNode $node): void
    {
        $parent = $node->parentNode;

        if ($parent === null) {
            return;
        }

        while ($node->firstChild) {
            $parent->insertBefore($node->firstChild, $node);
        }

        $parent->removeChild($node);
    }

    private function removeEmptyParagraphs(DOMXPath $xpath, DOMNode $root): void
    {
        foreach (iterator_to_array($xpath->query('.//p|.//div|.//h1|.//h2|.//h3|.//h4', $root)) as $block) {
            $text = trim(str_replace("\u{00A0}", ' ', $block->textContent));

            if ($text === '' && $xpath->query('.//img|.//iframe|.//table', $block)->length === 0) {
                $block->parentNode?->removeChild($block);
            }
        }
    }

    private function absolutize(string $url): string
    {
        return str_starts_with($url, '//') ? 'https:'.$url : $url;
    }
}
