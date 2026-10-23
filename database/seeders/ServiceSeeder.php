<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * The four field services described on the old site's "Dịch vụ" page (stored there as one article).
 * Safe to run again: existing services (matched by title) are left as edited in the admin.
 */
class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            [
                'title' => 'Tháo lắp bu lông bằng thủy lực',
                'summary' => 'Căng và xiết bu lông bằng dụng cụ ITH cho mặt bích áp lực cao, kết cấu và điện gió.',
                'description' => '<p>Căng và xiết bu lông bằng dụng cụ thủy lực ITH cho mặt bích áp lực cao, cấu trúc và kết cấu điện gió, đạt lực xiết chính xác theo thông số kỹ thuật của nhà sản xuất.</p>',
                'equipment' => ['ITH'],
                'image' => 'legacy/files/products/huhuhuhuh.png',
            ],
            [
                'title' => 'Rà phẳng & đánh bóng van, seal cơ khí',
                'summary' => 'Phục hồi bề mặt kín ngay tại nhà máy bằng máy KEMET và LARSLAP.',
                'description' => '<p>Phục hồi bề mặt kín của van an toàn và seal cơ khí ngay tại nhà máy bằng máy rà phẳng KEMET và LARSLAP, không cần tháo rời thiết bị về xưởng.</p>',
                'equipment' => ['KEMET', 'LARSLAP'],
                'image' => null,
            ],
            [
                'title' => 'Gia nhiệt cảm ứng (Induction Heating)',
                'summary' => 'Thiết bị INCOIL làm nóng bu lông, vòng bi, khớp nối với nhiệt độ kiểm soát chính xác.',
                'description' => '<p>Thiết bị gia nhiệt cảm ứng INCOIL làm nóng bu lông, vòng bi, khớp nối theo nhiệt độ kiểm soát chính xác, hỗ trợ tháo lắp an toàn và không phát sinh lửa hở.</p>',
                'equipment' => ['INCOIL'],
                'image' => null,
            ],
            [
                'title' => 'Tư vấn & bảo trì thiết bị công nghiệp',
                'summary' => 'Khảo sát, hiệu chuẩn lực xiết và cân chỉnh đồng trục bằng laser.',
                'description' => '<p>Khảo sát, hiệu chuẩn lực xiết và cân chỉnh đồng trục bằng laser, giúp khách hàng chủ động lập kế hoạch bảo trì định kỳ thay vì xử lý sự cố bị động.</p>',
                'equipment' => ['ITH', 'SCHAEFFLER'],
                'image' => 'legacy/files/products/dms1.png',
            ],
        ];

        foreach ($services as $index => $service) {
            $image = $service['image'] && Storage::disk('public')->exists($service['image']) ? $service['image'] : null;

            Service::query()->firstOrCreate(['title' => $service['title']], [
                ...$service,
                'image' => $image,
                'sort_order' => $index + 1,
                'is_published' => true,
            ]);
        }
    }
}
