<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    private const PER_PAGE = 24;

    /**
     * Overview of all product groups, or search results when `?q=` is present.
     */
    public function index(Request $request): View
    {
        $term = trim((string) $request->query('q'));

        if ($term !== '') {
            return view('products.listing', [
                'title' => "Kết quả tìm kiếm: “{$term}”",
                'term' => $term,
                'products' => $this->paginate(Product::query()->search($term)),
                'tree' => $this->categoryTree(),
                'currentCategory' => null,
            ]);
        }

        $categories = ProductCategory::query()
            ->published()
            ->whereNull('parent_id')
            ->with(['children' => fn ($query) => $query->published()->ordered()])
            ->ordered()
            ->get();

        return view('products.index', [
            'categories' => $categories,
            'counts' => $this->publishedCountsByRootCategory($categories),
            'featured' => $categories->where('is_featured', true)->take(4),
            'brands' => Brand::query()->published()->orderByDesc('is_featured')->ordered()->get(),
        ]);
    }

    public function category(ProductCategory $category): View
    {
        abort_unless($category->is_published, 404);

        return view('products.listing', [
            'title' => $category->name,
            'category' => $category,
            'currentCategory' => $category,
            'products' => $this->paginate(Product::query()->whereIn('product_category_id', $category->descendantAndSelfIds())),
            'tree' => $this->categoryTree(),
        ]);
    }

    public function brand(Brand $brand): View
    {
        abort_unless($brand->is_published, 404);

        return view('products.listing', [
            'title' => "Sản phẩm {$brand->name}",
            'brand' => $brand,
            'currentCategory' => null,
            'products' => $this->paginate($brand->products()->getQuery()),
            'tree' => $this->categoryTree(),
        ]);
    }

    public function show(Product $product): View
    {
        abort_unless($product->is_published, 404);

        $product->load(['brand', 'category.parent']);
        $product->newQuery()->whereKey($product->id)->increment('view_count');

        return view('products.show', [
            'product' => $product,
            'related' => Product::query()
                ->published()
                ->where('product_category_id', $product->product_category_id)
                ->whereKeyNot($product->id)
                ->with('brand')
                ->ordered()
                ->limit(4)
                ->get(),
        ]);
    }

    /**
     * @param  Builder<Product>  $query
     * @return LengthAwarePaginator<int, Product>
     */
    private function paginate(Builder $query): LengthAwarePaginator
    {
        return $query->published()->with('brand')->ordered()->paginate(self::PER_PAGE)->withQueryString();
    }

    /**
     * @return Collection<int, ProductCategory>
     */
    private function categoryTree(): Collection
    {
        return ProductCategory::query()
            ->published()
            ->whereNull('parent_id')
            ->with(['children' => fn ($query) => $query->published()->ordered()])
            ->ordered()
            ->get();
    }

    /**
     * Published product totals per top-level category, including products filed under sub-groups.
     *
     * @param  Collection<int, ProductCategory>  $roots
     * @return array<int, int>
     */
    private function publishedCountsByRootCategory(Collection $roots): array
    {
        $perCategory = Product::query()->published()
            ->selectRaw('product_category_id, count(*) as aggregate')
            ->groupBy('product_category_id')
            ->pluck('aggregate', 'product_category_id');

        return $roots->mapWithKeys(fn (ProductCategory $root) => [
            $root->id => $root->descendantAndSelfIds()->sum(fn (int $id) => (int) ($perCategory[$id] ?? 0)),
        ])->all();
    }
}
