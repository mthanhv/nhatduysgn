<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
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
                'title' => __('Kết quả tìm kiếm: “:term”', ['term' => $term]),
                'term' => $term,
                'products' => $this->paginate(Product::query()->search($term)),
                'tree' => ProductCategory::publishedTree(),
                'currentCategory' => null,
            ]);
        }

        $categories = ProductCategory::publishedTree();

        return view('products.index', [
            'categories' => $categories,
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
            // Products of sub-groups follow the sub-group order, then each group's own product order.
            'products' => $this->paginate(Product::query()
                ->whereIn('product_category_id', $category->descendantAndSelfIds())
                ->orderBy(ProductCategory::query()->select('sort_order')->whereColumn('product_categories.id', 'products.product_category_id'))),
            'tree' => ProductCategory::publishedTree(),
        ]);
    }

    public function brand(Brand $brand): View
    {
        abort_unless($brand->is_published, 404);

        return view('products.listing', [
            'title' => __('Sản phẩm :brand', ['brand' => $brand->name]),
            'brand' => $brand,
            'currentCategory' => null,
            'products' => $this->paginate($brand->products()->getQuery()),
            'tree' => ProductCategory::publishedTree(),
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
}
