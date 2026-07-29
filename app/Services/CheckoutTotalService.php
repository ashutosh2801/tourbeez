<?php

namespace App\Services;

class CheckoutTotalService
{
    public function taxableSubtotal(
        float $discountedItemsTotal,
        float $paidAmount,
        float $addonsTotal,
        float $bookingFee
    ): float {
        return round(
            max($discountedItemsTotal - $paidAmount, 0)
            + max($addonsTotal, 0)
            + max($bookingFee, 0),
            2
        );
    }

    public function outstandingTotal(float $grossTotal, float $totalCredits): float
    {
        return round(max($grossTotal - $totalCredits, 0), 2);
    }
}
