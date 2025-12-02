<?php

namespace App\Payments;

interface PaymentInterface
{
    /**
     * Create charge for given payment data.
     *
     * Expected input keys: amount, currency (optional), description, metadata (optional)
     *
     * Return a standardized array with: ['success' => bool, 'transaction_id' => string|null, 'raw' => mixed]
     */
    public function createCharge(array $data): array;
}

