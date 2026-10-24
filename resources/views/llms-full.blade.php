@php
    $facts = function ($product): string {
        $facts = collect([$product->code ? 'Mã: '.$product->code : null, $product->brand ? 'Hãng: '.$product->brand->name : null, $product->origin])->filter();

        return $facts->isEmpty() ? '' : ' — '.$facts->implode(' · ');
    };
@endphp
# {!! setting('company_name') !!}: danh mục sản phẩm

> Toàn bộ sản phẩm đang giới thiệu trên {{ url('/') }}. Hàng chính hãng; giá báo theo yêu cầu. Liên hệ: {!! setting('phone') !!} · {!! setting('email') !!} · {{ route('contact') }}

@foreach ($categories as $category)
## {!! $category->name !!}

{{ route('products.category', $category) }}

@foreach ($products->get($category->id, collect()) as $product)
- [{!! $product->name !!}]({{ route('products.show', $product) }}){!! $facts($product) !!}
@endforeach
@foreach ($category->children as $child)

### {!! $child->name !!}

{{ route('products.category', $child) }}

@foreach ($products->get($child->id, collect()) as $product)
- [{!! $product->name !!}]({{ route('products.show', $product) }}){!! $facts($product) !!}
@endforeach
@endforeach

@endforeach
