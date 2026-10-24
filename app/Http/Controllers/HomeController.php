<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Service;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        return view('pages.home', [
            'categories' => ProductCategory::query()
                ->published()
                ->whereNull('parent_id')
                ->orderByDesc('is_featured')
                ->ordered()
                ->limit(8)
                ->get(),
            'featuredProducts' => Product::query()->published()->where('is_featured', true)->with('brand')->ordered()->limit(4)->get(),
            'services' => Service::query()->published()->ordered()->limit(4)->get(),
            'brands' => Brand::query()->published()->orderByDesc('is_featured')->ordered()->limit(12)->get(),
        ]);
    }
}
