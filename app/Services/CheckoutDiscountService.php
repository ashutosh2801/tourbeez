<?php

namespace App\Services;

use Carbon\CarbonInterface;

class CheckoutDiscountService
{
    public function isSpecialDiscountEligible(
        mixed $rule,
        CarbonInterface $tourDate,
        CarbonInterface $today
    ): bool {
        if (
            !$rule
            || !(bool) data_get($rule, 'is_discount')
            || strtoupper((string) data_get($rule, 'charge')) !== 'NONE'
        ) {
            return false;
        }

        $noticeDays = max((int) data_get($rule, 'notice_days', 0), 0);
        $daysUntilTour = $today->startOfDay()->diffInDays($tourDate->startOfDay(), false);

        return $daysUntilTour >= $noticeDays;
    }

    public function specialDiscount(float $unitPrice, mixed $rule): float
    {
        $unitPrice = max($unitPrice, 0);
        $value = max((float) data_get($rule, 'discount_value', 0), 0);

        $discount = strtoupper((string) data_get($rule, 'discount_type')) === 'PERCENT'
            ? $unitPrice * min($value, 100) / 100
            : $value;

        return round(min($discount, $unitPrice), 2);
    }
}
