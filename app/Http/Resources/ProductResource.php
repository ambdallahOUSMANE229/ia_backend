<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,

            'price' => $this->price,
            'compare_price' => $this->compare_price,

            'sku' => $this->sku,
            'brand' => $this->brand,

            'status' => $this->status,
            'is_featured' => $this->is_featured,
            'is_new' => $this->is_new,

            /*
            |--------------------------------------------------------------------------
            | Relations
            |--------------------------------------------------------------------------
            */

            'category' => CategoryResource::make(
                $this->whenLoaded('category')
            ),

            'images' => ProductImageResource::collection(
                $this->whenLoaded('images')
            ),

            'variants' => ProductVariantResource::collection(
                $this->whenLoaded('variants')
            ),

            'reviews' => ReviewResource::collection(
                $this->whenLoaded('reviews')
            ),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
