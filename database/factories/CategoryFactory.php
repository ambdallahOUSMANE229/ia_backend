<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->randomElement([
            'T-shirts',
            'Pantalons',
            'Vestes',
            'Chaussures',
            'Casquettes',
            'Montres',
            'Sacs',
            'Accessoires',
        ]);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->sentence(12),
            'image' => null,
            'is_active' => true,
        ];
    }
}
