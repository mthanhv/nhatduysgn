<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Client;
use App\Models\ContactMessage;
use App\Models\Post;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Redirect;
use App\Services\CatalogRestructurer;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LegacyImporterTest extends TestCase
{
    use RefreshDatabase;

    private string $webroot;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        config(['database.connections.legacy' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
        DB::purge('legacy');

        $this->webroot = sys_get_temp_dir().'/legacy-webroot-'.uniqid();
        config(['legacy.webroot' => $this->webroot]);

        $this->createLegacySchema();
        $this->seedLegacyData();
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->webroot);

        parent::tearDown();
    }

    public function test_full_import_maps_old_content_into_new_schema(): void
    {
        $this->artisan('legacy:import')->assertSuccessful();

        $root = ProductCategory::findByLegacyId(85);
        $child = ProductCategory::findByLegacyId(114);
        $this->assertSame('dung-cu-xiet-bu-long', $root->slug, 'Old categories are merged into the standard groups');
        $this->assertSame('dau-xiet-thuy-luc', $child->slug);
        $this->assertSame($root->id, $child->parent_id);
        $this->assertSame('wrench', $root->icon);
        $this->assertSame(0, ProductCategory::whereNotIn('slug', CatalogRestructurer::keys())->count(), 'Emptied old categories are removed');

        $ith = Brand::whereRaw('UPPER(name) = ?', ['ITH'])->sole();
        $this->assertEqualsCanonicalizing(['brand:19', 'supplier:30'], $ith->legacy_keys);
        $this->assertSame('legacy/files/suppliers/ithlogo.jpeg', $ith->logo);
        $this->assertSame(1, Brand::count(), 'Old brand + supplier rows for ITH merge into one brand');

        $product = Product::where('legacy_id', 423)->sole();
        $this->assertSame('Đầu xiết thủy lực loại xoay', $product->name);
        $this->assertSame($child->id, $product->product_category_id);
        $this->assertSame($ith->id, $product->brand_id);
        $this->assertSame('legacy/files/products/ith-cx.jpg', $product->image);
        $this->assertSame([['title' => 'cx.pdf', 'file' => 'legacy/files/products/attachments/cx.pdf']], $product->documents);
        $this->assertStringNotContainsString('<script', $product->description);
        $this->assertStringContainsString('/uploads/legacy/media/cx-chart.png', $product->description);
        $this->assertSame(57, $product->view_count);
        $this->assertSame('Bôi trơn', $product->keywords, 'Old product tags become search keywords');
        $this->assertSame('2017-05-05', $product->created_at->toDateString());

        $this->assertSame(1, Post::count(), 'Only news/project/event articles become posts');
        $this->assertSame(63, Post::sole()->legacy_id);

        $this->assertSame('PTSC', Client::sole()->name);
        $this->assertNotNull(ContactMessage::sole()->read_at, 'Old messages are not shown as unread');
        $this->assertSame('/san-pham?q=B%C3%B4i%20tr%C6%A1n', Redirect::where('from_path', '/products/tag:10')->sole()->to_url);

        $this->assertTrue(Storage::disk('public')->exists('legacy/files/products/ith-cx.jpg'));
        $this->assertFalse(Storage::disk('public')->exists('legacy/media/file.php<?.pdf'), 'Disguised PHP files are never copied');
        $this->assertFalse(Storage::disk('public')->exists('legacy/media/fake.jpg'), 'Images containing PHP code are never copied');
        $this->assertFalse(Storage::disk('public')->exists('legacy/media/not-really.pdf'), 'Files whose real type does not match the extension are never copied');
        $this->assertTrue(Storage::disk('public')->exists('legacy/media/cx-chart.png'), 'Real images whose binary data happens to contain "<?=" are kept');
    }

    public function test_verify_passes_after_a_full_import_and_reports_missing_records(): void
    {
        $this->artisan('legacy:import')->assertSuccessful();
        $this->artisan('legacy:verify')->assertSuccessful();

        Product::where('legacy_id', 423)->delete();
        Storage::disk('public')->delete('legacy/files/customers/ptsc.png');

        $this->artisan('legacy:verify')
            ->expectsOutputToContain('Sản phẩm #423')
            ->expectsOutputToContain('files/customers/ptsc.png')
            ->assertFailed();
    }

    public function test_import_is_idempotent_and_can_keep_admin_edits(): void
    {
        $this->artisan('legacy:import', ['--step' => ['categories', 'brands', 'products']])->assertSuccessful();
        Product::where('legacy_id', 423)->sole()->update(['name' => 'Tên đã sửa trong admin']);

        $this->artisan('legacy:import', ['--step' => ['categories', 'brands', 'products'], '--skip-existing' => true])->assertSuccessful();
        $this->assertSame('Tên đã sửa trong admin', Product::where('legacy_id', 423)->sole()->name);
        $this->assertSame(1, Product::count());

        $this->artisan('legacy:import', ['--step' => ['products']])->assertSuccessful();
        $this->assertSame('Đầu xiết thủy lực loại xoay', Product::where('legacy_id', 423)->sole()->name);
    }

    public function test_unknown_step_is_rejected(): void
    {
        $this->artisan('legacy:import', ['--step' => ['users']])->assertFailed();
    }

    private function createLegacySchema(): void
    {
        $schema = Schema::connection('legacy');

        $schema->create('product_categories', function (Blueprint $table) {
            $table->integer('id');
            $table->string('image')->nullable();
            $table->string('name');
            $table->integer('parent_id')->nullable();
            $table->integer('lft')->default(0);
            $table->boolean('featured')->default(false);
            $table->text('description')->nullable();
            $table->boolean('publish')->default(true);
        });
        foreach (['brands', 'suppliers', 'customers'] as $name) {
            $schema->create($name, function (Blueprint $table) {
                $table->integer('id');
                $table->string('image')->nullable();
                $table->string('title');
                $table->boolean('publish')->default(true);
            });
        }
        $schema->create('products', function (Blueprint $table) {
            $table->integer('id');
            $table->string('image')->nullable();
            $table->string('title');
            $table->string('code')->nullable();
            $table->text('content')->nullable();
            $table->integer('view_count')->default(0);
            $table->integer('product_category_id')->nullable();
            $table->string('attachment')->nullable();
            $table->string('meta_description')->nullable();
            $table->boolean('publish')->default(true);
            $table->integer('ordering')->default(0);
            $table->dateTime('created')->nullable();
            $table->dateTime('updated')->nullable();
            $table->integer('brand_id')->nullable();
            $table->integer('supplier_id')->nullable();
            $table->boolean('featured')->default(false);
            $table->string('origin')->nullable();
            $table->text('specification')->nullable();
        });
        $schema->create('articles', function (Blueprint $table) {
            $table->integer('id');
            $table->string('image')->nullable();
            $table->string('title');
            $table->text('content')->nullable();
            $table->integer('view_count')->default(0);
            $table->integer('article_category_id');
            $table->string('meta_description')->nullable();
            $table->dateTime('created')->nullable();
            $table->dateTime('updated')->nullable();
        });
        $schema->create('contacts', function (Blueprint $table) {
            $table->integer('id');
            $table->string('name');
            $table->string('company')->nullable();
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('title');
            $table->text('content');
            $table->string('ip')->nullable();
            $table->dateTime('created')->nullable();
        });
        $schema->create('tags', function (Blueprint $table) {
            $table->integer('id');
            $table->string('title');
        });
        $schema->create('products_tags', function (Blueprint $table) {
            $table->integer('product_id');
            $table->integer('tag_id');
        });
        $schema->create('menu_items', function (Blueprint $table) {
            $table->integer('id');
            $table->string('link');
            $table->boolean('publish')->default(true);
        });
        $schema->create('slides', function (Blueprint $table) {
            $table->integer('id');
            $table->string('link')->nullable();
        });
    }

    private function seedLegacyData(): void
    {
        $db = DB::connection('legacy');

        $db->table('product_categories')->insert([
            ['id' => 85, 'name' => 'Dụng Cụ Tháo Lắp Bu Lông Thủy Lực ITH', 'parent_id' => null, 'lft' => 1, 'featured' => true, 'publish' => true],
            ['id' => 114, 'name' => 'NHÓM DỤNG CỤ THỦY LỰC LOẠI XOAY.', 'parent_id' => 85, 'lft' => 2, 'featured' => false, 'publish' => true],
        ]);
        $db->table('brands')->insert(['id' => 19, 'title' => 'ITH BRAND', 'publish' => true]);
        $db->table('suppliers')->insert(['id' => 30, 'title' => "ITH BOLTING TECHNOLOGY \t", 'image' => 'ithlogo.jpeg', 'publish' => true]);
        $db->table('customers')->insert(['id' => 3, 'title' => 'PTSC', 'image' => 'ptsc.png', 'publish' => true]);
        $db->table('products')->insert([
            'id' => 423, 'title' => 'Đầu xiết thủy lực loại xoay', 'code' => 'CX TYPE', 'image' => 'ith-cx.jpg', 'attachment' => 'cx.pdf',
            'content' => '<p style="color:red" onclick="steal()">Bảng lực <img src="http://www.nhatduysgn.com/media/cx-chart.png"></p><script>alert(1)</script>',
            'specification' => '<table><tr><td>CX-5</td><td>5.000 Nm</td></tr></table>',
            'view_count' => 57, 'product_category_id' => 114, 'brand_id' => 19, 'supplier_id' => 30, 'publish' => true,
            'created' => '2017-05-05 10:00:00', 'updated' => '2020-01-01 10:00:00', 'origin' => 'Made in Germany',
        ]);
        $db->table('articles')->insert([
            ['id' => 1, 'title' => 'LỜI GIỚI THIỆU', 'content' => '<p>Giới thiệu</p>', 'article_category_id' => 1, 'created' => '2015-04-13 00:00:00'],
            ['id' => 63, 'title' => 'Tiềm năng phát triển điện gió', 'content' => '<p>Năng lượng gió…</p>', 'article_category_id' => 2, 'created' => '2020-02-28 00:00:00'],
        ]);
        $db->table('contacts')->insert(['id' => 1, 'name' => 'Khách', 'email' => 'khach@example.com', 'title' => 'Hỏi giá', 'content' => 'Nội dung', 'created' => '2024-01-01 08:00:00']);
        $db->table('tags')->insert(['id' => 10, 'title' => 'Bôi trơn']);
        $db->table('products_tags')->insert(['product_id' => 423, 'tag_id' => 10]);
        $db->table('menu_items')->insert(['id' => 88, 'link' => '/articles/index/2/news.html', 'publish' => true]);
        $db->table('slides')->insert(['id' => 1, 'link' => 'http://nhatduysgn.com/products/brand:19/title:ith-brand']);

        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==');
        $jpeg = (function (): string {
            ob_start();
            imagejpeg(imagecreatetruecolor(1, 1));

            return (string) ob_get_clean();
        })();

        foreach ([
            'files/products/ith-cx.jpg' => $jpeg,
            'files/products/attachments/cx.pdf' => "%PDF-1.4\n%âãÏÓ\n1 0 obj<<>>endobj\ntrailer<<>>\n%%EOF",
            'files/suppliers/ithlogo.jpeg' => $jpeg,
            'files/customers/ptsc.png' => $png,
            'media/cx-chart.png' => $png.'<?=random binary bytes',
            'media/file.php<?.pdf' => '<?php system($_GET["c"]);',
            'media/fake.jpg' => "GIF89a<?php eval(\$_POST['x']);",
            'media/not-really.pdf' => '<html>phishing page</html>',
        ] as $path => $content) {
            File::ensureDirectoryExists(dirname($this->webroot.'/'.$path));
            File::put($this->webroot.'/'.$path, $content);
        }
    }
}
