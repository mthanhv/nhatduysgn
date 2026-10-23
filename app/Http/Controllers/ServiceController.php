<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Product;
use App\Models\Project;
use App\Models\Service;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function __invoke(): View
    {
        $services = Service::query()->published()->ordered()->get();
        $brands = Brand::query()->published()->get()->keyBy(fn (Brand $brand) => mb_strtoupper($brand->name));

        return view('pages.services', [
            'services' => $services,
            'brandsByService' => $services->mapWithKeys(fn (Service $service) => [
                $service->id => collect($service->equipment)->map(fn (string $name) => $brands->get(mb_strtoupper(trim($name))))->filter()->values(),
            ]),
            'productsByService' => $services->mapWithKeys(fn (Service $service) => [
                $service->id => $this->equipmentProducts($service, $brands),
            ]),
            'projects' => Project::query()->published()->ordered()->limit(3)->get(),
        ]);
    }

    /**
     * Up to three catalogue products made by the brands a service uses (featured first).
     *
     * @param  Collection<string, Brand>  $brands
     * @return Collection<int, Product>
     */
    private function equipmentProducts(Service $service, Collection $brands): Collection
    {
        $brandIds = collect($service->equipment)->map(fn (string $name) => $brands->get(mb_strtoupper(trim($name)))?->id)->filter();

        if ($brandIds->isEmpty()) {
            return collect();
        }

        // Interleave brands so a service using e.g. Schaeffler + ITH shows equipment from both.
        $perBrand = $brandIds->map(fn (int $brandId) => Product::query()
            ->published()
            ->where('brand_id', $brandId)
            ->whereNotNull('image')
            ->with('brand')
            ->orderByDesc('is_featured')
            ->orderByDesc('view_count')
            ->limit(3)
            ->get());

        return collect(range(0, 2))
            ->flatMap(fn (int $index) => $perBrand->map(fn (Collection $products) => $products->get($index)))
            ->filter()
            ->take(3)
            ->values();
    }
}
