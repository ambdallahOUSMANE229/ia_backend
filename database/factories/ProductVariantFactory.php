<?php

namespace Database\Factories;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    public function definition(): array
    {
        $sizes = [
            'S',
            'M',
            'L',
            'XL',
            'XXL',
        ];

        $colors = [
            'Noir',
            'Blanc',
            'Beige',
            'Gris',
            'Vert',
            'Bleu marine',
        ];

        return [
            'product_id' => Product::factory(),

            'sku' => 'OUS-VAR-' . fake()->unique()->numerify('#####'),

            'size' => fake()->randomElement($sizes),

            'color' => fake()->randomElement($colors),

            'price' => fake()->randomFloat(
                2,
                12000,
                150000
            ),

            'stock' => fake()->numberBetween(0, 50),

            'weight' => fake()->randomFloat(
                2,
                0.1,
                3
            ),
        ];
    }
}
