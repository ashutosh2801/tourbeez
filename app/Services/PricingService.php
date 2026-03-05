<?php

namespace App\Services;

use App\Models\TourSpecialDeposit;

class PricingService
{
    public static function applyTourDiscount(
        $tour,
        float $amount,
        bool $isDiscountEnabled
    ): float {

        // Guard 1 — request flag
        if (!$isDiscountEnabled) {
            return 0;
        }

        $depositRule = TourSpecialDeposit::where('tour_id', $tour->id)
            ->where('is_discount', 1)
            ->first();
        \Log::warning($depositRule);
        if (!$depositRule) {
            return 0;
        }

        $discount = 0;

        if ($depositRule->discount_type === 'PERCENT') {
            $discount = ($amount * $depositRule->discount_value) / 100;
        }

        if ($depositRule->discount_type === 'FIXED') {
            $discount = $depositRule->discount_value;
        }

        return min($discount, $amount);
    }
}
