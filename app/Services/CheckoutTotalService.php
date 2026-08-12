<?php

namespace App\Services;

class CheckoutTotalService
{
    public function currencySnapshotLine(
        int $previousQuantity,
        int $currentQuantity,
        float $previousGrossTotal,
        int $previousNewQuantity,
        float $previousNewUnitPrice,
        float $currentUnitPrice,
        bool $previousAdjustmentSettled
    ): array {
        $previousQuantity = max($previousQuantity, 0);
        $currentQuantity = max($currentQuantity, 0);
        $currentUnitPrice = max($currentUnitPrice, 0);

        if ($previousAdjustmentSettled) {
            $lockedQuantity = min($previousQuantity, $currentQuantity);
            $lockedTotal = $previousQuantity > 0
                ? $previousGrossTotal * $lockedQuantity / $previousQuantity
                : 0;
        } else {
            $lockedQuantity = min(
                max($previousQuantity - max($previousNewQuantity, 0), 0),
                $currentQuantity
            );
            $lockedTotal = max(
                $previousGrossTotal
                - max($previousNewQuantity, 0) * max($previousNewUnitPrice, 0),
                0
            );
            if ($lockedQuantity < max($previousQuantity - $previousNewQuantity, 0)) {
                $baseQuantity = max($previousQuantity - $previousNewQuantity, 0);
                $lockedTotal = $baseQuantity > 0
                    ? $lockedTotal * $lockedQuantity / $baseQuantity
                    : 0;
            }
        }

        $newQuantity = max($currentQuantity - $lockedQuantity, 0);
        $grossTotal = round($lockedTotal + $newQuantity * $currentUnitPrice, 2);

        return [
            'new_quantity' => $newQuantity,
            'new_unit_price' => round($currentUnitPrice, 2),
            'gross_total' => $grossTotal,
            'average_unit_price' => $currentQuantity > 0
                ? round($grossTotal / $currentQuantity, 2)
                : 0.0,
        ];
    }

    public function adjustmentAmount(array $pricing, array $extras, array $fees): float
    {
        $amount = collect($pricing)->sum(fn ($item) =>
            max((int) ($item['newly_added_quantity'] ?? 0), 0)
            * max((float) ($item['newly_added_price'] ?? $item['actual_price'] ?? $item['price'] ?? 0), 0)
        );
        $amount += collect($extras)->sum(fn ($item) =>
            max((int) ($item['newly_added_quantity'] ?? 0), 0)
            * max((float) ($item['newly_added_price'] ?? $item['price'] ?? 0), 0)
        );
        $amount += collect($fees)->sum(
            fn ($fee) => max((float) ($fee['newly_added_amount'] ?? 0), 0)
        );

        return round($amount, 2);
    }

    public function hasMatchingAdjustmentPayment(float $adjustmentAmount, iterable $payments): bool
    {
        if ($adjustmentAmount <= 0.01) return false;

        foreach ($payments as $payment) {
            $status = strtolower((string) data_get($payment, 'status'));
            $type = strtoupper((string) data_get($payment, 'payment_type'));
            if (
                $status === 'succeeded'
                && !in_array($type, ['DISCOUNT', 'PROMOCODE', 'BOOKINGFEE', 'REFUND', 'COMMISSION', 'EXCLUDED'], true)
                && abs((float) data_get($payment, 'amount') - $adjustmentAmount) <= 0.02
            ) {
                return true;
            }
        }

        return false;
    }

    public function nextAdjustmentQuantity(
        int $previousQuantity,
        int $currentQuantity,
        int $carriedAdjustmentQuantity,
        bool $previousAdjustmentSettled
    ): int {
        $carried = $previousAdjustmentSettled ? 0 : max($carriedAdjustmentQuantity, 0);

        return min(
            max($currentQuantity, 0),
            max($carried + max($currentQuantity - $previousQuantity, 0), 0)
        );
    }

    public function nextAdjustmentTax(
        float $previousTax,
        float $currentTax,
        float $carriedAdjustmentTax,
        bool $previousAdjustmentSettled
    ): float {
        $originalTax = $previousAdjustmentSettled
            ? max($previousTax, 0)
            : max($previousTax - max($carriedAdjustmentTax, 0), 0);

        return round(max($currentTax - $originalTax, 0), 2);
    }

    public function discountAdjustedTaxableSubtotal(
        float $grossItemsAndAddons,
        float $specialDiscount,
        float $promoDiscount,
        float $bookingFee = 0
    ): float {
        return round(max(
            $grossItemsAndAddons
            - max($specialDiscount, 0)
            - max($promoDiscount, 0)
            + max($bookingFee, 0),
            0
        ), 2);
    }

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
