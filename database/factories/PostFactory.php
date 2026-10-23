<?php

namespace Database\Factories;

use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => ucfirst(fake()->unique()->words(6, true)),
            'excerpt' => fake()->sentence(),
            'content' => '<p>'.fake()->paragraphs(3, true).'</p>',
            'published_at' => now()->subDays(fake()->numberBetween(1, 300)),
            'is_published' => true,
        ];
    }
}
