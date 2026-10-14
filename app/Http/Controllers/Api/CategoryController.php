<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    /**
     * Liste des catégories.
     */
    public function index()
    {
        $categories = Category::query()
            ->where('is_active', true)
            ->withCount('products')
            ->orderBy('name')
            ->get();

        return CategoryResource::collection($categories);
    }

    /**
     * Afficher une catégorie.
     */
    public function show(Category $category)
    {
        $category->load([
            'products' => function ($query) {
                $query
                    ->where('status', 'active')
                    ->with([
                        'images',
                        'variants',
                    ]);
            }
        ]);

        return new CategoryResource($category);
    }
}
