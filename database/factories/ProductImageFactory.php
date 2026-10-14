<?php

namespace Database\Factories;

use App\Models\ProductImage;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Product;


/**
 * @extends Factory<ProductImage>
 */
class ProductImageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),

            'image' => 'products/' . fake()->uuid() . '.webp',

            'is_primary' => false,

            'sort_order' => fake()->numberBetween(1, 5),
        ];
    }
}
