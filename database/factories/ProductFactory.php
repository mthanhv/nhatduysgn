<?php

namespace Database\Factories;

use App\Models\Brand;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_category_id' => ProductCategory::factory(),
            'brand_id' => Brand::factory(),
            'name' => ucfirst(fake()->unique()->words(4, true)),
            'code' => strtoupper(fake()->bothify('??-###')),
            'origin' => 'Made in Germany',
            'summary' => fake()->sentence(),
            'description' => '<p>'.fake()->paragraph().'</p>',
            'is_published' => true,
        ];
    }
}
