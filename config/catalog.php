<?php

/*
|--------------------------------------------------------------------------
| Product group structure
|--------------------------------------------------------------------------
|
| Applied by `php artisan catalog:restructure` (and at the end of
| `legacy:import`). Groups are organised by application; brands have their
| own pages, so they are not used as groups.
|
| key       stable slug of the group (/danh-muc/{key}); never reuse a key.
| legacy    old site category IDs merged into this group: their products move
|           here and /products/category:{id} redirects here.
| products  old site product IDs moved here explicitly (products that were
|           misfiled on the old site or whose old category was deleted).
|
| Names and icons are only used when a group is first created; after that,
| editors own them in the admin.
|
*/

return [
    [
        'key' => 'dung-cu-xiet-bu-long', 'name' => 'Dụng cụ xiết bu lông', 'icon' => 'wrench', 'featured' => true,
        'legacy' => [85, 113],
        'children' => [
            ['key' => 'dau-xiet-thuy-luc', 'name' => 'Đầu xiết thủy lực & bơm', 'legacy' => [114, 117]],
            ['key' => 'dung-cu-cang-bu-long', 'name' => 'Dụng cụ căng bu lông', 'legacy' => [111, 112]],
            ['key' => 'bo-nhan-luc-can-xiet', 'name' => 'Bộ nhân lực, cần xiết & kiểm tra lực', 'legacy' => [115, 118]],
            ['key' => 'sung-xiet-bu-long', 'name' => 'Súng xiết bu lông', 'legacy' => [96, 133, 134, 135, 136, 137, 138, 163], 'products' => [703, 740]],
        ],
    ],
    [
        'key' => 'vong-bi', 'name' => 'Vòng bi & bảo trì vòng bi', 'icon' => 'ring', 'featured' => true,
        'legacy' => [171],
        'children' => [
            ['key' => 'vong-bi-o-truot', 'name' => 'Vòng bi & ổ trượt', 'legacy' => [172, 182, 183, 184, 185, 186]],
            ['key' => 'goi-do', 'name' => 'Gối đỡ & cụm ổ đỡ', 'legacy' => [181]],
            ['key' => 'dung-cu-lap-dat-vong-bi', 'name' => 'Dụng cụ lắp đặt, gia nhiệt & cân chỉnh', 'legacy' => [173, 174, 175, 176, 177, 178, 180], 'products' => [806, 807, 808, 809, 810, 811, 812, 814, 815, 816, 817, 818, 819]],
        ],
    ],
    [
        'key' => 'dau-mo-boi-tron', 'name' => 'Dầu & mỡ bôi trơn', 'icon' => 'drop', 'featured' => true,
        'legacy' => [164],
        'children' => [
            ['key' => 'mo-boi-tron', 'name' => 'Mỡ bôi trơn', 'legacy' => [165, 109, 139], 'products' => [772]],
            ['key' => 'dau-boi-tron', 'name' => 'Dầu bôi trơn & dầu cắt gọt', 'legacy' => [170, 168, 166, 167, 103, 179, 110], 'products' => [768]],
            ['key' => 'dau-mo-thuc-pham', 'name' => 'Dầu mỡ cấp thực phẩm', 'legacy' => [153]],
        ],
    ],
    [
        'key' => 'hoa-chat-bao-tri', 'name' => 'Hóa chất bảo trì', 'icon' => 'can',
        'legacy' => [88],
        'children' => [
            ['key' => 'chong-gi-tham-thau', 'name' => 'Chống gỉ & thẩm thấu', 'legacy' => [105, 104]],
            ['key' => 'tay-rua-ve-sinh', 'name' => 'Tẩy rửa & vệ sinh', 'legacy' => [107, 108, 169]],
            ['key' => 'san-pham-chuyen-dung-mro', 'name' => 'Sản phẩm chuyên dụng MRO', 'legacy' => [106], 'products' => [713]],
        ],
    ],
    [
        'key' => 'keo-cong-nghiep', 'name' => 'Keo công nghiệp', 'icon' => 'glue', 'featured' => true,
        'legacy' => [67, 121],
        'children' => [
            ['key' => 'keo-khoa-ren', 'name' => 'Keo khóa ren', 'legacy' => [84]],
            ['key' => 'keo-lam-kin', 'name' => 'Keo làm kín ren & thay thế gioăng', 'legacy' => [89, 86]],
            ['key' => 'keo-chong-xoay', 'name' => 'Keo chống xoay', 'legacy' => [102]],
            ['key' => 'keo-dan-nhanh', 'name' => 'Keo dán nhanh', 'legacy' => [120]],
        ],
    ],
    [
        'key' => 'thiet-bi-gia-cong-ong', 'name' => 'Thiết bị gia công ống & thép tấm', 'icon' => 'pipe', 'featured' => true,
        'legacy' => [98],
        'children' => [
            ['key' => 'may-cat-vat-mep-ong', 'name' => 'Máy cắt & vát mép ống', 'legacy' => [146, 147]],
            ['key' => 'may-vat-mep-thep-tam', 'name' => 'Máy vát mép thép tấm', 'legacy' => [152]],
            ['key' => 'may-phay-mat-bich', 'name' => 'Máy phay mặt bích', 'legacy' => [119]],
            ['key' => 'dung-cu-kep-gia-do-ong', 'name' => 'Dụng cụ kẹp & giá đỡ ống', 'products' => [425, 427, 428, 429, 432]],
        ],
    ],
    [
        'key' => 'thiet-bi-lo-hoi-trao-doi-nhiet', 'name' => 'Thiết bị lò hơi & trao đổi nhiệt', 'icon' => 'tube',
        'legacy' => [94, 124, 128],
    ],
    [
        'key' => 'ra-van-seal-co-khi', 'name' => 'Rà van & seal cơ khí', 'icon' => 'valve',
        'legacy' => [100],
        'children' => [
            ['key' => 'may-ra-phang-seal', 'name' => 'Máy rà phẳng seal cơ khí', 'legacy' => [154]],
            ['key' => 'may-ra-van', 'name' => 'Máy & bộ dụng cụ rà van', 'legacy' => [158, 155], 'products' => [729, 730]],
            ['key' => 'vat-lieu-mai', 'name' => 'Vật liệu mài & phụ kiện', 'legacy' => [156], 'products' => [559, 544, 545, 546, 547, 548, 735]],
            ['key' => 'may-lam-sach-chi-tiet', 'name' => 'Máy làm sạch chi tiết', 'legacy' => [157]],
        ],
    ],
    [
        'key' => 'gia-nhiet-cam-ung', 'name' => 'Gia nhiệt cảm ứng', 'icon' => 'heat',
        'legacy' => [187],
    ],
    [
        'key' => 'dai-kep-khop-noi-ong', 'name' => 'Đai kẹp & khớp nối ống', 'icon' => 'clamp',
        'legacy' => [161],
    ],
    [
        'key' => 'thiet-bi-do-can-chinh', 'name' => 'Thiết bị đo, cân chỉnh & đánh dấu', 'icon' => 'scan',
        'children' => [
            ['key' => 'may-do-phan-tich', 'name' => 'Máy đo & phân tích cầm tay', 'legacy' => [162], 'products' => [326, 439]],
            ['key' => 'shim-can-chinh', 'name' => 'Shim cân chỉnh', 'legacy' => [130, 131, 132]],
            ['key' => 'chat-danh-dau', 'name' => 'Chất đánh dấu Dykem', 'legacy' => [159, 160]],
        ],
    ],
    [
        'key' => 'dung-cu-cam-tay', 'name' => 'Dụng cụ cầm tay & cách điện', 'icon' => 'bolt',
        'children' => [
            ['key' => 'dung-cu-cach-dien', 'name' => 'Dụng cụ cách điện', 'legacy' => [101, 140, 141, 142, 145]],
            ['key' => 'bo-tuyp-co-le', 'name' => 'Bộ tuýp & cờ lê', 'products' => [26, 327, 328, 329, 367, 368, 369, 370, 371]],
        ],
    ],
    [
        'key' => 'thiet-bi-nang-ha', 'name' => 'Thiết bị nâng hạ', 'icon' => 'hoist',
        'legacy' => [95, 144],
    ],
];
