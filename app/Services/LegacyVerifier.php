<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\Client;
use App\Models\ContactMessage;
use App\Models\Post;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Redirect;
use FilesystemIterator;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * Reconciles the old site against the new one after `legacy:import`.
 *
 * Every check returns the number of items it inspected and a list of problems; an empty problem list
 * means that part of the old site is fully represented in the new one.
 */
class LegacyVerifier
{
    /** @var array<string, array{checked: int, problems: array<int, string>, notes: array<int, string>}> */
    private array $results = [];

    public function __construct(
        private LegacyUrlResolver $resolver,
        private string $connection = 'legacy',
        private ?string $webroot = null,
    ) {
        $this->webroot ??= config('legacy.webroot');
    }

    /**
     * @param  array<int, string>  $extraUrls  real old URLs, e.g. from the old server's access logs
     * @return array<string, array{checked: int, problems: array<int, string>, notes: array<int, string>}>
     */
    public function run(array $extraUrls = []): array
    {
        $this->results = [];

        $this->checkCategories();
        $this->checkBrands();
        $this->checkProducts();
        $this->checkArticles();
        $this->checkClients();
        $this->checkContacts();
        $this->checkTags();
        $this->checkUploadFolders();
        $this->checkContentLinks();
        $this->checkOldUrls($this->generatedOldUrls(), 'URL cũ (sinh từ dữ liệu)');

        if ($extraUrls !== []) {
            $this->checkOldUrls($extraUrls, 'URL cũ (danh sách nhập vào)');
        }

        return $this->results;
    }

    private function checkCategories(): void
    {
        $rows = $this->db()->table('product_categories')->get();
        $new = ProductCategory::query()->whereNotNull('legacy_id')->get()->keyBy('legacy_id');
        $problems = [];

        foreach ($rows as $row) {
            $category = $new->get($row->id);

            if (! $category) {
                $problems[] = "Nhóm #{$row->id} “{$row->name}” chưa được chuyển.";

                continue;
            }

            if ($row->parent_id && $category->parent?->legacy_id !== (int) $row->parent_id) {
                $problems[] = "Nhóm #{$row->id} “{$row->name}” sai nhóm cha.";
            }

            if ($category->is_published !== (bool) $row->publish) {
                $problems[] = "Nhóm #{$row->id} khác trạng thái hiển thị.";
            }
        }

        $this->record('Nhóm sản phẩm', $rows->count(), $problems);
    }

    private function checkBrands(): void
    {
        $keys = Brand::query()->pluck('legacy_keys')->flatten()->filter()->flip();
        $problems = [];
        $checked = 0;

        foreach (['brands' => 'brand', 'suppliers' => 'supplier'] as $table => $type) {
            foreach ($this->db()->table($table)->get() as $row) {
                $checked++;

                if (! $keys->has("{$type}:{$row->id}")) {
                    $problems[] = ucfirst($type)." #{$row->id} “".trim($row->title).'” chưa gắn vào thương hiệu nào.';
                }
            }
        }

        $this->record('Thương hiệu & nhà cung cấp', $checked, $problems, [Brand::count().' thương hiệu sau khi gộp.']);
    }

    private function checkProducts(): void
    {
        $tags = $this->db()->table('products_tags')->join('tags', 'tags.id', '=', 'products_tags.tag_id')
            ->get(['products_tags.product_id', 'tags.title'])->groupBy('product_id');
        $products = Product::query()->whereNotNull('legacy_id')->with(['category', 'brand'])->get()->keyBy('legacy_id');
        // Old products may point at brands/suppliers that were deleted on the old site; those had no brand there either.
        $existingBrands = $this->db()->table('brands')->pluck('id')->flip();
        $existingSuppliers = $this->db()->table('suppliers')->pluck('id')->flip();
        $problems = [];
        $count = 0;

        $this->db()->table('products')->orderBy('id')->chunk(200, function ($rows) use ($products, $tags, $existingBrands, $existingSuppliers, &$problems, &$count): void {
            foreach ($rows as $row) {
                $count++;
                $product = $products->get($row->id);
                $label = "Sản phẩm #{$row->id} “".Str::limit(trim($row->title), 50).'”';

                if (! $product) {
                    $problems[] = "{$label} chưa được chuyển.";

                    continue;
                }

                if ($this->normalize($product->name) !== $this->normalize($row->title)) {
                    $problems[] = "{$label}: tên khác (“{$product->name}”).";
                }

                if ($row->product_category_id && $product->category?->legacy_id !== (int) $row->product_category_id && $this->db()->table('product_categories')->where('id', $row->product_category_id)->exists()) {
                    $problems[] = "{$label}: sai nhóm sản phẩm.";
                }

                if (! $product->brand && ($existingBrands->has($row->brand_id) || $existingSuppliers->has($row->supplier_id))) {
                    $problems[] = "{$label}: mất thương hiệu.";
                }

                if ($product->is_published !== (bool) $row->publish) {
                    $problems[] = "{$label}: khác trạng thái hiển thị.";
                }

                if (filled($row->image) && $this->legacySourceExists('files/products/'.$row->image) && blank($product->image)) {
                    $problems[] = "{$label}: thiếu ảnh {$row->image}.";
                }

                if (filled($row->attachment) && $this->legacySourceExists('files/products/attachments/'.$row->attachment) && blank($product->documents)) {
                    $problems[] = "{$label}: thiếu tài liệu {$row->attachment}.";
                }

                if (filled(strip_tags((string) $row->content)) && blank($product->description) && filled(trim(html_entity_decode(strip_tags((string) $row->content)), " \u{00A0}\n\r\t"))) {
                    $problems[] = "{$label}: mất nội dung mô tả.";
                }

                foreach ($tags->get($row->id, collect()) as $tag) {
                    if (! Str::contains(mb_strtolower((string) $product->keywords), mb_strtolower(trim($tag->title)))) {
                        $problems[] = "{$label}: thiếu từ khóa “{$tag->title}”.";
                    }
                }
            }
        });

        $this->record('Sản phẩm (nội dung, ảnh, tài liệu, nhóm, thương hiệu, từ khóa)', $count, $problems);
    }

    private function checkArticles(): void
    {
        $pages = config('legacy.article_pages');
        $newsCategories = config('legacy.news_category_ids');
        $posts = Post::query()->whereNotNull('legacy_id')->pluck('id', 'legacy_id');
        $problems = [];
        $notes = [];
        $rows = $this->db()->table('articles')->get();

        foreach ($rows as $row) {
            if (isset($pages[$row->id])) {
                $notes[] = "Bài #{$row->id} “".trim($row->title)."” là trang cố định → {$pages[$row->id]} (nội dung nằm trong Cài đặt website / Dịch vụ).";
            } elseif (in_array((int) $row->article_category_id, $newsCategories, true) && ! $posts->has($row->id)) {
                $problems[] = "Bài viết #{$row->id} “".trim($row->title).'” chưa được chuyển.';
            } elseif (! in_array((int) $row->article_category_id, $newsCategories, true)) {
                $problems[] = "Bài #{$row->id} “".trim($row->title).'” thuộc chuyên mục chưa được xử lý.';
            }
        }

        $this->record('Bài viết & trang', $rows->count(), $problems, $notes);
    }

    private function checkClients(): void
    {
        $this->checkIds('customers', Client::query()->pluck('legacy_id'), 'Khách hàng tiêu biểu', 'title');
    }

    private function checkContacts(): void
    {
        $this->checkIds('contacts', ContactMessage::query()->pluck('legacy_id'), 'Tin nhắn liên hệ', 'title');
    }

    private function checkTags(): void
    {
        $existing = Redirect::query()->where('from_path', 'like', '/products/tag:%')->pluck('from_path')->flip();
        $rows = $this->db()->table('tags')->get();
        $problems = $rows->reject(fn ($tag) => blank(trim($tag->title)) || $existing->has('/products/tag:'.$tag->id))
            ->map(fn ($tag) => "Thẻ #{$tag->id} “{$tag->title}” chưa có chuyển hướng.")->values()->all();

        $this->record('Thẻ sản phẩm → chuyển hướng tìm kiếm', $rows->count(), $problems);
    }

    /**
     * Every safe file in the old upload folders must exist under public/uploads/legacy.
     */
    private function checkUploadFolders(): void
    {
        if (blank($this->webroot) || ! is_dir($this->webroot)) {
            $this->record('Tệp tải lên (files/, media/)', 0, ['Không tìm thấy thư mục webroot cũ (LEGACY_WEBROOT) — chưa kiểm tra được tệp.']);

            return;
        }

        $disk = Storage::disk('public');
        $prefix = config('legacy.uploads_prefix');
        $problems = [];
        $skipped = [];
        $checked = 0;

        foreach (['files', 'media'] as $folder) {
            $source = $this->webroot.'/'.$folder;

            if (! is_dir($source)) {
                continue;
            }

            /** @var SplFileInfo $file */
            foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source, FilesystemIterator::SKIP_DOTS)) as $file) {
                if (! $file->isFile()) {
                    continue;
                }

                $relative = $folder.'/'.ltrim(substr($file->getPathname(), strlen($source)), '/');

                if (! LegacyImporter::isSafeUpload($file)) {
                    $skipped[] = $relative;

                    continue;
                }

                $checked++;

                if (! $disk->exists($prefix.'/'.$relative)) {
                    $problems[] = "Chưa sao chép: {$relative}";
                }
            }
        }

        $notes = $skipped === [] ? [] : [count($skipped).' tệp bị bỏ qua có chủ đích (định dạng không cho phép hoặc chứa mã PHP): '.implode(', ', array_slice($skipped, 0, 8)).(count($skipped) > 8 ? ', …' : '')];

        $this->record('Tệp tải lên (files/, media/)', $checked, $problems, $notes);
    }

    /**
     * Images and links inside migrated rich text must point at something that exists.
     */
    private function checkContentLinks(): void
    {
        $disk = Storage::disk('public');
        $problems = [];
        $checked = 0;
        $sources = collect()
            ->merge(Product::query()->get(['id', 'name', 'description', 'specifications'])->flatMap(fn ($p) => [["Sản phẩm #{$p->id}", $p->description], ["Sản phẩm #{$p->id}", $p->specifications]]))
            ->merge(ProductCategory::query()->get(['id', 'description'])->map(fn ($c) => ["Nhóm #{$c->id}", $c->description]))
            ->merge(Post::query()->get(['id', 'content'])->map(fn ($p) => ["Bài viết #{$p->id}", $p->content]));

        foreach ($sources as [$label, $html]) {
            if (blank($html)) {
                continue;
            }

            preg_match_all('/\b(?:src|href)="([^"]+)"/i', $html, $matches);

            foreach (array_unique($matches[1]) as $url) {
                $checked++;
                $url = html_entity_decode($url);
                $host = parse_url($url, PHP_URL_HOST);

                if ($host && in_array(strtolower($host), config('legacy.site_hosts'), true)) {
                    $problems[] = "{$label}: còn liên kết tuyệt đối tới website cũ {$url}";
                } elseif (str_starts_with($url, '/uploads/')) {
                    if (! $disk->exists(rawurldecode(Str::after($url, '/uploads/')))) {
                        $problems[] = "{$label}: ảnh/tệp không tồn tại {$url}";
                    }
                } elseif (str_starts_with($url, '/') && ! str_starts_with($url, '//')) {
                    if ($this->status($url) >= 400) {
                        $problems[] = "{$label}: liên kết nội bộ hỏng {$url}";
                    }
                }
            }
        }

        $this->record('Ảnh & liên kết trong nội dung', $checked, array_values(array_unique($problems)));
    }

    /**
     * @param  array<int, string>  $urls
     */
    private function checkOldUrls(array $urls, string $title): void
    {
        $problems = [];
        $checked = 0;

        foreach (array_unique(array_filter(array_map('trim', $urls))) as $url) {
            $path = $this->pathOf($url);

            if ($path === null) {
                continue;
            }

            $checked++;
            $status = $this->status($path);

            if ($status === 200) {
                continue;
            }

            [$basePath, $query] = array_pad(explode('?', $path, 2), 2, '');
            parse_str($query, $params);
            $target = $this->resolver->resolve($basePath, $params);

            if ($target === null) {
                $problems[] = "{$path} → 404";

                continue;
            }

            $targetStatus = $this->status($this->pathOf($target) ?? $target);

            if ($targetStatus !== 200) {
                $problems[] = "{$path} → {$target} ({$targetStatus})";
            }
        }

        $this->record($title, $checked, $problems);
    }

    /**
     * Old URLs reconstructed from the legacy database and its menus/slides.
     *
     * @return array<int, string>
     */
    private function generatedOldUrls(): array
    {
        $db = $this->db();

        return collect(['/gioi-thieu.html', '/dich-vu.html', '/chung-chi.html', '/lien-he.html', '/tin-tuc', '/du-an', '/su-kien', '/san-pham', '/products'])
            ->merge($db->table('products')->get(['id', 'title'])->map(fn ($row) => '/p'.$row->id.'-'.Str::slug($row->title).'.html'))
            ->merge($db->table('product_categories')->pluck('id')->map(fn ($id) => '/products/category:'.$id))
            ->merge($db->table('brands')->pluck('id')->map(fn ($id) => '/products/brand:'.$id))
            ->merge($db->table('suppliers')->pluck('id')->map(fn ($id) => '/products/supplier:'.$id))
            ->merge($db->table('tags')->pluck('id')->map(fn ($id) => '/products/tag:'.$id))
            ->merge($db->table('articles')->get(['id', 'title'])->map(fn ($row) => '/a'.$row->id.'-'.Str::slug($row->title).'.html'))
            ->merge($db->table('menu_items')->where('publish', 1)->pluck('link'))
            ->merge($db->table('slides')->pluck('link'))
            ->filter()
            ->all();
    }

    /**
     * Status code the site returns for a path, following redirects inside the site.
     */
    private function status(string $path, int $hops = 3): int
    {
        // Absolute URL: a bare path with CakePHP segments ("brand:14") is rejected by Request::create().
        $response = app(Kernel::class)->handle(Request::create(rtrim(config('app.url'), '/').$path, 'GET'));
        $status = $response->getStatusCode();

        if ($status >= 300 && $status < 400 && $hops > 0) {
            $location = $this->pathOf((string) $response->headers->get('Location'));

            return $location ? $this->status($location, $hops - 1) : $status;
        }

        if ($status >= 300 && $status < 400 && str_starts_with((string) $response->headers->get('Location'), url('/uploads/'))) {
            return 200;
        }

        return $status;
    }

    /**
     * Site-relative path (with query) for a URL on the old or new domain; null for other sites.
     */
    private function pathOf(string $url): ?string
    {
        $url = trim($url);

        if (preg_match('#^https?://([^/]+)(.*)$#i', $url, $matches)) {
            $host = strtolower($matches[1]);
            $ownHosts = [...config('legacy.site_hosts'), strtolower((string) parse_url(config('app.url'), PHP_URL_HOST)), 'localhost'];

            if (! in_array(preg_replace('/:\d+$/', '', $host), $ownHosts, true)) {
                return null;
            }

            $url = $matches[2] ?: '/';
        }

        if (str_starts_with($url, '/uploads/')) {
            return Storage::disk('public')->exists(rawurldecode(strtok(Str::after($url, '/uploads/'), '?#'))) ? null : $url;
        }

        return str_starts_with($url, '/') ? strtok($url, '#') : '/'.ltrim($url, '/');
    }

    /**
     * @param  Collection<int, int|null>  $newIds
     */
    private function checkIds(string $table, Collection $newIds, string $title, string $labelColumn): void
    {
        $migrated = $newIds->filter()->flip();
        $rows = $this->db()->table($table)->get(['id', $labelColumn]);
        $problems = $rows->reject(fn ($row) => $migrated->has($row->id))
            ->map(fn ($row) => "#{$row->id} “".Str::limit(trim((string) $row->{$labelColumn}), 50).'” chưa được chuyển.')->values()->all();

        $this->record($title, $rows->count(), $problems);
    }

    private function legacySourceExists(string $relative): bool
    {
        return filled($this->webroot) && is_file($this->webroot.'/'.$relative);
    }

    private function normalize(?string $value): string
    {
        return Str::of(html_entity_decode((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8'))->replace("\u{00A0}", ' ')->squish()->rtrim('.')->lower()->toString();
    }

    /**
     * @param  array<int, string>  $problems
     * @param  array<int, string>  $notes
     */
    private function record(string $check, int $checked, array $problems, array $notes = []): void
    {
        $this->results[$check] = ['checked' => $checked, 'problems' => $problems, 'notes' => $notes];
    }

    private function db(): ConnectionInterface
    {
        return DB::connection($this->connection);
    }
}
