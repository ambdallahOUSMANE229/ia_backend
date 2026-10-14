<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Http\Request;

class CartItemController extends Controller
{
    /**
     * Ajouter un produit au panier
     */
   public function store(Request $request)
{
    $validated = $request->validate([
        'product_id' => ['required', 'exists:products,id'],
        'quantity' => ['required', 'integer', 'min:1'],
    ]);

    $product = Product::findOrFail($validated['product_id']);

    $cart = Cart::firstOrCreate([
        'user_id' => auth()->id(),
    ]);

    $cartItem = CartItem::updateOrCreate(
        [
            'cart_id' => $cart->id,
            'product_id' => $product->id,
        ],
        [
            'quantity' => $validated['quantity'],
            'unit_price' => $product->price,
        ]
    );

    $cartItem->load('product');

    return response()->json([
        'message' => 'Produit ajouté au panier.',
        'data' => $cartItem,
    ], 201);
}

    /**
     * Modifier la quantité
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $cartItem = CartItem::where('id', $id)
            ->whereHas('cart', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->firstOrFail();

        $cartItem->update([
            'quantity' => $validated['quantity'],
        ]);

        $cartItem->load('product');

        return response()->json([
            'message' => 'Quantité mise à jour.',
            'data' => $cartItem,
        ]);
    }

    /**
     * Supprimer un produit du panier
     */
    public function destroy(string $id)
    {
        $cartItem = CartItem::where('id', $id)
            ->whereHas('cart', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->firstOrFail();

        $cartItem->delete();

        return response()->json([
            'message' => 'Produit supprimé du panier.',
        ]);
    }
}
