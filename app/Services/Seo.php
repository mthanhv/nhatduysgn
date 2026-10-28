<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\Post;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Project;
use App\Models\Service;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Search-engine and AI-agent metadata: meta descriptions written from the record's own data when an editor
 * left the SEO field empty, and schema.org structured data (JSON-LD) for every page type.
 */
class Seo
{
    public const DESCRIPTION_LENGTH = 158;

    /**
     * Plain one-line text from HTML, cut at a word boundary.
     */
    public static function text(?string $html, int $limit = self::DESCRIPTION_LENGTH): string
    {
        $text = html_entity_decode(strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>', '</li>'], ' ', (string) $html)), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = trim(preg_replace('/\s+/u', ' ', str_replace("\u{00A0}", ' ', $text)) ?? '');

        if (mb_strlen($text) <= $limit) {
            return $text;
        }

        $cut = mb_substr($text, 0, $limit - 1);
        $space = mb_strrpos($cut, ' ');

        // Unicode-aware trim: rtrim() works on bytes and would split Vietnamese letters next to "–".
        return preg_replace('/[\s,.;:\-–(]+$/u', '', $space > $limit * 0.6 ? mb_substr($cut, 0, $space) : $cut).'…';
    }

    public const TITLE_LENGTH = 58;

    private const UNITS = ['MM' => 'mm', 'CM' => 'cm', 'M' => 'm', 'KG' => 'kg', 'G' => 'g', 'ML' => 'ml', 'L' => 'L', 'NM' => 'Nm', 'BAR' => 'bar', 'PSI' => 'psi', 'INCH' => 'inch', 'MICRON' => 'micron', 'KW' => 'kW', 'HP' => 'HP'];

    /** Acronyms kept upper case when an ALL-CAPS product name is put in normal case. */
    private const ACRONYMS = ['GE', 'UK', 'USA', 'EU', 'MRO', 'ANSI', 'API', 'ISO', 'DIN', 'JIS', 'CNC', 'PTFE', 'NH1', 'HP', 'LED', 'PMM'];

    /**
     * Search-result title for a product: the editor's SEO title, or the name in normal case (old names are often
     * ALL CAPS) plus the product code, which also tells apart look-alike items such as 23 NORMA clamp sizes.
     */
    public static function productTitle(Product $product): string
    {
        if (filled($product->ownValue('meta_title'))) {
            return self::text($product->ownValue('meta_title'), 70);
        }

        $code = $product->code && ! Str::contains(Str::upper($product->name), Str::upper($product->code)) ? ' – '.$product->code : '';
        $name = self::readableCase(self::text($product->name, 1000));

        $budget = max(20, self::TITLE_LENGTH - mb_strlen($code));

        // Without a separate code the model is usually at the end ("… Spintec 105"): keep both ends.
        if ($code === '' && mb_strlen($name) > $budget) {
            $tail = mb_substr($name, -20);
            $tail = trim(mb_strpos($tail, ' ') !== false ? mb_substr($tail, mb_strpos($tail, ' ')) : $tail, ' ()');

            return rtrim(self::text($name, $budget - mb_strlen($tail) - 2), '…').'… '.$tail;
        }

        return self::text($name, $budget).$code;
    }

    /**
     * "ĐAI SIẾT (CỔ DÊ) CHỊU LỰC THÉP NORMA GROUP" → "Đai siết (cổ dê) chịu lực thép NORMA group". Words with digits
     * (codes, sizes) and brand names stay as written; text that is not mostly upper case is returned unchanged.
     */
    public static function readableCase(string $text): string
    {
        $letters = preg_replace('/[^\p{L}]/u', '', $text) ?? '';
        $upper = preg_replace('/[^\p{Lu}]/u', '', $letters) ?? '';

        if (mb_strlen($letters) < 12 || mb_strlen($upper) / max(1, mb_strlen($letters)) < 0.8) {
            return $text;
        }

        $brands = once(fn () => Brand::query()->pluck('name')->map(fn (string $name) => mb_strtoupper($name))->flip()->all());

        $words = preg_split('/(\s+)/u', $text, -1, PREG_SPLIT_DELIM_CAPTURE) ?: [];
        $result = '';

        foreach ($words as $word) {
            $bare = trim($word, '()[],.:;"\'');
            $keep = preg_match('/\d/', $word) || isset($brands[mb_strtoupper($bare)]) || in_array($bare, self::ACRONYMS, true);
            // "0.10MM" → "0.10mm": a number with a unit is not a code.
            $word = preg_replace_callback('/^([\d.,\/x×\-]+)(MM|CM|M|KG|G|ML|L|NM|BAR|PSI|INCH|MICRON|KW|HP)([,.;:)]*)$/u', fn (array $m) => $m[1].self::UNITS[$m[2]].$m[3], $word) ?? $word;
            $result .= $keep ? $word : mb_strtolower($word);
        }

        return preg_replace_callback('/\p{L}/u', fn (array $letter) => mb_strtoupper($letter[0]), $result, 1) ?? $result;
    }

    /**
     * Old SEO fields were often keyword lists ("ITH, ith, Đầu Nhân Lực, …"), which search engines ignore or treat
     * as stuffing; those are replaced by the generated description.
     */
    public static function isKeywordList(?string $text): bool
    {
        $text = self::text($text, 1000);

        return substr_count($text, ',') >= 3 && ! preg_match('/[.!?…](\s|$)/u', $text);
    }

    /**
     * @param  bool  $generated  ignore the editor's SEO text (used to show the automatic text in the admin)
     */
    public static function productDescription(Product $product, bool $generated = false): string
    {
        if (! $generated && filled($product->ownValue('meta_description')) && ! self::isKeywordList($product->ownValue('meta_description'))) {
            return self::text($product->ownValue('meta_description'));
        }

        $facts = collect([
            $product->code && ! Str::contains(Str::upper($product->name), Str::upper($product->code)) ? __('mã').' '.$product->code : null,
            $product->brand ? __('hãng').' '.$product->brand->name : null,
            $product->origin,
        ])->filter()->implode(', ');

        // Old descriptions usually open with the product name again and often say "download the file"; drop both.
        $lead = self::text($product->summary ?: preg_replace('#<table.*?</table>#is', ' ', (string) $product->description), 400);
        $lead = trim(preg_replace('/[^.]*(tải tài liệu|file đính kèm|tệp đính kèm)[^.]*\.?/iu', '', $lead) ?? $lead);
        if (mb_stripos($lead, $product->name) === 0) {
            $lead = preg_replace('/^[\s:.,–\-]+/u', '', mb_substr($lead, mb_strlen($product->name)));
        }

        return self::text(collect([
            $product->name.($facts !== '' ? " ({$facts})" : '').'.',
            self::text($lead, 220),
            __('Hàng chính hãng, tư vấn kỹ thuật: :phone.', ['phone' => setting('phone')]),
        ])->filter()->implode(' '));
    }

    public static function categoryDescription(ProductCategory $category, bool $generated = false): string
    {
        if (! $generated && filled($category->ownValue('meta_description')) && ! self::isKeywordList($category->ownValue('meta_description'))) {
            return self::text($category->ownValue('meta_description'));
        }

        $products = Product::query()->published()->whereIn('product_category_id', $category->descendantAndSelfIds());
        $brands = Brand::query()->whereIn('id', (clone $products)->select('brand_id'))->orderBy('name')->pluck('name');
        // Group summaries that only list brand names ("LOCTITE · KEMET") add nothing to the sentence above.
        $summaryIsBrands = collect(preg_split('/\s*[·,]\s*/u', (string) $category->summary))
            ->every(fn (string $part) => $part === '' || Brand::query()->where('name', $part)->exists());
        $own = self::text(($summaryIsBrands ? null : $category->summary) ?: $category->description, 220);
        $children = $category->children()->published()->ordered()->pluck('name');

        return self::text(collect([
            $category->name.': '.($brands->isNotEmpty()
                ? __(':count sản phẩm chính hãng từ :brands.', ['count' => (clone $products)->count(), 'brands' => $brands->take(4)->implode(', ')])
                : __(':count sản phẩm chính hãng.', ['count' => (clone $products)->count()])),
            $own,
            $children->isNotEmpty() ? __('Gồm').': '.$children->implode(', ').'.' : null,
            __('Tư vấn và cung cấp bởi :company.', ['company' => setting('company_name')]),
        ])->filter()->implode(' '));
    }

    public static function brandDescription(Brand $brand): string
    {
        $groups = ProductCategory::query()
            ->whereIn('id', $brand->products()->published()->select('product_category_id'))
            ->with('parent')
            ->get()
            ->map(fn (ProductCategory $category) => $category->parent?->name ?? $category->name)
            ->unique()
            ->take(4);

        return self::text(collect([
            __('Sản phẩm :brand chính hãng:country do :name phân phối tại Việt Nam.', ['brand' => $brand->name, 'country' => $brand->country ? " ({$brand->country})" : '', 'name' => setting('brand_name')]),
            $groups->isNotEmpty() ? __('Nhóm sản phẩm').': '.$groups->implode(', ').'.' : null,
            self::text($brand->description, 160),
            __('Tư vấn kỹ thuật: :phone.', ['phone' => setting('phone')]),
        ])->filter()->implode(' '));
    }

    public static function postDescription(Post $post, bool $generated = false): string
    {
        return self::text(($generated ? null : $post->ownValue('meta_description')) ?: ($post->excerpt ?: $post->content));
    }

    public static function projectDescription(Project $project, bool $generated = false): string
    {
        return self::text(($generated ? null : $project->ownValue('meta_description')) ?: ($project->summary ?: $project->content));
    }

    /**
     * Absolute URL for an uploaded file or asset (social networks and crawlers ignore relative image URLs).
     */
    public static function absoluteUrl(?string $path): ?string
    {
        $url = media_url($path);

        if ($url === null) {
            return null;
        }

        // Old uploads have spaces and Vietnamese letters in their names; encode each path segment once.
        $parts = parse_url(url($url));
        $encodedPath = implode('/', array_map(fn (string $segment) => rawurlencode(rawurldecode($segment)), explode('/', $parts['path'] ?? '')));

        return ($parts['scheme'] ?? 'https').'://'.($parts['host'] ?? '').(isset($parts['port']) ? ':'.$parts['port'] : '').$encodedPath;
    }

    /**
     * @return array<string, mixed>
     */
    public static function organization(): array
    {
        $place = fn (string $name, ?string $street) => $street ? [
            '@type' => 'Place',
            'name' => $name,
            'address' => ['@type' => 'PostalAddress', 'streetAddress' => $street, 'addressLocality' => 'TP. Hồ Chí Minh', 'addressCountry' => 'VN'],
        ] : null;

        return [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            '@id' => url('/').'#organization',
            'name' => setting('company_name'),
            'alternateName' => setting('brand_name'),
            'url' => url('/'),
            'logo' => asset('images/logo.png'),
            'image' => asset('images/hero-ith-cx.jpg'),
            'description' => setting('seo_description'),
            'email' => setting('email'),
            'telephone' => setting('phone'),
            'faxNumber' => setting('fax'),
            'foundingDate' => setting('founded_year'),
            'taxID' => setting('tax_code'),
            'address' => $place(__('Trụ sở'), setting('address'))['address'],
            'location' => array_values(array_filter([
                $place(__('Trụ sở'), setting('address')),
                $place(__('Văn phòng đại diện'), setting('office_address')),
            ])),
            'contactPoint' => [[
                '@type' => 'ContactPoint',
                'contactType' => 'sales',
                'telephone' => setting('phone'),
                'email' => setting('email'),
                'areaServed' => 'VN',
                'availableLanguage' => ['vi', 'en'],
            ]],
            'areaServed' => ['@type' => 'Country', 'name' => 'Việt Nam'],
            'knowsAbout' => ProductCategory::query()->published()->whereNull('parent_id')->ordered()->pluck('name')->all(),
            'brand' => Brand::query()->published()->ordered()->pluck('name')->map(fn (string $name) => ['@type' => 'Brand', 'name' => $name])->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function website(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            '@id' => url('/').'#website',
            'url' => url('/'),
            'name' => setting('brand_name'),
            'inLanguage' => app()->getLocale(),
            'publisher' => ['@id' => url('/').'#organization'],
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => ['@type' => 'EntryPoint', 'urlTemplate' => lroute('products.index').'?q={search_term_string}'],
                'query-input' => 'required name=search_term_string',
            ],
        ];
    }

    /**
     * @param  array<string, string|null>  $items  label => url (null = current page), without the home page
     * @return array<string, mixed>
     */
    public static function breadcrumbs(array $items): array
    {
        $position = 1;
        $elements = [['@type' => 'ListItem', 'position' => $position, 'name' => __('Trang chủ'), 'item' => lroute('home')]];

        foreach ($items as $label => $url) {
            $elements[] = ['@type' => 'ListItem', 'position' => ++$position, 'name' => (string) $label, 'item' => $url ?? url()->current()];
        }

        return ['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => $elements];
    }

    /**
     * Product without price/offers: prices are given on request, so this describes the item only.
     *
     * @return array<string, mixed>
     */
    public static function product(Product $product): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            '@id' => lroute('products.show', $product).'#product',
            'name' => $product->name,
            'url' => lroute('products.show', $product),
            'sku' => $product->code,
            'mpn' => $product->code,
            'description' => self::productDescription($product),
            'image' => array_values(array_filter([self::absoluteUrl($product->image), ...collect($product->gallery ?? [])->map(fn ($path) => self::absoluteUrl(is_array($path) ? ($path['image'] ?? null) : $path))->all()])),
            'brand' => $product->brand ? ['@type' => 'Brand', 'name' => $product->brand->name] : null,
            'category' => collect([$product->category?->parent?->name, $product->category?->name])->filter()->implode(' > ') ?: null,
            'countryOfOrigin' => $product->origin,
            'keywords' => $product->keywords,
            'seller' => ['@id' => url('/').'#organization'],
        ];
    }

    /**
     * @param  Paginator<int, Product>|Collection<int, Product>  $products
     * @return array<string, mixed>
     */
    public static function productList(string $name, string $description, Paginator|Collection $products): array
    {
        $offset = $products instanceof Paginator ? ($products->currentPage() - 1) * $products->perPage() : 0;
        $items = collect($products instanceof Paginator ? $products->items() : $products->all())->values();

        return [
            '@context' => 'https://schema.org',
            '@type' => 'CollectionPage',
            'name' => $name,
            'description' => $description,
            'url' => url()->current(),
            'isPartOf' => ['@id' => url('/').'#website'],
            'mainEntity' => [
                '@type' => 'ItemList',
                'numberOfItems' => $products instanceof LengthAwarePaginator ? $products->total() : $items->count(),
                'itemListElement' => $items->map(fn (Product $product, int $index) => [
                    '@type' => 'ListItem',
                    'position' => $offset + $index + 1,
                    'url' => lroute('products.show', $product),
                    'name' => $product->name,
                ])->all(),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function article(Post $post): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => Str::limit($post->title, 110, ''),
            'description' => self::postDescription($post),
            'url' => lroute('posts.show', $post),
            'mainEntityOfPage' => lroute('posts.show', $post),
            'datePublished' => $post->published_at?->toAtomString(),
            'dateModified' => $post->updated_at?->toAtomString(),
            'image' => self::absoluteUrl($post->cover),
            'inLanguage' => app()->getLocale(),
            'author' => ['@id' => url('/').'#organization'],
            'publisher' => ['@id' => url('/').'#organization'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function project(Project $project): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => Str::limit($project->title, 110, ''),
            'description' => self::projectDescription($project),
            'url' => lroute('projects.show', $project),
            'image' => self::absoluteUrl($project->cover),
            'about' => $project->industry,
            'inLanguage' => app()->getLocale(),
            'author' => ['@id' => url('/').'#organization'],
            'publisher' => ['@id' => url('/').'#organization'],
        ];
    }

    /**
     * @param  Collection<int, Service>  $services
     * @param  array<int, array{question: string, answer: string}>  $faqs
     * @return array<int, array<string, mixed>>
     */
    public static function services(Collection $services, array $faqs): array
    {
        $graph = $services->map(fn ($service) => [
            '@context' => 'https://schema.org',
            '@type' => 'Service',
            'name' => $service->title,
            'description' => self::text($service->ownValue('meta_description') ?: ($service->summary ?: $service->description)),
            'url' => lroute('services').'#'.$service->slug,
            'image' => self::absoluteUrl($service->image),
            'serviceType' => $service->title,
            'areaServed' => ['@type' => 'Country', 'name' => 'Việt Nam'],
            'provider' => ['@id' => url('/').'#organization'],
        ])->all();

        if ($faqs !== []) {
            $graph[] = [
                '@context' => 'https://schema.org',
                '@type' => 'FAQPage',
                'mainEntity' => collect($faqs)->map(fn (array $faq) => [
                    '@type' => 'Question',
                    'name' => $faq['question'],
                    'acceptedAnswer' => ['@type' => 'Answer', 'text' => $faq['answer']],
                ])->all(),
            ];
        }

        return $graph;
    }

    /**
     * JSON for a <script type="application/ld+json"> block, with empty values removed.
     *
     * @param  array<mixed>  $data
     */
    public static function json(array $data): string
    {
        return json_encode(self::withoutEmpty($data), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG);
    }

    /**
     * @param  array<mixed>  $data
     * @return array<mixed>
     */
    private static function withoutEmpty(array $data): array
    {
        $clean = [];

        foreach ($data as $key => $value) {
            $value = is_array($value) ? self::withoutEmpty($value) : $value;

            if ($value !== null && $value !== '' && $value !== []) {
                $clean[$key] = $value;
            }
        }

        return array_is_list($data) ? array_values($clean) : $clean;
    }
}
