<?php

namespace App\Services;

use Illuminate\Support\Arr;

class OrderPaymentSummaryService
{
    /**
     * Build the accounting credits applied to an order.
     *
     * Discounts and booking fees are kept separate so callers cannot
     * accidentally count the booking fee as both a payment and a fee.
     */
    public function summarize(iterable $payments): array
    {
        $summary = [
            'paid_amount' => 0.0,
            'authorized_amount' => 0.0,
            'booking_fee' => 0.0,
            'special_discount' => 0.0,
            'promo_discount' => 0.0,
            'promo_code' => '',
            'refunded_amount' => 0.0,
        ];
        $grossPayments = 0.0;
        $refundLedgerAmount = 0.0;
        $paymentRefundAmount = 0.0;

        foreach ($payments as $payment) {
            $status = strtolower((string) $this->value($payment, 'status'));
            $type = strtoupper((string) $this->value($payment, 'payment_type'));
            $amount = max((float) $this->value($payment, 'amount'), 0);

            if ($status === 'discount' && $type === 'DISCOUNT') {
                $summary['special_discount'] += $amount;
                continue;
            }

            if ($status === 'discount' && $type === 'PROMOCODE') {
                $summary['promo_discount'] += $amount;
                $summary['promo_code'] = (string) $this->value($payment, 'transaction_id');
                continue;
            }

            if ($status !== 'succeeded') {
                if (in_array($status, ['uncaptured', 'requires_capture'], true)) {
                    if (!in_array($type, ['BOOKINGFEE', 'REFUND'], true)) {
                        $summary['authorized_amount'] += $amount;
                    }
                    continue;
                }
                if (!in_array($status, ['partial_refunded', 'refunded'], true)) {
                    continue;
                }
            }

            if ($type === 'BOOKINGFEE') {
                $summary['booking_fee'] += $amount;
                continue;
            }

            if ($type === 'REFUND') {
                $refundLedgerAmount += $amount;
                continue;
            }

            $grossPayments += $amount;
            $paymentRefundAmount += min(
                max((float) $this->value($payment, 'refund_amount'), 0),
                $amount
            );
        }

        $summary['refunded_amount'] = max($paymentRefundAmount, $refundLedgerAmount);
        $summary['paid_amount'] = max($grossPayments - $summary['refunded_amount'], 0);

        foreach (['paid_amount', 'authorized_amount', 'booking_fee', 'special_discount', 'promo_discount', 'refunded_amount'] as $key) {
            $summary[$key] = round($summary[$key], 2);
        }

        $summary['total_credits'] = round(
            $summary['paid_amount']
            + $summary['special_discount']
            + $summary['promo_discount'],
            2
        );

        return $summary;
    }

    private function value(mixed $payment, string $key): mixed
    {
        return is_array($payment)
            ? Arr::get($payment, $key)
            : data_get($payment, $key);
    }
}
