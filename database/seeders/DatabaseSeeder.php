<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Catégories OUSWEAR
        |--------------------------------------------------------------------------
        */

        $categories = Category::factory()
            ->count(8)
            ->create();

        /*
        |--------------------------------------------------------------------------
        | Produits
        |--------------------------------------------------------------------------
        */

        Product::factory()
            ->count(40)
            ->recycle($categories)
            ->create()
            ->each(function (Product $product) {

                /*
                |--------------------------------------------------------------------------
                | Images
                |--------------------------------------------------------------------------
                */

                ProductImage::factory()
                    ->count(3)
                    ->create([
                        'product_id' => $product->id,
                    ]);

                /*
                |--------------------------------------------------------------------------
                | Variantes
                |--------------------------------------------------------------------------
                */

                ProductVariant::factory()
                    ->count(4)
                    ->create([
                        'product_id' => $product->id,
                    ]);
            });
    }
}
