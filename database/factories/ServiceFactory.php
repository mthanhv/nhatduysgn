<?php

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => ucfirst(fake()->unique()->words(4, true)),
            'summary' => fake()->sentence(),
            'description' => '<p>'.fake()->paragraph().'</p>',
            'equipment' => ['ITH'],
            'is_published' => true,
        ];
    }
}
