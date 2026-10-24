@props(['data'])
{{-- schema.org structured data; $data is one object or a list of objects. --}}
@foreach (array_is_list($data) ? $data : [$data] as $item)
<script type="application/ld+json">{!! \App\Services\Seo::json($item) !!}</script>
@endforeach
