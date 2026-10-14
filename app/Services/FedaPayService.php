<?php

namespace App\Services;

use FedaPay\FedaPay;
use FedaPay\Transaction;

class FedaPayService
{
    public function __construct()
    {
        FedaPay::setApiKey(
            config('services.fedapay.secret_key')
        );

        FedaPay::setEnvironment(
            config('services.fedapay.environment', 'sandbox')
        );
    }

    /**
     * Crée une transaction FedaPay.
     */
  public function createTransaction(
    float $amount,
    string $description,
    string $firstname,
    string $lastname,
    string $email,
    ?string $phone = null,
    ?string $callbackUrl = null,
    ?string $returnUrl = null
) {
    $customer = [
        'firstname' => $firstname,
        'lastname' => $lastname,
        'email' => $email,
    ];

    if ($phone) {
        $customer['phone_number'] = [
            'number' => $phone,
            'country' => 'bj',
        ];
    }

    $data = [
        'description' => $description,
        'amount' => (int) round($amount),
        'currency' => [
            'iso' => 'XOF',
        ],
        'customer' => $customer,
    ];

    if ($callbackUrl) {
        $data['callback_url'] = $callbackUrl;
    }

    if ($returnUrl) {
        $data['return_url'] = $returnUrl;
    }

   \Log::info('FEDAPAY TRANSACTION CREATE', [
    'amount' => $data['amount'] ?? null,
    'currency' => $data['currency']['iso'] ?? null,
    'description' => $data['description'] ?? null,
]);
    try {

        return Transaction::create($data);

    } catch (\FedaPay\Error\ApiConnection $e) {

        \Log::error('FEDAPAY API ERROR', [
            'message' => $e->getMessage(),
            'errors' => $e->hasErrors()
                ? $e->getErrors()
                : null,
            'error_message' => $e->hasErrors()
                ? $e->getErrorMessage()
                : null,
            'data' => $data,
        ]);

        throw $e;

    } catch (\Throwable $e) {

        \Log::error('FEDAPAY GENERAL ERROR', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'data' => $data,
        ]);

        throw $e;
    }
}

    /**
     * Génère l'URL de paiement FedaPay.
     */
    public function generatePaymentUrl($transaction): string
    {
        $token = $transaction->generateToken();

        return $token->url;
    }
}
