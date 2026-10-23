<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\Client;
use App\Models\ContactMessage;
use App\Models\Post;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Redirect;
use Closure;
use FilesystemIterator;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * Copies content from the old CakePHP site (database + upload folders) into the new schema.
 *
 * Every record keeps its old ID (legacy_id / legacy_keys), so the import can be re-run safely and the
 * redirect layer can map old URLs to new pages.
 */
class LegacyImporter
{
    public const STEPS = ['files', 'categories', 'brands', 'products', 'posts', 'clients', 'contacts', 'tags'];

    /** @var array<int, int> */
    private array $categoryMap = [];

    /** @var array<string, int> */
    private array $brandMap = [];

    private ?Closure $output = null;

    /** @var array<int, string>|null */
    private ?array $productTags = null;

    public function __construct(
        private LegacyHtml $html,
        private string $connection = 'legacy',
        private ?string $webroot = null,
        private bool $skipExisting = false,
    ) {
        $this->webroot ??= config('legacy.webroot');
    }

    public function skipExisting(bool $skip = true): self
    {
        $this->skipExisting = $skip;

        return $this;
    }

    public function onProgress(Closure $output): self
    {
        $this->output = $output;

        return $this;
    }

    /**
     * @param  array<int, string>  $steps
     * @return array<string, int> number of records (or files) handled per step
     */
    public function run(array $steps = self::STEPS): array
    {
        $results = [];

        foreach (self::STEPS as $step) {
            if (! in_array($step, $steps, true)) {
                continue;
            }

            $this->say("→ {$step}");
            $results[$step] = $this->{'import'.Str::studly($step)}();
        }

        return $results;
    }

    public function importFiles(): int
    {
        if (blank($this->webroot) || ! is_dir($this->webroot)) {
            $this->say('  Bỏ qua: không tìm thấy thư mục webroot cũ (LEGACY_WEBROOT).');

            return 0;
        }

        $copied = 0;
        $disk = Storage::disk('public');
        $prefix = config('legacy.uploads_prefix');

        foreach (['files', 'media'] as $folder) {
            $source = rtrim($this->webroot, '/').'/'.$folder;

            if (! is_dir($source)) {
                continue;
            }

            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source, FilesystemIterator::SKIP_DOTS));

            /** @var SplFileInfo $file */
            foreach ($iterator as $file) {
                if (! $file->isFile() || $file->isLink() || ! self::isSafeUpload($file)) {
                    continue;
                }

                $relative = $prefix.'/'.$folder.'/'.ltrim(substr($file->getPathname(), strlen($source)), '/');
                $target = $disk->path($relative);

                if (is_file($target) && filesize($target) === $file->getSize()) {
                    continue;
                }

                File::ensureDirectoryExists(dirname($target));
                File::copy($file->getPathname(), $target);
                $copied++;
            }
        }

        return $copied;
    }

    public function importCategories(): int
    {
        $rows = $this->db()->table('product_categories')->orderBy('lft')->get();
        $icons = config('legacy.category_icons', []);
        $featured = config('legacy.featured_category_ids', []);

        foreach ($rows as $row) {
            $existing = ProductCategory::query()->where('legacy_id', $row->id)->first();

            if ($existing && $this->skipExisting) {
                $this->categoryMap[$row->id] = $existing->id;

                continue;
            }

            $category = $existing ?? new ProductCategory(['legacy_id' => $row->id]);
            $category->fill([
                'name' => $this->cleanTitle($row->name),
                'description' => $this->html->clean($row->description ?? null),
                'image' => $this->legacyFile('categories', $row->image ?? null),
                'icon' => $category->icon ?? ($icons[$row->id] ?? null),
                'is_featured' => in_array($row->id, $featured, true) || (bool) $row->featured,
                'is_published' => (bool) $row->publish,
                'sort_order' => (int) $row->lft,
            ])->save();

            $this->categoryMap[$row->id] = $category->id;
        }

        foreach ($rows as $row) {
            if ($row->parent_id && isset($this->categoryMap[$row->parent_id])) {
                ProductCategory::query()->whereKey($this->categoryMap[$row->id])->update(['parent_id' => $this->categoryMap[$row->parent_id]]);
            }
        }

        return $rows->count();
    }

    public function importBrands(): int
    {
        $count = 0;
        $featured = config('legacy.featured_brands', []);

        foreach (['brands' => 'brand', 'suppliers' => 'supplier'] as $table => $type) {
            foreach ($this->db()->table($table)->orderBy('id')->get() as $row) {
                $name = $this->brandName($row->title);
                $brand = Brand::query()->whereRaw('UPPER(name) = ?', [mb_strtoupper($name)])->first() ?? new Brand(['name' => $name]);
                $keys = collect($brand->legacy_keys ?? [])->push("{$type}:{$row->id}")->unique()->values()->all();
                $logo = $type === 'supplier' ? $this->legacyFile('suppliers', $row->image ?? null) : null;

                if ($brand->exists && $this->skipExisting) {
                    $brand->forceFill(['legacy_keys' => $keys])->save();
                } else {
                    $brand->fill([
                        'legacy_keys' => $keys,
                        'logo' => $brand->logo ?: $logo,
                        'is_featured' => in_array(mb_strtoupper($name), $featured, true),
                        'is_published' => $brand->exists ? ($brand->is_published || (bool) $row->publish) : (bool) $row->publish,
                    ])->save();
                }

                $this->brandMap["{$type}:{$row->id}"] = $brand->id;
                $count++;
            }
        }

        return $count;
    }

    public function importProducts(): int
    {
        $this->ensureMaps();
        $count = 0;

        $this->db()->table('products')->orderBy('id')->chunk(100, function ($rows) use (&$count): void {
            foreach ($rows as $row) {
                $existing = Product::query()->where('legacy_id', $row->id)->first();

                if ($existing && $this->skipExisting) {
                    continue;
                }

                $attachment = $this->legacyFile('products/attachments', $row->attachment ?? null);
                $keywords = $this->productTags()[$row->id] ?? null;

                Product::withoutTimestamps(function () use ($row, $existing, $attachment, $keywords): void {
                    $product = $existing ?? new Product(['legacy_id' => $row->id]);
                    $product->fill([
                        'name' => $this->cleanTitle($row->title),
                        'code' => $this->nullableTrim($row->code),
                        'origin' => $this->nullableTrim($row->origin),
                        'description' => $this->html->clean($row->content),
                        'specifications' => $this->html->clean($row->specification),
                        'keywords' => $keywords,
                        'image' => $this->legacyFile('products', $row->image),
                        'documents' => $attachment ? [['title' => $row->attachment, 'file' => $attachment]] : null,
                        'product_category_id' => $this->categoryMap[$row->product_category_id] ?? null,
                        'brand_id' => $this->brandMap["brand:{$row->brand_id}"] ?? $this->brandMap["supplier:{$row->supplier_id}"] ?? null,
                        'is_featured' => (bool) $row->featured,
                        'is_published' => (bool) $row->publish,
                        'sort_order' => (int) $row->ordering,
                        'meta_description' => $this->nullableTrim($row->meta_description),
                    ]);
                    $product->view_count = (int) $row->view_count;
                    $product->created_at = $this->date($row->created) ?? now();
                    $product->updated_at = $this->date($row->updated) ?? $product->created_at;
                    $product->save();
                });

                $count++;
            }
        });

        $this->summarizeCategories();

        return $count;
    }

    public function importPosts(): int
    {
        $rows = $this->db()->table('articles')->whereIn('article_category_id', config('legacy.news_category_ids'))->orderBy('id')->get();

        foreach ($rows as $row) {
            $existing = Post::query()->where('legacy_id', $row->id)->first();

            if ($existing && $this->skipExisting) {
                continue;
            }

            Post::withoutTimestamps(function () use ($row, $existing): void {
                $content = $this->html->clean($row->content);
                $post = $existing ?? new Post(['legacy_id' => $row->id]);
                $post->fill([
                    'title' => $this->cleanTitle($row->title),
                    'content' => $content,
                    'excerpt' => Str::limit(Str::squish(html_entity_decode(strip_tags((string) $content))), 220),
                    'cover' => $this->legacyFile('articles', $row->image),
                    'published_at' => $this->date($row->created),
                    'is_published' => true,
                    'meta_description' => $this->nullableTrim($row->meta_description),
                ]);
                $post->view_count = (int) $row->view_count;
                $post->created_at = $this->date($row->created) ?? now();
                $post->updated_at = $this->date($row->updated) ?? $post->created_at;
                $post->save();
            });
        }

        return $rows->count();
    }

    public function importClients(): int
    {
        $rows = $this->db()->table('customers')->orderBy('id')->get();

        foreach ($rows as $row) {
            if ($this->skipExisting && Client::query()->where('legacy_id', $row->id)->exists()) {
                continue;
            }

            Client::query()->updateOrCreate(['legacy_id' => $row->id], [
                'name' => $this->cleanTitle($row->title),
                'logo' => $this->legacyFile('customers', $row->image),
                'is_published' => (bool) $row->publish,
                'sort_order' => $row->id,
            ]);
        }

        return $rows->count();
    }

    public function importContacts(): int
    {
        $count = 0;

        $this->db()->table('contacts')->orderBy('id')->chunk(200, function ($rows) use (&$count): void {
            foreach ($rows as $row) {
                if (ContactMessage::query()->where('legacy_id', $row->id)->exists()) {
                    continue;
                }

                $created = $this->date($row->created) ?? now();

                ContactMessage::withoutTimestamps(fn () => (new ContactMessage)->forceFill([
                    'legacy_id' => $row->id,
                    'topic' => 'other',
                    'name' => Str::limit($this->nullableTrim($row->name) ?? 'Không tên', 250, ''),
                    'company' => $this->nullableTrim($row->company),
                    'email' => Str::limit($this->nullableTrim($row->email) ?? '', 250, ''),
                    'phone' => Str::limit($this->nullableTrim($row->phone) ?? '', 50, '') ?: null,
                    'address' => $this->nullableTrim($row->address),
                    'subject' => Str::limit($this->nullableTrim($row->title) ?? '(Không tiêu đề)', 250, ''),
                    'message' => (string) $row->content,
                    'ip_address' => $this->nullableTrim($row->ip),
                    // Old messages were handled on the old site; keep them out of the unread inbox.
                    'read_at' => $created,
                    'created_at' => $created,
                    'updated_at' => $created,
                ])->save());

                $count++;
            }
        });

        return $count;
    }

    /**
     * Old tag listing URLs (/products/tag:12/...) become product searches for the tag name.
     */
    public function importTags(): int
    {
        $rows = $this->db()->table('tags')->get();

        foreach ($rows as $row) {
            $title = $this->nullableTrim($row->title);

            if ($title === null) {
                continue;
            }

            Redirect::query()->updateOrCreate(['from_path' => '/products/tag:'.$row->id], [
                'to_url' => route('products.index', ['q' => $title], false),
                'status_code' => 301,
                'is_active' => true,
                'note' => 'Thẻ sản phẩm cũ: '.$title,
            ]);
        }

        return $rows->count();
    }

    /**
     * Fills empty category summaries with the main brands of their products, e.g. "ITH · Schaeffler".
     */
    private function summarizeCategories(): void
    {
        ProductCategory::query()->whereNull('summary')->each(function (ProductCategory $category): void {
            $brands = Product::query()
                ->whereIn('product_category_id', $category->descendantAndSelfIds())
                ->whereNotNull('brand_id')
                ->select('brand_id', DB::raw('count(*) as total'))
                ->groupBy('brand_id')
                ->orderByDesc('total')
                ->limit(3)
                ->pluck('brand_id');

            $names = Brand::query()->whereIn('id', $brands)->pluck('name')->implode(' · ');

            if ($names !== '') {
                $category->forceFill(['summary' => $names])->save();
            }
        });
    }

    /**
     * Old product tags as "tag one, tag two" per legacy product ID.
     *
     * @return array<int, string>
     */
    private function productTags(): array
    {
        return $this->productTags ??= $this->db()->table('products_tags')
            ->join('tags', 'tags.id', '=', 'products_tags.tag_id')
            ->orderBy('tags.title')
            ->get(['products_tags.product_id', 'tags.title'])
            ->groupBy('product_id')
            ->map(fn ($tags) => $tags->pluck('title')->map(fn ($title) => trim($title))->filter()->unique()->implode(', '))
            ->all();
    }

    private function ensureMaps(): void
    {
        if ($this->categoryMap === []) {
            $this->categoryMap = ProductCategory::query()->whereNotNull('legacy_id')->pluck('id', 'legacy_id')->all();
        }

        if ($this->brandMap === []) {
            foreach (Brand::query()->whereNotNull('legacy_keys')->get(['id', 'legacy_keys']) as $brand) {
                foreach ($brand->legacy_keys as $key) {
                    $this->brandMap[$key] = $brand->id;
                }
            }
        }
    }

    /**
     * Only plain documents/images are copied, never anything that could execute (the old site was hit by webshells).
     *
     * A file is rejected when its name looks executable, its extension is not whitelisted, its real type does not
     * match its extension, or it contains PHP code anywhere.
     */
    public static function isSafeUpload(SplFileInfo $file): bool
    {
        $name = $file->getFilename();
        $extension = strtolower($file->getExtension());

        if (str_starts_with($name, '.') || preg_match('/\.(php\d?|phtml|phar|pht|phps|cgi|pl|py|sh|asp|aspx|jsp|htaccess)(\.|$)/i', $name) || preg_match('/[<>?*|"]/', $name)) {
            return false;
        }

        if (! in_array($extension, config('legacy.allowed_extensions'), true)) {
            return false;
        }

        $mime = (string) (new \finfo(FILEINFO_MIME_TYPE))->file($file->getPathname());
        $expected = match (true) {
            in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'], true) => 'image/',
            $extension === 'pdf' => 'application/pdf',
            default => null,
        };

        if ($expected !== null && $file->getSize() > 0 && ! str_starts_with($mime, $expected)) {
            return false;
        }

        $handle = fopen($file->getPathname(), 'rb');
        $previous = '';

        while ($handle && ! feof($handle)) {
            $chunk = $previous.fread($handle, 1024 * 1024);

            if (stripos($chunk, '<?php') !== false || ($extension === 'svg' && preg_match('/<script\b|\bon[a-z]+\s*=/i', $chunk))) {
                fclose($handle);

                return false;
            }

            $previous = substr($chunk, -16);
        }

        if ($handle) {
            fclose($handle);
        }

        return true;
    }

    private function legacyFile(string $folder, ?string $filename): ?string
    {
        $filename = $this->nullableTrim($filename);

        if ($filename === null || str_contains($filename, '..') || str_contains($filename, '/')) {
            return null;
        }

        $path = config('legacy.uploads_prefix').'/files/'.$folder.'/'.$filename;

        return Storage::disk('public')->exists($path) ? $path : null;
    }

    private function brandName(?string $title): string
    {
        $name = Str::of((string) $title)->replace(["\t", "\u{00A0}"], ' ')->squish()->replaceMatches('/\s+BRAND$/i', '')->trim(' -')->toString();
        $aliases = config('legacy.brand_aliases', []);

        return $aliases[mb_strtoupper($name)] ?? $name;
    }

    private function cleanTitle(?string $title): string
    {
        return Str::of(html_entity_decode((string) $title, ENT_QUOTES | ENT_HTML5, 'UTF-8'))
            ->replace(["\t", "\u{00A0}"], ' ')
            ->squish()
            ->rtrim('.')
            ->trim()
            ->toString() ?: 'Không tên';
    }

    private function nullableTrim(?string $value): ?string
    {
        $value = trim(html_entity_decode((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8'));

        return $value === '' ? null : $value;
    }

    private function date(?string $value): ?Carbon
    {
        if (blank($value) || str_starts_with($value, '0000')) {
            return null;
        }

        return Carbon::parse($value);
    }

    private function db(): ConnectionInterface
    {
        return DB::connection($this->connection);
    }

    private function say(string $message): void
    {
        if ($this->output) {
            ($this->output)($message);
        }
    }
}
