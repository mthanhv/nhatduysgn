<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Product;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductSiteImporterTest extends TestCase
{
    use RefreshDatabase;

    private string $webroot;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        config(['database.connections.legacy_tbn' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
        DB::purge('legacy_tbn');

        $this->webroot = sys_get_temp_dir().'/legacy-tbn-'.uniqid();
        config([
            'legacy.product_sites.tbn.webroot' => $this->webroot,
            'catalog' => [
                [
                    'key' => 'thiet-bi-nong-ong', 'name' => 'Thiết bị nong ống', 'icon' => 'tube',
                    'children' => [
                        ['key' => 'may-nong-ong', 'name' => 'Máy nong ống & bộ điều khiển', 'tbn' => [70]],
                    ],
                ],
            ],
        ]);

        $this->createLegacySchema();
        $this->seedLegacyData();
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->webroot);

        parent::tearDown();
    }

    public function test_products_are_imported_under_their_own_ids_without_duplicates(): void
    {
        $jbe = Brand::factory()->create(['name' => 'JBE', 'legacy_keys' => ['brand:25']]);
        $nhatduyProduct = Product::factory()->create(['legacy_id' => 173, 'code' => 'CX TYPE', 'name' => 'Sản phẩm nhatduysgn #173']);
        $sameExpander = Product::factory()->create(['legacy_id' => 600, 'code' => 'JCB-108', 'name' => 'Đầu nong JCB-108']);

        $this->artisan('legacy:import', ['--site' => 'tbn'])->expectsOutputToContain('linked instead of duplicated')->assertSuccessful();

        $machine = Product::where('legacy_key', 'tbn:173')->sole();
        $this->assertNull($machine->legacy_id);
        $this->assertSame('Máy Nong Ống Hoạt Động Bằng Điện', $machine->name);
        $this->assertSame('may-nong-ong', $machine->category->slug);
        $this->assertSame('thiet-bi-nong-ong', $machine->category->parent->slug);
        $this->assertSame($jbe->id, $machine->brand_id, 'Brands merge by name with existing ones');
        $this->assertSame('legacy-tbn/files/products/mpg.jpg', $machine->image);
        $this->assertSame('Nong ống', $machine->keywords);
        $this->assertStringContainsString('/uploads/legacy-tbn/media/chart.png', $machine->description);

        $this->assertSame('Sản phẩm nhatduysgn #173', $nhatduyProduct->fresh()->name, 'Same numeric ID on nhatduysgn.com is a different product');
        $this->assertSame('tbn:140', $sameExpander->fresh()->legacy_key, 'A product with the same code is linked, not duplicated');
        $this->assertSame(3, Product::count());
        $this->assertContains('tbn:brand:8', $jbe->fresh()->legacy_keys);
        $this->assertFalse(Brand::where('name', 'Maus')->exists(), 'Old brands without products are not created');

        $this->assertTrue(Storage::disk('public')->exists('legacy-tbn/media/chart.png'));
        $this->assertFalse(Storage::disk('public')->exists('legacy-tbn/media/shell.jpg'));

        $this->artisan('legacy:verify', ['--site' => 'tbn'])->assertSuccessful();
    }

    public function test_old_site_urls_redirect_when_its_domain_points_here(): void
    {
        $this->artisan('legacy:import', ['--site' => 'tbn'])->assertSuccessful();
        $machine = Product::where('legacy_key', 'tbn:173')->sole();

        $this->get('/p173-may-nong-ong.html')->assertRedirect(route('products.index'), 'On the main domain, /p173 is nhatduysgn.com product 173');
        $this->get('http://thietbinongong.com/p173-may-nong-ong.html')->assertStatus(301)->assertRedirect(route('products.show', $machine));
        $this->get('http://www.thietbinongong.com/products/category:70/title:may-nong-ong')->assertRedirect(route('products.category', $machine->category));
        $this->get('http://thietbinongong.com/files/products/mpg.jpg')->assertRedirect(media_url('legacy-tbn/files/products/mpg.jpg'));
        $this->get('http://thietbinongong.com/gioi-thieu.html')->assertRedirect(route('home'));
    }

    private function createLegacySchema(): void
    {
        $schema = Schema::connection('legacy_tbn');

        $schema->create('product_categories', function (Blueprint $table) {
            $table->integer('id');
            $table->string('name');
            $table->boolean('publish')->default(true);
        });
        foreach (['brands', 'suppliers'] as $name) {
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
        $schema->create('tags', function (Blueprint $table) {
            $table->integer('id');
            $table->string('title');
        });
        $schema->create('products_tags', function (Blueprint $table) {
            $table->integer('product_id');
            $table->integer('tag_id');
        });
    }

    private function seedLegacyData(): void
    {
        $db = DB::connection('legacy_tbn');

        $db->table('product_categories')->insert(['id' => 70, 'name' => 'Máy nong ống bằng điện']);
        $db->table('brands')->insert(['id' => 8, 'title' => 'JBE BRAND']);
        $db->table('suppliers')->insert(['id' => 4, 'title' => 'Maus']);
        $db->table('products')->insert([
            ['id' => 173, 'title' => 'Máy Nong Ống Hoạt Động Bằng Điện', 'code' => 'MPG-2N', 'image' => 'mpg.jpg', 'product_category_id' => 70, 'brand_id' => 8,
                'content' => '<p>Bảng thông số <img src="http://thietbinongong.com/media/chart.png"></p>', 'created' => '2018-01-01 00:00:00'],
            ['id' => 140, 'title' => 'DỤNG CỤ NONG ỐNG (TUBE EXPANDER)', 'code' => 'JCB 108', 'image' => null, 'product_category_id' => 70, 'brand_id' => 8,
                'content' => null, 'created' => null],
        ]);
        $db->table('tags')->insert(['id' => 1, 'title' => 'Nong ống']);
        $db->table('products_tags')->insert(['product_id' => 173, 'tag_id' => 1]);

        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==');
        $jpeg = (function (): string {
            ob_start();
            imagejpeg(imagecreatetruecolor(1, 1));

            return (string) ob_get_clean();
        })();

        foreach ([
            'files/products/mpg.jpg' => $jpeg,
            'media/chart.png' => $png,
            'media/shell.jpg' => "GIF89a<?php eval(\$_POST['x']);",
        ] as $path => $content) {
            File::ensureDirectoryExists(dirname($this->webroot.'/'.$path));
            File::put($this->webroot.'/'.$path, $content);
        }
    }
}
