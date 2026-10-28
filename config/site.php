<?php

/*
|--------------------------------------------------------------------------
| Site content defaults
|--------------------------------------------------------------------------
|
| Every key here can be overridden in the admin (Cài đặt website). These
| values are used until an editor saves a different one.
|
*/

return [

    'company_name' => 'Công ty TNHH Kỹ thuật Nhật Duy',
    'brand_name' => 'Nhật Duy',
    'tagline' => 'Kỹ thuật bảo trì công nghiệp',
    'founded_year' => '2011',
    'address' => '68/4 Đường số 3, P. Bình Hưng Hoà, TP. Hồ Chí Minh',
    // Representative office (VPĐD): shown next to the head office; the map and directions point here.
    'office_address' => '10 Đường Ao Đôi, P. Bình Trị Đông, TP. Hồ Chí Minh',
    'phone' => '028 7300 7797',
    'fax' => '028 7304 7744',
    'email' => 'sales@nhatduysgn.com',
    'zalo' => null,
    'tax_code' => '0310880481',
    'working_hours' => 'Thứ 2 – Thứ 7, 8:00 – 17:00',
    'map_url' => 'https://maps.google.com/?q=10+%C4%90%C6%B0%E1%BB%9Dng+Ao+%C4%90%C3%B4i,+B%C3%ACnh+Tr%E1%BB%8B+%C4%90%C3%B4ng,+TP.+H%E1%BB%93+Ch%C3%AD+Minh',
    'map_embed_url' => null,

    'seo_title' => 'Nhật Duy — Thiết bị & giải pháp bảo trì công nghiệp (MRO)',
    'seo_description' => 'Công ty TNHH Kỹ thuật Nhật Duy cung cấp thiết bị, vật tư và dịch vụ kỹ thuật bảo trì, sửa chữa, vận hành (MRO) cho ngành dầu khí, điện, đạm, hóa chất và thực phẩm từ năm 2011.',

    // Home and About page blocks: each is one rich-text field read by App\Services\ContentBlock (Heading = title,
    // paragraph before it = small line above, bullet list = items, small Heading + paragraph = cards).
    'home_hero_content' => '<p>Thành lập 2011 · TP. Hồ Chí Minh</p><h2>Thiết bị &amp; giải pháp bảo trì cho nhà máy công nghiệp.</h2><p>Nhật Duy cung cấp thiết bị, vật tư và dịch vụ kỹ thuật trong lĩnh vực Bảo trì, Sửa chữa và Vận hành (MRO) cho ngành dầu khí, điện, đạm, hóa chất và thực phẩm.</p>',
    'home_hero_image' => null,
    'home_hero_caption' => "ITH · Made in Germany\nĐầu xiết thủy lực dòng CX",
    'home_about_content' => '<h2>Đối tác lâu dài và đáng tin cậy trong bảo trì công nghiệp</h2><p>Công ty TNHH Kỹ thuật Nhật Duy là đại diện của các hãng sản xuất từ Hoa Kỳ, Đức, Nhật Bản, Tây Ban Nha, Hàn Quốc, Đài Loan… cung cấp công cụ, thiết bị, máy móc, vật tư thay thế, dầu mỡ bôi trơn và sản phẩm chống ăn mòn — giúp khách hàng tránh dừng máy vì vật tư kém chất lượng.</p><ul><li><p><strong>Đại diện JBE tại Việt Nam</strong> — thiết bị ngành ống thép, lò hơi nhập trực tiếp từ Hàn Quốc</p></li><li><p><strong>Hàng chính hãng</strong> — từ các nước G7/EU và các nước phát triển</p></li><li><p><strong>Đội ngũ kỹ thuật</strong> — hỗ trợ tại văn phòng, nhà máy và xưởng sản xuất của khách hàng</p></li></ul>',
    'home_about_image' => null,
    'home_stats_content' => '<ul><li><p><strong>2011</strong> năm thành lập</p></li><li><p><strong>17</strong> thương hiệu quốc tế đại diện &amp; phân phối</p></li><li><p><strong>430+</strong> sản phẩm chính hãng</p></li><li><p><strong>4</strong> dịch vụ kỹ thuật tại hiện trường</p></li></ul>',
    'industries_content' => '<ul><li><p>Dầu khí</p></li><li><p>Nhà máy điện</p></li><li><p>Nhà máy đạm</p></li><li><p>Hóa chất</p></li><li><p>Thực phẩm</p></li></ul>',

    'about_hero_content' => '<h2>Đồng hành cùng nhà máy Việt Nam trong bảo trì công nghiệp</h2><p>Công ty TNHH Kỹ thuật Nhật Duy cung cấp thiết bị và giải pháp Bảo trì, Sửa chữa và Vận hành (MRO) từ năm 2011.</p>',
    'about_hero_image' => null,
    'about_intro_content' => '<h2>Thiết bị chính hãng, đội ngũ kỹ thuật tận nơi</h2><p>Được thành lập năm 2011 tại Thành phố Hồ Chí Minh, Nhật Duy cung cấp thiết bị và vật tư bảo trì cho các ngành Dầu khí, Nhà máy Điện, Nhà máy Đạm, Hóa chất, Thực phẩm và nhiều ngành công nghiệp khác.</p><p>Chúng tôi là đại diện của nhiều hãng sản xuất từ các nước G7/EU và các nước phát triển, với đa dạng chủng loại: công cụ dụng cụ, thiết bị, máy móc, vật tư thay thế, vật tư tiêu hao, dầu mỡ bôi trơn, chống rỉ sét và chống ăn mòn.</p><p>Mục tiêu của chúng tôi là giúp khách hàng không phải dừng thiết bị vì những lỗi xuất phát từ sản phẩm kém chất lượng — và luôn có đội ngũ kỹ thuật sẵn sàng hỗ trợ tại nhà máy trong thời gian sớm nhất.</p>',
    'about_pillars_content' => '<h3>Bảo trì (Maintenance)</h3><p>Thiết bị, dụng cụ và vật tư cho bảo trì định kỳ: dầu mỡ bôi trơn, keo công nghiệp, chống gỉ, cân chỉnh.</p><h3>Sửa chữa (Repair)</h3><p>Giải pháp phục hồi và thay thế: rà van, seal cơ khí, tháo lắp bu lông, gia nhiệt cảm ứng.</p><h3>Vận hành (Operations)</h3><p>Vật tư tiêu hao và thiết bị giúp dây chuyền vận hành ổn định, an toàn và liên tục.</p>',
    'about_origins' => 'Hoa Kỳ, Đức, Nhật Bản, Tây Ban Nha, Hàn Quốc, Đài Loan và các nước phát triển khác.',
    'about_values_content' => '<h3>Hàng chính hãng, chất lượng cao</h3><p>Tiếp cận trực tiếp nguồn sản phẩm từ các hãng sản xuất uy tín trên thế giới.</p><h3>Hỗ trợ kỹ thuật tận nơi</h3><p>Kỹ sư có mặt tại văn phòng, nhà máy, xưởng sản xuất để tư vấn và xử lý sự cố trong thời gian sớm nhất.</p><h3>Lắng nghe &amp; cải tiến</h3><p>Ghi nhận phản hồi của khách hàng để không ngừng nâng cao chất lượng sản phẩm và dịch vụ.</p>',

    // Services page blocks (same ContentBlock rules as above).
    'services_hero_content' => '<p>Dịch vụ kỹ thuật tại hiện trường</p><h2>Đúng thiết bị, đúng quy trình, ngay tại nhà máy của bạn</h2><p>Với đội ngũ kỹ thuật giàu kinh nghiệm cùng thiết bị chuyên dụng từ các hãng hàng đầu thế giới, Nhật Duy trực tiếp triển khai dịch vụ cho các nhà máy dầu khí, điện lực, hóa chất và sản xuất công nghiệp trên toàn quốc.</p>',
    'services_hero_image' => null,
    'service_benefits_content' => '<h3>Triển khai tại nhà máy</h3><p>Kỹ thuật viên và thiết bị có mặt tại hiện trường trên toàn quốc, không cần gửi thiết bị về xưởng.</p><h3>Thiết bị chính hãng</h3><p>Dụng cụ ITH, KEMET, LARSLAP, INCOIL, Schaeffler — đúng thiết bị cho từng hạng mục.</p><h3>Đúng thông số kỹ thuật</h3><p>Thực hiện theo lực xiết, nhiệt độ và quy trình của nhà sản xuất thiết bị.</p><h3>Trọn gói vật tư</h3><p>Cung cấp kèm keo, dầu mỡ, gioăng, vật liệu mài… cho đợt bảo dưỡng.</p>',
    'service_steps_content' => '<h3>Tiếp nhận yêu cầu</h3><p>Khách hàng gửi thông tin thiết bị, điều kiện vận hành và thời gian dừng máy dự kiến.</p><h3>Khảo sát &amp; đề xuất</h3><p>Kỹ sư khảo sát hiện trường và đề xuất phương án, thiết bị phù hợp.</p><h3>Triển khai tại nhà máy</h3><p>Đội ngũ kỹ thuật thực hiện với thiết bị chuyên dụng, đảm bảo an toàn.</p><h3>Bàn giao &amp; báo cáo</h3><p>Nghiệm thu, bàn giao biên bản kỹ thuật và khuyến nghị bảo trì tiếp theo.</p>',
    'service_faqs_content' => '<h3>Nhật Duy triển khai dịch vụ ở những khu vực nào?</h3><p>Chúng tôi triển khai dịch vụ kỹ thuật tại hiện trường cho các nhà máy dầu khí, điện lực, hóa chất và sản xuất công nghiệp trên toàn quốc.</p><h3>Cần cung cấp những thông tin gì để được tư vấn?</h3><p>Loại thiết bị và hạng mục cần thực hiện (ví dụ cỡ bu lông, lực xiết, loại van), điều kiện vận hành, địa điểm và thời gian dừng máy dự kiến. Ảnh chụp hiện trạng giúp chúng tôi đề xuất phương án nhanh hơn.</p><h3>Sau khi hoàn thành có hồ sơ bàn giao không?</h3><p>Có. Sau khi nghiệm thu, chúng tôi bàn giao biên bản kỹ thuật và khuyến nghị bảo trì tiếp theo.</p><h3>Thiết bị và vật tư sử dụng có phải hàng chính hãng không?</h3><p>Nhật Duy là đại diện, nhà phân phối của các thương hiệu thiết bị sử dụng trong dịch vụ, vì vậy thiết bị và vật tư đều là hàng chính hãng.</p>',

    'contact_notify_email' => env('CONTACT_NOTIFY_EMAIL', 'sales@nhatduysgn.com'),

    /*
    | English versions (see Setting::TRANSLATABLE), shown on the /en site. An empty one falls back to the
    | Vietnamese value; the partner block stays hidden until it has a title.
    */
    'company_name_en' => 'Nhat Duy Engineering Co., Ltd.',
    'tagline_en' => 'Industrial maintenance engineering',
    'working_hours_en' => 'Mon – Sat, 8:00 – 17:00',
    'address_en' => '68/4 Street No. 3, Binh Hung Hoa Ward, Ho Chi Minh City, Vietnam',
    'office_address_en' => '10 Ao Doi Street, Binh Tri Dong Ward, Ho Chi Minh City, Vietnam',

    'seo_title_en' => 'Nhat Duy — Industrial maintenance equipment & solutions (MRO)',
    'seo_description_en' => 'Nhat Duy Engineering supplies equipment, materials and on-site technical services for maintenance, repair and operations (MRO) in oil & gas, power, fertilizer, chemical and food plants since 2011.',

    'home_hero_content_en' => '<p>Founded 2011 · Ho Chi Minh City</p><h2>Maintenance equipment &amp; solutions for industrial plants.</h2><p>Nhat Duy supplies equipment, materials and technical services for Maintenance, Repair and Operations (MRO) in the oil &amp; gas, power, fertilizer, chemical and food industries.</p>',
    'home_hero_caption_en' => "ITH · Made in Germany\nCX series hydraulic torque wrench",
    'home_about_content_en' => '<h2>A long-term, trusted partner in industrial maintenance</h2><p>Nhat Duy Engineering represents manufacturers from the USA, Germany, Japan, Spain, Korea, Taiwan and more, supplying tools, equipment, machinery, spare parts, lubricants and anti-corrosion products — so customers never stop a machine because of poor-quality materials.</p><ul><li><p><strong>JBE representative in Vietnam</strong> — pipe and boiler equipment imported directly from Korea</p></li><li><p><strong>Genuine products</strong> — from G7/EU and other developed countries</p></li><li><p><strong>Technical team</strong> — support at the customer&#039;s office, plant and workshop</p></li></ul>',
    'home_stats_content_en' => '<ul><li><p><strong>2011</strong> year founded</p></li><li><p><strong>17</strong> international brands represented &amp; distributed</p></li><li><p><strong>430+</strong> genuine products</p></li><li><p><strong>4</strong> on-site technical services</p></li></ul>',
    'industries_content_en' => '<ul><li><p>Oil &amp; gas</p></li><li><p>Power plants</p></li><li><p>Fertilizer plants</p></li><li><p>Chemicals</p></li><li><p>Food &amp; beverage</p></li></ul>',

    'about_hero_content_en' => '<h2>Supporting Vietnamese plants in industrial maintenance</h2><p>Nhat Duy Engineering has supplied Maintenance, Repair and Operations (MRO) equipment and solutions since 2011.</p>',
    'about_intro_content_en' => '<h2>Genuine equipment, engineers on site</h2><p>Founded in 2011 in Ho Chi Minh City, Nhat Duy supplies maintenance equipment and materials to the oil &amp; gas, power, fertilizer, chemical and food industries, among many others.</p><p>We represent manufacturers from G7/EU and other developed countries across a wide range: hand tools, equipment, machinery, spare parts, consumables, lubricants, rust prevention and corrosion protection.</p><p>Our goal is that customers never have to stop equipment because of faults caused by poor-quality products — with a technical team always ready to support them at the plant as soon as possible.</p>',
    'about_pillars_content_en' => '<h3>Maintenance</h3><p>Equipment, tools and materials for planned maintenance: lubricants, industrial adhesives, rust prevention, alignment.</p><h3>Repair</h3><p>Restoration and replacement: valve lapping, mechanical seals, bolting, induction heating.</p><h3>Operations</h3><p>Consumables and equipment that keep production lines running steadily, safely and continuously.</p>',
    'about_origins_en' => 'USA, Germany, Japan, Spain, Korea, Taiwan and other developed countries.',
    'about_values_content_en' => '<h3>Genuine, high-quality products</h3><p>Direct access to products from reputable manufacturers worldwide.</p><h3>On-site technical support</h3><p>Engineers at your office, plant or workshop to advise and resolve issues as quickly as possible.</p><h3>Listening &amp; improving</h3><p>We act on customer feedback to keep improving our products and services.</p>',

    'services_hero_content_en' => '<p>On-site technical services</p><h2>The right equipment and procedures, right at your plant</h2><p>With an experienced technical team and specialist equipment from world-leading brands, Nhat Duy delivers services directly to oil &amp; gas, power, chemical and manufacturing plants nationwide.</p>',
    'service_benefits_content_en' => '<h3>Delivered at your plant</h3><p>Technicians and equipment on site nationwide — no need to ship equipment to a workshop.</p><h3>Genuine equipment</h3><p>ITH, KEMET, LARSLAP, INCOIL and Schaeffler tools — the right equipment for each job.</p><h3>To specification</h3><p>Performed to the equipment manufacturer&#039;s torque, temperature and procedures.</p><h3>Materials included</h3><p>Adhesives, lubricants, gaskets, lapping compounds and more supplied for the shutdown.</p>',
    'service_steps_content_en' => '<h3>Request</h3><p>You send the equipment details, operating conditions and planned shutdown time.</p><h3>Survey &amp; proposal</h3><p>Our engineers survey the site and propose a suitable method and equipment.</p><h3>On-site work</h3><p>Our technical team carries out the work with specialist equipment, safely.</p><h3>Handover &amp; report</h3><p>Acceptance, technical report handover and recommendations for further maintenance.</p>',
    'service_faqs_content_en' => '<h3>Where does Nhat Duy provide services?</h3><p>We deliver on-site technical services to oil &amp; gas, power, chemical and manufacturing plants throughout Vietnam.</p><h3>What information do you need to advise us?</h3><p>The equipment and work required (e.g. bolt size, torque, valve type), operating conditions, location and the planned shutdown window. Photos of the current condition help us propose a solution faster.</p><h3>Do you provide handover documents?</h3><p>Yes. After acceptance we hand over a technical report and recommendations for further maintenance.</p><h3>Are the equipment and materials genuine?</h3><p>Nhat Duy is the representative and distributor of the brands used in our services, so all equipment and materials are genuine.</p>',

];
