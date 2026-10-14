<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Liste des produits.
     */
    public function index(Request $request)
    {
        $query = Product::query()
            ->where('status', 'active')
            ->with([
                'category',
                'images',
                'variants',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Recherche
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {

            $search = $request->input('search');

            $query->where(function ($q) use ($search) {

                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%")
                    ->orWhere(
                        'description',
                        'like',
                        "%{$search}%"
                    );
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Catégorie
        |--------------------------------------------------------------------------
        */

        if ($request->filled('category_id')) {
            $query->where(
                'category_id',
                $request->integer('category_id')
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Produit vedette
        |--------------------------------------------------------------------------
        */

        if ($request->boolean('featured')) {
            $query->where('is_featured', true);
        }

        /*
        |--------------------------------------------------------------------------
        | Nouveau produit
        |--------------------------------------------------------------------------
        */

        if ($request->boolean('new')) {
            $query->where('is_new', true);
        }

        /*
        |--------------------------------------------------------------------------
        | Prix minimum
        |--------------------------------------------------------------------------
        */

        if ($request->filled('min_price')) {
            $query->where(
                'price',
                '>=',
                $request->input('min_price')
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Prix maximum
        |--------------------------------------------------------------------------
        */

        if ($request->filled('max_price')) {
            $query->where(
                'price',
                '<=',
                $request->input('max_price')
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Tri
        |--------------------------------------------------------------------------
        */

        match ($request->input('sort')) {

            'price_asc' => $query->orderBy('price', 'asc'),

            'price_desc' => $query->orderBy('price', 'desc'),

            'newest' => $query->latest(),

            default => $query->latest(),
        };

        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $products = $query->paginate(
            $request->integer('per_page', 12)
        );

        return ProductResource::collection($products);
    }

    /**
     * Afficher un produit.
     */
    public function show(Product $product)
    {
        $product->load([
            'category',
            'images',
            'variants',
            'reviews.user',
        ]);

        return new ProductResource($product);
    }
}
