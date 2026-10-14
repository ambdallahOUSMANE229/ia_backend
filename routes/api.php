<?php
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PaymentController;

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductImageController;
use App\Http\Controllers\Api\ProductVariantController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CartItemController;
use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\OrderController;

use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\CouponController;
use App\Http\Controllers\Api\AuthController;

/*
|--------------------------------------------------------------------------
| API publique
|--------------------------------------------------------------------------
*/

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::apiResource('categories', CategoryController::class)
    ->only(['index', 'show']);

Route::apiResource('products', ProductController::class)
    ->only(['index', 'show']);

Route::get(
    '/products/{product}/reviews',
    [ReviewController::class, 'index']
);



Route::post(
    '/payments/fedapay/callback',
    [PaymentController::class, 'fedapayCallback']
)->name('fedapay.callback');

Route::get(
    '/payments/fedapay/callback',
    [PaymentController::class, 'fedapayReturn']
)->name('fedapay.callback.return');

Route::get('/payments/fedapay/return',
[PaymentController::class, 'fedapayReturn'])
->name('fedapay.return');

Route::post(
    '/payments/fedapay/webhook',
    [PaymentController::class, 'fedapayWebhook']
)->name('fedapay.webhook');


/*
|--------------------------------------------------------------------------
| API protégée
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Authentification
    |--------------------------------------------------------------------------
    */

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/user', function (Request $request) {
	return response()->json([
		'user' => $request->user()
]);
});


    /*
    |--------------------------------------------------------------------------
    | Panier
    |--------------------------------------------------------------------------
    */

    Route::get('/cart', [CartController::class, 'show']);

    Route::post('/cart/items', [CartItemController::class, 'store']);

    Route::put(
        '/cart/items/{cartItem}',
        [CartItemController::class, 'update']
    );

    Route::delete(
        '/cart/items/{cartItem}',
        [CartItemController::class, 'destroy']
    );


    /*
    |--------------------------------------------------------------------------
    | Adresses
    |--------------------------------------------------------------------------
    */

    Route::apiResource('addresses', AddressController::class);

    Route::patch(
        '/addresses/{address}/default',
        [AddressController::class, 'setDefault']
    );


    /*
    |--------------------------------------------------------------------------
    | Commandes
    |--------------------------------------------------------------------------
    */

    Route::apiResource('orders', OrderController::class)
        ->only(['index', 'store', 'show']);


    /*
    |--------------------------------------------------------------------------
    | Avis
    |--------------------------------------------------------------------------
    */

    Route::apiResource('reviews', ReviewController::class)
        ->only(['store', 'update', 'destroy']);


    /*
    |--------------------------------------------------------------------------
    | Paiements
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/orders/{order}/payment',
        [PaymentController::class, 'store']
    );
});



