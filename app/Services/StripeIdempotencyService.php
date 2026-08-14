<?php

namespace App\Services;

class StripeIdempotencyService
{
    public function checkoutIntentKey(
        int $orderId,
        string $intentType,
        string $action,
        float $amount,
        string $currency,
        int $attemptSequence
    ): string {
        $fingerprint = implode('|', [
            $orderId,
            strtolower($intentType),
            strtolower($action),
            number_format($amount, 2, '.', ''),
            strtolower($currency),
            max($attemptSequence, 1),
        ]);

        return 'tourbeez-checkout-' . hash('sha256', $fingerprint);
    }
}
