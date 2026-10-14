<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'category_id' => Category::factory(),
            'name' => ucfirst($name),
            'slug' => Str::slug($name) . '-' . fake()->unique()->numberBetween(1000, 9999),
            'description' => fake()->paragraph(),
            'price' => fake()->randomFloat(2, 10000, 150000),
            'compare_price' => fake()->optional()->randomFloat(2, 150000, 200000),
            'sku' => 'OUS-' . strtoupper(fake()->unique()->bothify('???###')),
            'brand' => 'OUSWEAR',
            'status' => 'active',
            'is_featured' => fake()->boolean(20),
            'is_new' => fake()->boolean(30),
        ];
    }
}
