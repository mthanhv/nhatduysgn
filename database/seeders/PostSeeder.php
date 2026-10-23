<?php

namespace Database\Seeders;

use App\Models\Post;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * SAMPLE news: general technical guides about product lines Nhật Duy sells (no invented company events).
 * Not called by DatabaseSeeder; run explicitly: `php artisan db:seed --class=PostSeeder`.
 * Have the technical team review before publishing on the live site.
 */
class PostSeeder extends Seeder
{
    public function run(): void
    {
        $posts = [
            [
                'title' => 'Cách chọn đầu xiết bu lông thủy lực phù hợp',
                'days_ago' => 5,
                'cover' => 'legacy/files/products/ith-cx.jpg',
                'excerpt' => 'Lực xiết yêu cầu, cỡ đai ốc và không gian thao tác là ba yếu tố quyết định khi chọn đầu xiết thủy lực. Hướng dẫn nhanh cho kỹ sư bảo trì.',
                'content' => <<<'HTML'
<p>Chọn sai đầu xiết thủy lực khiến bu lông không đạt lực thiết kế hoặc kéo dài thời gian dừng máy vì phải đổi thiết bị giữa chừng. Dưới đây là các bước chọn thiết bị mà đội kỹ thuật Nhật Duy thường áp dụng khi tư vấn cho nhà máy.</p>
<h2>1. Xác định lực xiết yêu cầu</h2>
<p>Lực xiết (Nm) lấy từ tài liệu của nhà sản xuất thiết bị hoặc bản vẽ mối ghép. Giá trị này phụ thuộc cỡ bu lông, cấp bền vật liệu và chất bôi trơn ren được sử dụng. Không nên tự suy đoán: cùng một bu lông nhưng bôi trơn khác nhau sẽ cần lực xiết khác nhau.</p>
<h2>2. Chọn dải lực của thiết bị</h2>
<p>Nên chọn đầu xiết có lực tối đa cao hơn rõ rệt so với lực yêu cầu, để thiết bị không phải làm việc sát giới hạn và còn dư lực khi tháo bu lông bị kẹt. Ví dụ dòng ITH CX:</p>
<table>
<thead><tr><th>Kiểu loại</th><th>Lực xiết tối đa (Nm)</th></tr></thead>
<tbody>
<tr><td>CX-2</td><td>2.000</td></tr>
<tr><td>CX-5</td><td>5.000</td></tr>
<tr><td>CX-12</td><td>12.800</td></tr>
<tr><td>CX-28</td><td>28.000</td></tr>
<tr><td>CX-40</td><td>40.000</td></tr>
</tbody>
</table>
<h2>3. Kiểm tra cỡ đai ốc và không gian lắp đặt</h2>
<ul>
<li><strong>Loại đầu vuông</strong> dùng với khẩu (tuýp) — linh hoạt, phù hợp khi có đủ khoảng trống.</li>
<li><strong>Loại dẹt (low profile)</strong> như ITH DKS, DRS — dùng cho mặt bích, cụm bu lông sát nhau hoặc không gian thấp.</li>
<li>Đo khoảng cách giữa các bu lông và chiều cao thông thủy trước khi chọn.</li>
</ul>
<h2>4. Bơm thủy lực và phụ kiện</h2>
<p>Đầu xiết cần bơm có áp suất và lưu lượng phù hợp. Nên kiểm tra đồng hồ áp suất, ống dẫn và khớp nối nhanh trước mỗi đợt bảo trì.</p>
<blockquote>Cần hỗ trợ chọn thiết bị? Gửi cho chúng tôi cỡ bu lông, lực xiết và ảnh vị trí lắp đặt — kỹ sư Nhật Duy sẽ đề xuất cấu hình phù hợp.</blockquote>
HTML,
            ],
            [
                'title' => '5 nguyên nhân khiến vòng bi hỏng sớm và cách phòng tránh',
                'days_ago' => 19,
                'cover' => 'legacy/files/products/screen-shot-2023-02-22-at-145541.png',
                'excerpt' => 'Phần lớn vòng bi không hỏng vì hết tuổi thọ mà vì bôi trơn, lắp đặt và cân chỉnh chưa đúng. Nhận biết sớm giúp giảm đáng kể thời gian dừng máy.',
                'content' => <<<'HTML'
<p>Vòng bi được thiết kế để làm việc rất lâu nếu điều kiện vận hành phù hợp. Trên thực tế, nhiều vòng bi phải thay sớm vì những nguyên nhân hoàn toàn có thể phòng tránh.</p>
<h2>1. Bôi trơn không đúng</h2>
<p>Thiếu mỡ, thừa mỡ hoặc dùng sai loại mỡ đều làm tăng nhiệt độ và mài mòn. Chọn mỡ theo nhiệt độ, tốc độ và tải trọng làm việc; với điều kiện đặc biệt nên dùng mỡ chuyên dụng như các dòng của Klüber.</p>
<h2>2. Lệch trục</h2>
<p>Lệch trục giữa động cơ và thiết bị gây rung, tải lệch và nóng vòng bi. Cân chỉnh đồng trục bằng thiết bị laser cho kết quả nhanh và chính xác hơn so với thước và đồng hồ so.</p>
<h2>3. Nhiễm bẩn</h2>
<p>Bụi, nước và hạt kim loại lọt vào vòng bi gây rỗ bề mặt lăn. Cần kiểm tra phớt chặn và giữ sạch dụng cụ, mỡ trong quá trình bảo dưỡng.</p>
<h2>4. Lắp đặt sai cách</h2>
<p>Dùng búa đóng trực tiếp lên vòng bi làm hỏng rãnh lăn ngay từ đầu. Nên dùng bộ dụng cụ đóng chuyên dụng, cảo cơ khí/thủy lực và máy gia nhiệt cảm ứng để lắp ghép có độ dôi.</p>
<h2>5. Quá tải và rung động</h2>
<p>Tải vượt thiết kế hoặc rung từ thiết bị lân cận rút ngắn tuổi thọ. Giám sát rung động định kỳ giúp phát hiện sớm hư hỏng trước khi thiết bị phải dừng đột ngột.</p>
<h2>Gợi ý kế hoạch bảo trì</h2>
<ul>
<li>Lập lịch bôi trơn theo từng thiết bị, ghi rõ loại mỡ và lượng mỡ.</li>
<li>Cân chỉnh đồng trục sau mỗi lần tháo lắp động cơ, bơm, quạt.</li>
<li>Đo rung định kỳ và lưu xu hướng để so sánh.</li>
</ul>
HTML,
            ],
            [
                'title' => 'Keo khóa ren: chọn đúng loại theo lực khóa và điều kiện làm việc',
                'days_ago' => 40,
                'cover' => 'legacy/files/slides/5728ea33baece.png',
                'excerpt' => 'Keo khóa ren giúp chống tự tháo lỏng do rung động. Phân biệt lực khóa thấp, trung bình, cao để chọn đúng sản phẩm cho từng mối ghép.',
                'content' => <<<'HTML'
<p>Rung động làm bu lông, đai ốc tự tháo lỏng theo thời gian. Keo khóa ren điền đầy khe hở giữa các vòng ren, đóng rắn và giữ mối ghép cố định, đồng thời chống ăn mòn ren.</p>
<h2>Phân loại theo lực khóa</h2>
<table>
<thead><tr><th>Lực khóa</th><th>Ví dụ (Loctite)</th><th>Ứng dụng</th></tr></thead>
<tbody>
<tr><td>Thấp</td><td>222</td><td>Vít nhỏ, chi tiết cần tháo thường xuyên bằng dụng cụ tay</td></tr>
<tr><td>Trung bình</td><td>243</td><td>Bu lông, đai ốc thông dụng; tháo được bằng dụng cụ tay</td></tr>
<tr><td>Cao</td><td>263, 272</td><td>Mối ghép cố định, chịu rung lớn; 272 cho nhiệt độ cao</td></tr>
<tr><td>Thẩm thấu</td><td>290</td><td>Khóa ren cho cụm đã lắp sẵn, keo tự thấm vào ren</td></tr>
</tbody>
</table>
<h2>Lưu ý khi sử dụng</h2>
<ul>
<li>Làm sạch dầu mỡ trên ren trước khi bôi keo để đạt lực khóa đúng.</li>
<li>Mối ghép dùng keo lực khóa cao thường cần gia nhiệt cục bộ để tháo — tham khảo hướng dẫn của nhà sản xuất.</li>
<li>Kiểm tra giới hạn nhiệt độ và hóa chất tiếp xúc của từng loại keo.</li>
</ul>
<p>Nhật Duy phân phối đầy đủ các dòng keo khóa ren, làm kín ren, thay thế gioăng và chống xoay. Liên hệ để được tư vấn sản phẩm phù hợp với mối ghép của bạn.</p>
HTML,
            ],
            [
                'title' => 'Gia nhiệt cảm ứng: tháo lắp an toàn không cần lửa hở',
                'days_ago' => 62,
                'cover' => 'legacy/files/products/screen-shot-2024-06-18-at-104852.png',
                'excerpt' => 'So với khò lửa, gia nhiệt cảm ứng cho nhiệt độ đồng đều, kiểm soát chính xác và an toàn hơn trong khu vực có nguy cơ cháy nổ.',
                'content' => <<<'HTML'
<p>Tháo bu lông bị kẹt, lắp vòng bi hay khớp nối có độ dôi thường cần gia nhiệt chi tiết. Phương pháp khò lửa truyền thống khó kiểm soát nhiệt độ và tiềm ẩn rủi ro cháy nổ.</p>
<h2>Nguyên lý</h2>
<p>Dòng điện xoay chiều trong cuộn cảm tạo từ trường, sinh dòng điện xoáy làm nóng trực tiếp chi tiết kim loại. Nhiệt sinh ra ngay trong chi tiết, không cần ngọn lửa.</p>
<h2>Lợi ích</h2>
<ul>
<li><strong>An toàn:</strong> không có lửa hở, phù hợp nhà máy dầu khí, hóa chất, nơi kiểm soát chặt công việc nóng.</li>
<li><strong>Kiểm soát nhiệt độ:</strong> cài đặt và theo dõi nhiệt độ, tránh làm thay đổi tính chất vật liệu.</li>
<li><strong>Đồng đều và nhanh:</strong> nhiệt phân bố đều quanh chi tiết, rút ngắn thời gian tháo lắp.</li>
</ul>
<h2>Ứng dụng thường gặp</h2>
<ul>
<li>Nới bu lông vỏ tua-bin, bu lông mặt bích làm việc ở nhiệt độ cao.</li>
<li>Lắp, tháo vòng bi và khớp nối có độ dôi.</li>
<li>Gia nhiệt trước và sau hàn.</li>
</ul>
<p>Nhật Duy cung cấp hệ thống gia nhiệt cảm ứng INCOIL và dịch vụ gia nhiệt tại hiện trường.</p>
HTML,
            ],
            [
                'title' => 'Vát mép ống trước khi hàn: vì sao quan trọng?',
                'days_ago' => 85,
                'cover' => 'legacy/files/slides/cold-cutting.jpg',
                'excerpt' => 'Mép vát đúng góc và đồng đều là điều kiện để mối hàn ngấu hoàn toàn. So sánh phương pháp mài tay với máy cắt vát mép nguội.',
                'content' => <<<'HTML'
<p>Với ống thành dày, hàn trực tiếp trên mép cắt vuông khiến kim loại hàn không ngấu hết chiều dày. Vát mép tạo rãnh hàn để đảm bảo chất lượng mối hàn theo tiêu chuẩn.</p>
<h2>Yêu cầu của mép vát</h2>
<ul>
<li>Góc vát và mặt đáy (chân vát) theo quy trình hàn được duyệt.</li>
<li>Mép vát đồng đều trên toàn chu vi ống.</li>
<li>Bề mặt sạch, không ba-via, không bị biến đổi nhiệt.</li>
</ul>
<h2>Mài tay hay dùng máy?</h2>
<p>Mài tay phụ thuộc tay nghề, khó đồng đều và mất thời gian với ống lớn. Cắt bằng nhiệt tạo vùng ảnh hưởng nhiệt cần xử lý thêm. Máy cắt, vát mép nguội (kẹp trong hoặc ngoài ống) cho mép vát chính xác, lặp lại được và không làm biến đổi vật liệu.</p>
<h2>Thiết bị JBE</h2>
<p>Nhật Duy là đại diện của JBE (Hàn Quốc) tại Việt Nam, cung cấp máy vát mép ống, máy cắt vát mép ống, máy vát mép thép tấm cùng dụng cụ kẹp căn chỉnh và giá đỡ ống — phục vụ thi công đường ống, lò hơi và kết cấu thép.</p>
HTML,
            ],
            [
                'title' => 'Chuẩn bị cho đợt dừng máy bảo dưỡng: danh mục kiểm tra',
                'days_ago' => 110,
                'cover' => 'legacy/files/slides/tighten-and-loosen-industrial-bolted-joints-with-ith-bolting-tools.png',
                'excerpt' => 'Đợt dừng máy thành công được quyết định từ khâu chuẩn bị. Danh mục giúp đội bảo trì không thiếu thiết bị, vật tư khi bắt đầu thi công.',
                'content' => <<<'HTML'
<p>Thời gian dừng máy luôn bị giới hạn. Thiếu một bộ dụng cụ hay một loại vật tư có thể làm chậm cả dây chuyền công việc. Dưới đây là danh mục nên rà soát trước mỗi đợt bảo dưỡng.</p>
<h2>Phạm vi công việc</h2>
<ul>
<li>Danh sách thiết bị, mối ghép cần tháo lắp; bản vẽ và thông số lực xiết.</li>
<li>Thứ tự ưu tiên và các hạng mục trên đường găng tiến độ.</li>
</ul>
<h2>Dụng cụ và thiết bị</h2>
<ul>
<li>Đầu xiết, dụng cụ căng bu lông, bơm thủy lực — đã kiểm tra và hiệu chuẩn.</li>
<li>Thiết bị gia nhiệt, cảo, dụng cụ cân chỉnh, thiết bị đo.</li>
<li>Phương án dự phòng khi thiết bị gặp sự cố.</li>
</ul>
<h2>Vật tư</h2>
<ul>
<li>Gioăng, bu lông, đai ốc thay thế đúng chủng loại.</li>
<li>Keo khóa ren, keo làm kín, mỡ chống kẹt, dầu mỡ bôi trơn.</li>
<li>Hóa chất tẩy rửa, chống gỉ.</li>
</ul>
<h2>An toàn và hồ sơ</h2>
<ul>
<li>Giấy phép làm việc, phương án an toàn cho công việc trên cao, công việc nóng.</li>
<li>Biểu mẫu ghi nhận lực xiết và nghiệm thu từng mối ghép.</li>
</ul>
<p>Nhật Duy có thể cung cấp trọn gói thiết bị, vật tư và kỹ thuật viên cho đợt dừng máy. Liên hệ sớm để chúng tôi chuẩn bị theo phạm vi công việc của nhà máy.</p>
HTML,
            ],
            [
                'title' => 'Rà phẳng van an toàn tại hiện trường: quy trình cơ bản',
                'days_ago' => 140,
                'cover' => 'legacy/files/slides/hand-lappping.png',
                'excerpt' => 'Rà phẳng tại chỗ giúp phục hồi bề mặt làm kín của van an toàn và seal cơ khí mà không cần gửi thiết bị về xưởng.',
                'content' => <<<'HTML'
<p>Van an toàn rò rỉ thường do bề mặt làm kín giữa đĩa van và đế van bị mòn, xước. Rà phẳng (lapping) khôi phục độ phẳng và độ nhẵn của bề mặt này.</p>
<h2>Các bước cơ bản</h2>
<ol>
<li>Tháo, vệ sinh và kiểm tra bề mặt làm kín.</li>
<li>Rà thô bằng vật liệu mài hạt lớn để loại bỏ vết xước sâu.</li>
<li>Rà tinh với vật liệu mài mịn hơn, kiểm soát lực và chuyển động đều.</li>
<li>Kiểm tra độ phẳng bằng phương pháp quang học (tấm phẳng quang học và đèn đơn sắc).</li>
<li>Lắp lại, thử áp và ghi nhận kết quả.</li>
</ol>
<h2>Thiết bị</h2>
<p>Máy rà phẳng và bộ dụng cụ rà van KEMET, LARSLAP cho phép thực hiện ngay tại xưởng hiện trường của nhà máy, phù hợp đợt bảo dưỡng có nhiều van cần kiểm định.</p>
HTML,
            ],
        ];

        foreach ($posts as $data) {
            $publishedAt = now()->subDays($data['days_ago'])->setTime(8, 30);

            Post::query()->firstOrCreate(['title' => $data['title']], [
                'excerpt' => $data['excerpt'],
                'content' => $data['content'],
                'cover' => Storage::disk('public')->exists($data['cover']) ? $data['cover'] : null,
                'published_at' => $publishedAt,
                'is_published' => true,
            ]);
        }
    }
}
