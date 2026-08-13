<?php

namespace App\Services;

class CheckoutTaxService
{
    public function calculate(float $subtotal, string $type, float $value): float
    {
        $subtotal = max($subtotal, 0);
        $value = max($value, 0);

        return round(match (strtoupper($type)) {
            'PERCENT' => $subtotal * min($value, 100) / 100,
            'FIXED_PER_ORDER' => $value,
            default => 0,
        }, 2);
    }
}
