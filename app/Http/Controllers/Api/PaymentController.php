<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use FedaPay\FedaPay;
use FedaPay\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    /**
     * Créer un paiement FedaPay pour une commande.
     *
     * Cette méthode est conservée pour les cas où le frontend
     * appelle directement /orders/{order}/payment.
     */
    public function store(Request $request, string $order)
    {
        $orderModel = Order::with('payment')
            ->where('user_id', $request->user()->id)
            ->findOrFail($order);

        /*
        |--------------------------------------------------------------------------
        | Vérifier si un paiement existe déjà
        |--------------------------------------------------------------------------
        */

        if ($orderModel->payment) {
            return response()->json([
                'message' => 'Un paiement existe déjà pour cette commande.',
                'payment' => $orderModel->payment,
            ], 409);
        }

        try {
            /*
            |--------------------------------------------------------------------------
            | Configuration FedaPay
            |--------------------------------------------------------------------------
            */

            FedaPay::setApiKey(
                config('services.fedapay.secret_key')
            );

            FedaPay::setEnvironment(
                config('services.fedapay.environment', 'sandbox')
            );

            /*
            |--------------------------------------------------------------------------
            | Créer la transaction FedaPay
            |--------------------------------------------------------------------------
            */

            $transaction = Transaction::create([
                'description' => 'Commande OUSWEAR ' . $orderModel->order_number,

                'amount' => (int) round($orderModel->total),

                'currency' => [
                    'iso' => 'XOF',
                ],

                'callback_url' => config('services.fedapay.callback_url'),

                'return_url' => route('fedapay.return'),

                'customer' => [
                    'firstname' => $request->user()->name,
                    'lastname' => '',
                    'email' => $request->user()->email,
                ],
            ]);

            /*
            |--------------------------------------------------------------------------
            | Enregistrer le paiement OUSWEAR
            |--------------------------------------------------------------------------
            */

            $payment = Payment::create([
                'order_id' => $orderModel->id,
                'reference' => 'PAY-' . $orderModel->order_number,
                'method' => 'fedapay',
                'amount' => $orderModel->total,
                'status' => 'pending',
                'transaction_id' => (string) $transaction->id,
                'paid_at' => null,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Générer l'URL de paiement
            |--------------------------------------------------------------------------
            */

            $token = $transaction->generateToken();

            $checkoutUrl = $token->url ?? null;

            /*
            |--------------------------------------------------------------------------
            | Retourner les informations au frontend
            |--------------------------------------------------------------------------
            */

            return response()->json([
                'message' => 'Transaction FedaPay créée.',
                'payment' => $payment,
                'transaction' => [
                    'id' => $transaction->id,
                    'status' => $transaction->status ?? null,
                ],
                'checkout_url' => $checkoutUrl,
            ], 201);

        } catch (\Throwable $e) {

            Log::error('FEDAPAY CREATE TRANSACTION ERROR', [
                'order_id' => $orderModel->id,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'message' => 'Impossible de créer le paiement FedaPay.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Callback FedaPay.
     *
     * Cette route est publique.
     * FedaPay appelle cette URL après le traitement
     * de la transaction.
     */
    public function fedapayCallback(Request $request)
    {
        Log::info('FEDAPAY CALLBACK REÇU', [
            'payload' => $request->all(),
        ]);

        try {
            /*
            |--------------------------------------------------------------------------
            | Récupérer l'identifiant de transaction
            |--------------------------------------------------------------------------
            */

            $transactionId =
                $request->input('id')
                ?? $request->input('transaction_id')
                ?? data_get($request->all(), 'transaction.id');

            if (!$transactionId) {
                Log::warning(
                    'FEDAPAY CALLBACK : transaction_id absent',
                    [
                        'payload' => $request->all(),
                    ]
                );

                return response()->json([
                    'message' => 'Identifiant de transaction manquant.',
                ], 400);
            }

            /*
            |--------------------------------------------------------------------------
            | Configuration FedaPay
            |--------------------------------------------------------------------------
            */

            FedaPay::setApiKey(
                config('services.fedapay.secret_key')
            );

            FedaPay::setEnvironment(
                config('services.fedapay.environment', 'sandbox')
            );

            /*
            |--------------------------------------------------------------------------
            | Récupérer la transaction directement chez FedaPay
            |--------------------------------------------------------------------------
            */

            $transaction = Transaction::retrieve($transactionId);

            Log::info('FEDAPAY TRANSACTION RECUPEREE', [
                'id' => $transaction->id ?? null,
                'status' => $transaction->status ?? null,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Chercher le paiement OUSWEAR
            |--------------------------------------------------------------------------
            */

            $payment = Payment::where(
                'transaction_id',
                (string) $transactionId
            )->first();

            if (!$payment) {
                Log::warning(
                    'FEDAPAY : paiement OUSWEAR introuvable',
                    [
                        'transaction_id' => $transactionId,
                    ]
                );

                return response()->json([
                    'message' => 'Paiement OUSWEAR introuvable.',
                ], 404);
            }

            /*
            |--------------------------------------------------------------------------
            | Normaliser le statut FedaPay
            |--------------------------------------------------------------------------
            */

            $status = strtolower(
                (string) ($transaction->status ?? '')
            );

            /*
            |--------------------------------------------------------------------------
            | Paiement réussi
            |--------------------------------------------------------------------------
            */

            if (in_array($status, [
                'approved',
                'transferred',
                'paid',
            ], true)) {

                DB::transaction(function () use ($payment) {

                    $payment->update([
                        'status' => 'paid',
                        'paid_at' => now(),
                    ]);

                    $payment->order()->update([
                        'status' => 'processing',
                    ]);
                });

                Log::info('FEDAPAY : paiement confirmé', [
                    'payment_id' => $payment->id,
                    'order_id' => $payment->order_id,
                    'transaction_id' => $payment->transaction_id,
                ]);

                return response()->json([
                    'message' => 'Paiement confirmé.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Paiement échoué / annulé
            |--------------------------------------------------------------------------
            */

            if (in_array($status, [
                'canceled',
                'cancelled',
                'declined',
                'failed',
                'rejected',
            ], true)) {

                $payment->update([
                    'status' => 'failed',
                ]);

                $payment->order()->update([
                    'status' => 'cancelled',
                ]);

                Log::warning('FEDAPAY : paiement échoué', [
                    'payment_id' => $payment->id,
                    'order_id' => $payment->order_id,
                    'transaction_id' => $payment->transaction_id,
                    'status' => $status,
                ]);

                return response()->json([
                    'message' => 'Paiement échoué.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Paiement encore en attente
            |--------------------------------------------------------------------------
            */

            Log::info('FEDAPAY : paiement encore en attente', [
                'payment_id' => $payment->id,
                'transaction_id' => $transactionId,
                'status' => $status,
            ]);

            return response()->json([
                'message' => 'Paiement encore en attente.',
                'status' => $status,
            ]);

        } catch (\Throwable $e) {

            Log::error('FEDAPAY CALLBACK ERROR', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'message' => 'Erreur lors du traitement du paiement.',
            ], 500);
        }
    }

    /**
     * Retour du navigateur après le paiement FedaPay.
     *
     * FedaPay redirige le navigateur vers cette route
     * après le paiement.
     */
    public function fedapayReturn(Request $request)
{
    Log::info('FEDAPAY RETURN REÇU', [
        'payload' => $request->all(),
    ]);

    try {
        /*
        |--------------------------------------------------------------------------
        | Récupération robuste de l'ID de transaction
        |--------------------------------------------------------------------------
        */
        $transactionId =
            $request->input('id')
            ?? $request->input('transaction_id')
            ?? data_get($request->all(), 'transaction.id');

        if (!$transactionId) {
            Log::warning('FEDAPAY RETURN : transaction_id absent', [
                'payload' => $request->all(),
            ]);

            return redirect()->away(
                config('app.frontend_url') . '/payment-error'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Recherche du paiement OUSWEAR
        |--------------------------------------------------------------------------
        */
        $payment = Payment::with('order')
            ->where('transaction_id', (string) $transactionId)
            ->first();

        if (!$payment) {
            Log::warning('FEDAPAY RETURN : paiement introuvable', [
                'transaction_id' => $transactionId,
            ]);

            return redirect()->away(
                config('app.frontend_url') . '/payment-error'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Redirection vers la confirmation de commande Vue
        |--------------------------------------------------------------------------
        */
        $frontendUrl = rtrim(config('app.frontend_url'), '/');

        $redirectUrl =
            $frontendUrl .
            '/order-confirmation/' .
            $payment->order_id;

        Log::info('FEDAPAY RETURN : redirection frontend', [
            'transaction_id' => $transactionId,
            'payment_id' => $payment->id,
            'order_id' => $payment->order_id,
            'redirect_url' => $redirectUrl,
        ]);

        return redirect()->away($redirectUrl);

    } catch (\Throwable $e) {
        Log::error('FEDAPAY RETURN ERROR', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);

        return redirect()->away(
            config('app.frontend_url') . '/payment-error'
        );
    }
}

public function fedapayWebhook(Request $request)
{
    $payload = $request->getContent();
    $signature = $request->header('X-FEDAPAY-SIGNATURE');
    $webhookSecret = config('services.fedapay.webhook_secret');

    /*
    |--------------------------------------------------------------------------
    | Vérification obligatoire de la signature
    |--------------------------------------------------------------------------
    */

    if (!$signature || !$webhookSecret) {
        Log::warning('FEDAPAY WEBHOOK : signature ou secret absent');

        return response()->json([
            'message' => 'Webhook non autorisé.',
        ], 400);
    }

    try {
        /*
        |--------------------------------------------------------------------------
        | Important : on vérifie le RAW BODY
        |--------------------------------------------------------------------------
        */
        $event = \FedaPay\Webhook::constructEvent(
            $payload,
            $signature,
            $webhookSecret
        );
    } catch (\UnexpectedValueException $e) {
        Log::warning('FEDAPAY WEBHOOK : payload invalide', [
            'message' => $e->getMessage(),
        ]);

        return response()->json([
            'message' => 'Payload invalide.',
        ], 400);
    } catch (\FedaPay\Error\SignatureVerification $e) {
        Log::warning('FEDAPAY WEBHOOK : signature invalide');

        return response()->json([
            'message' => 'Signature invalide.',
        ], 400);
    } catch (\Throwable $e) {
        Log::error('FEDAPAY WEBHOOK : erreur vérification', [
            'message' => $e->getMessage(),
        ]);

        return response()->json([
            'message' => 'Webhook invalide.',
        ], 400);
    }

    /*
    |--------------------------------------------------------------------------
    | Type d'événement
    |--------------------------------------------------------------------------
    */

    $eventType =
        $event->type
        ?? $event->name
        ?? null;

    /*
    |--------------------------------------------------------------------------
    | ID transaction
    |--------------------------------------------------------------------------
    */

    $transactionId = $event->object_id ?? null;

    if (!$transactionId) {
        $eventData = json_decode($payload, true);

        $transactionId =
            data_get($eventData, 'object_id')
            ?? data_get($eventData, 'entity.id');
    }

    Log::info('FEDAPAY WEBHOOK REÇU', [
        'event_type' => $eventType,
        'transaction_id' => $transactionId,
    ]);

    /*
    |--------------------------------------------------------------------------
    | On ne traite que les événements transaction
    |--------------------------------------------------------------------------
    */

    $allowedEvents = [
        'transaction.approved',
        'transaction.canceled',
        'transaction.declined',
    ];

    if (!in_array($eventType, $allowedEvents, true)) {
        Log::info('FEDAPAY WEBHOOK : événement ignoré', [
            'event_type' => $eventType,
        ]);

        return response()->json([
            'message' => 'Événement ignoré.',
        ], 200);
    }

    if (!$transactionId) {
        Log::warning('FEDAPAY WEBHOOK : transaction_id absent');

        return response()->json([
            'message' => 'Transaction introuvable.',
        ], 400);
    }

    try {
        /*
        |--------------------------------------------------------------------------
        | Vérification directe auprès de FedaPay
        |--------------------------------------------------------------------------
        */

        FedaPay::setApiKey(
            config('services.fedapay.secret_key')
        );

        FedaPay::setEnvironment(
            config('services.fedapay.environment', 'sandbox')
        );

        $transaction = Transaction::retrieve($transactionId);

        $status = strtolower(
            (string) ($transaction->status ?? '')
        );

        /*
        |--------------------------------------------------------------------------
        | Paiement OUSWEAR correspondant
        |--------------------------------------------------------------------------
        */

        $payment = Payment::where(
            'transaction_id',
            (string) $transactionId
        )->first();

        if (!$payment) {
            Log::warning('FEDAPAY WEBHOOK : paiement OUSWEAR introuvable', [
                'transaction_id' => $transactionId,
            ]);

            return response()->json([
                'message' => 'Paiement introuvable.',
            ], 404);
        }

        /*
        |--------------------------------------------------------------------------
        | Paiement réussi
        |--------------------------------------------------------------------------
        */

        if (in_array($status, [
            'approved',
            'transferred',
            'paid',
        ], true)) {

            DB::transaction(function () use ($payment) {

                /*
                 * Idempotence :
                 * si le webhook est reçu plusieurs fois,
                 * on ne recrée rien.
                 */
                if ($payment->status !== 'paid') {
                    $payment->update([
                        'status' => 'paid',
                        'paid_at' => $payment->paid_at ?? now(),
                    ]);
                }

                $payment->order()->update([
                    'status' => 'processing',
                ]);
            });

            Log::info('FEDAPAY WEBHOOK : paiement confirmé', [
                'payment_id' => $payment->id,
                'order_id' => $payment->order_id,
                'transaction_id' => $transactionId,
            ]);

            return response()->json([
                'message' => 'Paiement confirmé.',
            ], 200);
        }

        /*
        |--------------------------------------------------------------------------
        | Paiement échoué
        |--------------------------------------------------------------------------
        */

        if (in_array($status, [
            'canceled',
            'cancelled',
            'declined',
            'failed',
            'rejected',
        ], true)) {

            DB::transaction(function () use ($payment) {

                if ($payment->status !== 'paid') {
                    $payment->update([
                        'status' => 'failed',
                    ]);

                    $payment->order()->update([
                        'status' => 'cancelled',
                    ]);
                }
            });

            Log::warning('FEDAPAY WEBHOOK : paiement échoué', [
                'payment_id' => $payment->id,
                'order_id' => $payment->order_id,
                'transaction_id' => $transactionId,
                'status' => $status,
            ]);

            return response()->json([
                'message' => 'Paiement échoué.',
            ], 200);
        }

        Log::info('FEDAPAY WEBHOOK : statut non final', [
            'transaction_id' => $transactionId,
            'status' => $status,
        ]);

        return response()->json([
            'message' => 'Statut non final.',
        ], 200);

    } catch (\Throwable $e) {

        Log::error('FEDAPAY WEBHOOK ERROR', [
            'transaction_id' => $transactionId,
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);

        return response()->json([
            'message' => 'Erreur lors du traitement du webhook.',
        ], 500);
    }
}

}
