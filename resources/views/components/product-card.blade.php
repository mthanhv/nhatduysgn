@props(['product'])
<article class="card product-card">
    <a class="thumb" href="{{ lroute('products.show', $product) }}" tabindex="-1" aria-hidden="true">
        @if ($product->image)
            <img src="{{ thumb_url($product->image, 640) }}" alt="{{ $product->name }}" loading="lazy">
        @else
            <span class="placeholder">{{ $product->brand?->name ?? setting('brand_name') }}</span>
        @endif
    </a>
    <div class="body">
        @if ($product->brand)
            <span class="kicker">{{ $product->brand->name }}</span>
        @endif
        <h3><a href="{{ lroute('products.show', $product) }}">{{ $product->name }}</a></h3>
        @if ($product->code)
            <span class="chip">{{ $product->code }}</span>
        @endif
        <a class="more" href="{{ lroute('products.show', $product) }}">{{ __('Xem chi tiết') }} <x-ui-icon name="arrow-right" :size="16" /></a>
    </div>
</article>
