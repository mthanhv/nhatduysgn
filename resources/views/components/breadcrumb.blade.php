@props(['items' => []])
{{-- $items: ['Label' => url, ..., 'Current label' => null] --}}
<nav aria-label="Breadcrumb" class="breadcrumb">
    <a href="{{ route('home') }}">Trang chủ</a>
    @foreach ($items as $label => $url)
        <span aria-hidden="true">/</span>
        @if ($url)
            <a href="{{ $url }}">{{ $label }}</a>
        @else
            <span aria-current="page">{{ $label }}</span>
        @endif
    @endforeach
</nav>
<x-json-ld :data="\App\Services\Seo::breadcrumbs($items)" />
