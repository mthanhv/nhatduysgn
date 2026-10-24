<?php

namespace Tests\Feature;

use App\Services\Thumbnailer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ThumbnailTest extends TestCase
{
    use RefreshDatabase;

    private string $root;

    protected function setUp(): void
    {
        parent::setUp();

        $this->root = sys_get_temp_dir().'/thumbs-'.uniqid();
        config(['filesystems.disks.public.root' => $this->root, 'filesystems.disks.public.url' => '/uploads']);
        Storage::forgetDisk('public');

        $image = imagecreatetruecolor(2400, 1200);
        File::ensureDirectoryExists($this->root.'/legacy/files/products');
        imagepng($image, $this->root.'/legacy/files/products/ảnh lớn.png');
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->root);

        parent::tearDown();
    }

    public function test_large_uploads_are_served_as_resized_webp(): void
    {
        $url = thumb_url('legacy/files/products/ảnh lớn.png', 600);
        $this->assertSame('/uploads/_thumbs/640/legacy/files/products/ảnh lớn.png.webp', $url);

        $this->get('/uploads/_thumbs/640/legacy/files/products/'.rawurlencode('ảnh lớn.png').'.webp')
            ->assertOk()
            ->assertHeader('Content-Type', 'image/webp');

        [$width, $height] = getimagesize($this->root.'/_thumbs/640/legacy/files/products/ảnh lớn.png.webp');
        $this->assertSame([640, 320], [$width, $height]);
    }

    public function test_only_known_sizes_and_real_uploads_are_resized(): void
    {
        $this->get('/uploads/_thumbs/777/legacy/files/products/x.png.webp')->assertNotFound();
        $this->get('/uploads/_thumbs/640/../../.env.webp')->assertNotFound();
        $this->get('/uploads/_thumbs/640/legacy/khong-co.png.webp')->assertNotFound();
        $this->assertNull(app(Thumbnailer::class)->make(640, '_thumbs/640/a.png'));
    }

    public function test_files_that_cannot_be_resized_keep_their_original_url(): void
    {
        $this->assertSame('/uploads/brands/logo.svg', thumb_url('brands/logo.svg', 320));
        $this->assertSame('https://cdn.example.com/a.jpg', thumb_url('https://cdn.example.com/a.jpg', 320));
        $this->assertSame('/images/hero.jpg', thumb_url(null, 1280, '/images/hero.jpg'));
    }
}
