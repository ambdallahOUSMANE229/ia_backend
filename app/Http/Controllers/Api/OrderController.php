<?php

namespace App\Http\Controllers\Api;

use App\Services\FedaPayService;

use Illuminate\Support\Str;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Créer une nouvelle commande.
     */
   public function store(Request $request)
{

$validated = $request->validate([
    'address_id' => 'required|integer|exists:addresses,id',
    'payment_method' => 'required|in:cash_on_delivery,fedapay',
]);



    $user = $request->user();

    $cart = Cart::with('items.product')
        ->where('user_id', $user->id)
        ->where('status', 'active')
        ->first();

    if (!$cart || $cart->items->isEmpty()) {
        return response()->json([
            'message' => 'Votre panier est vide.'
        ], 422);
    }

    DB::beginTransaction();

    try {

        /*
        |--------------------------------------------------------------------------
        | Adresse
        |--------------------------------------------------------------------------
        */


$address = Address::where('user_id', $user->id)
    ->findOrFail($validated['address_id']);



        /*
        |--------------------------------------------------------------------------
        | Calcul du panier
        |--------------------------------------------------------------------------
        */

        $subtotal = $cart->items->sum(function ($item) {
            return $item->unit_price * $item->quantity;
        });

        $shippingCost = 0;
        $discount = 0;
        $total = $subtotal + $shippingCost - $discount;

        /*
        |--------------------------------------------------------------------------
        | Commande
        |--------------------------------------------------------------------------
        */

        $orderNumber =
            'OUS-' .
            now()->format('YmdHis') .
            '-' .
            random_int(100, 999);

        $order = Order::create([
            'user_id' => $user->id,
            'address_id' => $address->id,
            'order_number' => $orderNumber,
            'status' => 'pending',
            'subtotal' => $subtotal,
            'shipping_cost' => $shippingCost,
            'discount' => $discount,
            'total' => $total,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Produits de la commande
        |--------------------------------------------------------------------------
        */

        foreach ($cart->items as $item) {

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item->product_id,
                'product_variant_id' => $item->product_variant_id,
                'product_name' => $item->product->name,
                'sku' => $item->product->sku,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'total' => $item->unit_price * $item->quantity,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Paiement interne OUSWEAR
        |--------------------------------------------------------------------------
        */

        $payment = Payment::create([
            'order_id' => $order->id,
            'reference' => 'PAY-' . $orderNumber,
            'method' => $validated['payment_method'],
            'amount' => $total,
            'status' => 'pending',
            'transaction_id' => null,
            'paid_at' => null,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Paiement FedaPay
        |--------------------------------------------------------------------------
        */

        if ($validated['payment_method'] === 'fedapay') {


$nameParts = preg_split(
    '/\s+/',
    trim($address->first_name . ' ' . $address->last_name),
    2
);

$firstname = $nameParts[0] ?? '';
$lastname = $nameParts[1] ?? '';

$transaction = $this->fedaPayService->createTransaction(
    amount: $total,
    description: 'Commande OUSWEAR ' . $orderNumber,
    firstname: $firstname,
    lastname: $lastname,
    email: $user->email,
    phone: $address->phone,
    callbackUrl: config('services.fedapay.callback_url'),
    returnUrl: config('services.fedapay.return_url')
);



            $paymentUrl =
                $this->fedaPayService->generatePaymentUrl($transaction);

            /*
            | Enregistrer l'identifiant FedaPay
            */

            $payment->update([
                'transaction_id' => (string) $transaction->id,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Commande créée. Redirection vers FedaPay.',
                'order' => $order->load([
                    'items',
                    'address',
                    'payment',
                ]),
                'payment_url' => $paymentUrl,
            ], 201);
        }

        /*
        |--------------------------------------------------------------------------
        | Paiement à la livraison
        |--------------------------------------------------------------------------
        */

        DB::commit();

        return response()->json([
            'message' => 'Commande créée avec succès.',
            'order' => $order->load([
                'items',
                'address',
                'payment',
            ]),
        ], 201);

    } catch (\Throwable $e) {

        DB::rollBack();

        Log::error('ORDER CREATION ERROR', [
            'user_id' => $request->user()?->id,
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);

        return response()->json([
            'message' => 'Impossible de créer la commande.',
        ], 500);
    }
}

    /**
     * Liste des commandes de l'utilisateur connecté.
     */
    public function index(Request $request)
    {
        $orders = Order::with([
            'items.product',
            'address',
            'payment',
        ])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'data' => $orders,
        ]);
    }

    /**
     * Afficher une commande.
     */
    public function show(Request $request, string $id)
    {
        $order = Order::with([
            'items.product',
            'address',
            'payment',
        ])
            ->where('user_id', $request->user()->id)
            ->findOrFail($id);

        return response()->json([
            'data' => $order,
        ]);
    }

    public function __construct(
        private FedaPayService $fedaPayService
    ) {
    }


}
