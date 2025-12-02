<?php

namespace App\Payments;

class DummyPayment implements PaymentInterface
{
    public function createCharge(array $data): array
    {
        // Very simple dummy implementation that always succeeds and returns a mock transaction id
        $transactionId = 'dummy_' . uniqid();

        return [
            'success' => true,
            'transaction_id' => $transactionId,
            'raw' => [
                'amount' => $data['amount'] ?? null,
                'currency' => $data['currency'] ?? 'USD',
                'method' => $data['method'] ?? null,
            ],
        ];
    }
}

