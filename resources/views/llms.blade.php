# {!! setting('company_name') !!}

> {!! setting('seo_description') !!}

{!! setting('brand_name') !!} ({!! setting('company_name') !!}, MST {!! setting('tax_code') !!}, thành lập {!! setting('founded_year') !!}) là đại diện và nhà phân phối thiết bị, vật tư bảo trì công nghiệp (MRO) chính hãng tại Việt Nam, kèm dịch vụ kỹ thuật tại nhà máy. Giá sản phẩm được báo theo yêu cầu: liên hệ để nhận báo giá.

## Liên hệ

- Điện thoại: {!! setting('phone') !!}
- Email: {!! setting('email') !!}
@if (setting('zalo'))
- Zalo: {!! setting('zalo') !!}
@endif
- Trụ sở: {!! setting('address') !!}
@if (setting('office_address'))
- Văn phòng đại diện: {!! setting('office_address') !!}
@endif
- Giờ làm việc: {!! setting('working_hours') !!}
- Gửi yêu cầu: {{ route('contact') }}

## Trang chính

- [Giới thiệu]({{ route('about') }}): {!! setting('about_hero_text') !!}
- [Sản phẩm]({{ route('products.index') }}): toàn bộ nhóm sản phẩm và thương hiệu; tìm kiếm theo tên/mã: {{ route('products.index') }}?q=MÃ_SẢN_PHẨM
- [Dịch vụ kỹ thuật]({{ route('services') }}): {!! setting('services_intro') !!}
- [Dự án]({{ route('projects.index') }}): dự án đã triển khai tại các nhà máy
- [Tin tức]({{ route('posts.index') }}): bài viết kỹ thuật và tin hoạt động
- [Liên hệ]({{ route('contact') }})

## Nhóm sản phẩm

@foreach ($categories as $category)
- [{!! $category->name !!}]({{ route('products.category', $category) }})@if ($category->children->isNotEmpty()): {!! $category->children->pluck('name')->implode('; ') !!}@endif

@endforeach

## Thương hiệu đại diện và phân phối

@foreach ($brands as $brand)
- [{!! $brand->name !!}]({{ route('products.brand', $brand) }})@if ($brand->country) ({!! $brand->country !!})@endif

@endforeach
@if ($services->isNotEmpty())

## Dịch vụ kỹ thuật

@foreach ($services as $service)
- [{!! $service->title !!}]({{ route('services') }}#{{ $service->slug }}): {!! \App\Services\Seo::text($service->summary ?: $service->description, 200) !!}
@endforeach
@endif

## Optional

- [Danh mục sản phẩm đầy đủ (Markdown)]({{ url('/llms-full.txt') }})
- [Sitemap]({{ route('sitemap') }})
