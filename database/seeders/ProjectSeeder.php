<?php

namespace Database\Seeders;

use App\Models\Project;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * SAMPLE projects for demos and design review only — client names are generic on purpose.
 * Not called by DatabaseSeeder; run explicitly: `php artisan db:seed --class=ProjectSeeder`.
 * Replace with real case studies in the admin before go-live.
 */
class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        $projects = [
            [
                'title' => 'Xiết bu lông tháp và mâm xoay tua-bin điện gió',
                'industry' => 'Điện gió',
                'client' => 'Dự án điện gió ven biển — khu vực Đồng bằng sông Cửu Long',
                'year' => '2024',
                'summary' => 'Cung cấp thiết bị và kỹ thuật viên xiết bu lông chân tháp, mặt bích thân tháp và mâm xoay cho cụm tua-bin trong giai đoạn lắp dựng.',
                'tags' => ['Tháo lắp bu lông thủy lực', 'ITH', 'Súng xiết EDS'],
                'cover' => 'legacy/files/slides/maintenance-free-bolts-for-windturbines-from-ihf-industrial-highload-fasteners.png',
                'content' => '<h2>Bối cảnh</h2><p>Mỗi tua-bin có hàng trăm bu lông cường độ cao ở chân tháp, các mặt bích thân tháp và mâm xoay, cần đạt lực xiết đồng đều theo quy trình của nhà sản xuất tua-bin.</p><h2>Giải pháp</h2><ul><li>Dụng cụ căng bu lông thủy lực ITH cho bu lông chân tháp và mặt bích.</li><li>Súng xiết điện ITH EDS cho mâm xoay, giảm thời gian thao tác trên cao.</li><li>Ghi nhận lực xiết từng bu lông và lập biên bản bàn giao.</li></ul><h2>Kết quả</h2><p>Hoàn thành theo tiến độ lắp dựng, hồ sơ lực xiết đầy đủ phục vụ nghiệm thu.</p>',
            ],
            [
                'title' => 'Bảo trì mặt bích áp lực cao trên giàn khai thác',
                'industry' => 'Dầu khí',
                'client' => 'Đơn vị khai thác dầu khí ngoài khơi',
                'year' => '2023',
                'summary' => 'Tháo lắp và xiết lại bu lông mặt bích đường ống công nghệ trong đợt dừng giàn bảo dưỡng định kỳ.',
                'tags' => ['Tháo lắp bu lông thủy lực', 'ITH', 'Căng bu lông ANSI'],
                'cover' => 'legacy/files/articles/may-cat-ong-nhat-duy-tag.jpg',
                'content' => '<h2>Bối cảnh</h2><p>Thời gian dừng giàn ngắn, khối lượng mặt bích lớn và yêu cầu an toàn nghiêm ngặt đối với hệ thống áp lực cao.</p><h2>Giải pháp</h2><ul><li>Dụng cụ căng bu lông ITH dòng ES theo chuẩn ANSI cho mặt bích áp lực cao.</li><li>Đầu xiết thủy lực loại xoay dòng CX cho các vị trí lắp đặt chật hẹp.</li><li>Kiểm tra, hiệu chuẩn thiết bị trước khi ra giàn.</li></ul><h2>Kết quả</h2><p>Các mối nối được xiết đúng lực thiết kế, không phát sinh rò rỉ khi chạy lại hệ thống.</p>',
            ],
            [
                'title' => 'Rà phẳng van an toàn trong đợt bảo dưỡng tổng thể',
                'industry' => 'Lọc hóa dầu',
                'client' => 'Nhà máy lọc hóa dầu — khu vực miền Trung',
                'year' => '2023',
                'summary' => 'Phục hồi bề mặt làm kín van an toàn tại hiện trường bằng máy rà phẳng, rút ngắn thời gian gửi van về xưởng.',
                'tags' => ['Rà phẳng van & seal', 'KEMET', 'LARSLAP'],
                'cover' => 'legacy/files/articles/long-son-2-15750220577471389865147.jpg',
                'content' => '<h2>Bối cảnh</h2><p>Số lượng van an toàn cần kiểm định và phục hồi lớn trong khoảng thời gian dừng nhà máy giới hạn.</p><h2>Giải pháp</h2><ul><li>Bố trí máy rà phẳng và bộ dụng cụ rà van ngay tại xưởng hiện trường của nhà máy.</li><li>Sử dụng vật liệu mài phù hợp cho từng loại vật liệu đĩa van và đế van.</li></ul><h2>Kết quả</h2><p>Van được phục hồi và trả về vị trí trong thời gian dừng máy, giảm chi phí vận chuyển và thay thế.</p>',
            ],
            [
                'title' => 'Cắt và vát mép ống cho hạng mục thay thế ống lò hơi',
                'industry' => 'Nhà máy điện',
                'client' => 'Nhà máy nhiệt điện — khu vực miền Nam',
                'year' => '2024',
                'summary' => 'Cung cấp máy cắt, vát mép ống JBE và hướng dẫn vận hành cho đội thi công thay thế ống lò hơi.',
                'tags' => ['Thiết bị ngành ống thép', 'JBE'],
                'cover' => 'legacy/files/slides/cold-cutting.jpg',
                'content' => '<h2>Bối cảnh</h2><p>Hạng mục thay thế ống lò hơi đòi hỏi mép vát chính xác để đảm bảo chất lượng mối hàn, trong không gian thao tác hạn chế.</p><h2>Giải pháp</h2><ul><li>Máy cắt, vát mép ống JBE kẹp trong lòng ống, không phát sinh nhiệt.</li><li>Dụng cụ kẹp căn chỉnh ống hỗ trợ ghép mối hàn.</li><li>Đào tạo vận hành cho đội thi công tại công trường.</li></ul><h2>Kết quả</h2><p>Mép vát đồng đều, giảm thời gian chuẩn bị mối hàn so với phương pháp mài tay.</p>',
            ],
            [
                'title' => 'Căng bu lông bằng thủy lực cho cụm tua-bin khí',
                'industry' => 'Nhà máy điện',
                'client' => 'Nhà máy điện chu trình hỗn hợp',
                'year' => '2025',
                'summary' => 'Tháo và lắp bu lông vỏ tua-bin bằng dụng cụ căng thủy lực, kết hợp gia nhiệt cảm ứng cho các bu lông kẹt.',
                'tags' => ['Tháo lắp bu lông thủy lực', 'Gia nhiệt cảm ứng', 'ITH', 'INCOIL'],
                'cover' => 'legacy/files/slides/tighten-and-loosen-industrial-bolted-joints-with-ith-bolting-tools.png',
                'content' => '<h2>Bối cảnh</h2><p>Bu lông vỏ tua-bin làm việc ở nhiệt độ cao lâu ngày, khó tháo bằng phương pháp thông thường.</p><h2>Giải pháp</h2><ul><li>Gia nhiệt cảm ứng INCOIL để nới bu lông có kiểm soát, không dùng lửa hở.</li><li>Dụng cụ căng bu lông thủy lực ITH để lắp lại đúng lực kéo yêu cầu.</li></ul><h2>Kết quả</h2><p>Tháo lắp an toàn, không làm hỏng ren bu lông và lỗ ren trên vỏ.</p>',
            ],
            [
                'title' => 'Cân chỉnh đồng trục và chuẩn hóa bôi trơn vòng bi',
                'industry' => 'Đạm & hóa chất',
                'client' => 'Nhà máy sản xuất phân đạm',
                'year' => '2025',
                'summary' => 'Cân chỉnh đồng trục bằng laser cho cụm bơm, quạt và chuẩn hóa mỡ bôi trơn vòng bi theo điều kiện vận hành.',
                'tags' => ['Tư vấn & bảo trì', 'SCHAEFFLER', 'KLÜBER'],
                'cover' => 'legacy/files/products/screen-shot-2023-02-22-at-145541.png',
                'content' => '<h2>Bối cảnh</h2><p>Một số cụm bơm và quạt có độ rung cao, vòng bi phải thay sớm hơn tuổi thọ thiết kế.</p><h2>Giải pháp</h2><ul><li>Khảo sát tình trạng và cân chỉnh đồng trục bằng thiết bị laser Schaeffler – FAG.</li><li>Đề xuất mỡ bôi trơn Klüber phù hợp nhiệt độ, tốc độ và môi trường làm việc.</li><li>Lập kế hoạch bôi trơn định kỳ cho đội bảo trì nhà máy.</li></ul><h2>Kết quả</h2><p>Độ rung giảm sau cân chỉnh, nhà máy có quy trình bôi trơn thống nhất cho các thiết bị quay.</p>',
            ],
        ];

        foreach ($projects as $index => $project) {
            Project::query()->firstOrCreate(['title' => $project['title']], [
                ...$project,
                'cover' => Storage::disk('public')->exists($project['cover']) ? $project['cover'] : null,
                'sort_order' => $index + 1,
                'is_published' => true,
            ]);
        }
    }
}
