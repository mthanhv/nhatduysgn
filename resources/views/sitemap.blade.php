{!! '<'.'?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach ($pages as $page)
    <url><loc>{{ $page }}</loc></url>
@endforeach
@foreach ($categories as $category)
    <url><loc>{{ route('products.category', $category) }}</loc><lastmod>{{ $category->updated_at?->toAtomString() }}</lastmod></url>
@endforeach
@foreach ($products as $product)
    <url><loc>{{ route('products.show', $product) }}</loc><lastmod>{{ $product->updated_at?->toAtomString() }}</lastmod></url>
@endforeach
@foreach ($projects as $project)
    <url><loc>{{ route('projects.show', $project) }}</loc><lastmod>{{ $project->updated_at?->toAtomString() }}</lastmod></url>
@endforeach
@foreach ($posts as $post)
    <url><loc>{{ route('posts.show', $post) }}</loc><lastmod>{{ $post->updated_at?->toAtomString() }}</lastmod></url>
@endforeach
</urlset>
