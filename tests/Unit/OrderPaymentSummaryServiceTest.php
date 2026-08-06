<?php

namespace Tests\Unit;

use App\Services\OrderPaymentSummaryService;
use PHPUnit\Framework\TestCase;

class OrderPaymentSummaryServiceTest extends TestCase
{
    public function test_it_separates_payments_fees_and_item_discounts_without_double_counting(): void
    {
        $summary = (new OrderPaymentSummaryService())->summarize([
            ['status' => 'succeeded', 'payment_type' => 'CARD', 'amount' => 75],
            ['status' => 'succeeded', 'payment_type' => 'BOOKINGFEE', 'amount' => 5],
            ['status' => 'discount', 'payment_type' => 'DISCOUNT', 'amount' => 10],
            [
                'status' => 'discount',
                'payment_type' => 'PROMOCODE',
                'amount' => 7.5,
                'transaction_id' => 'SAVE10',
            ],
            ['status' => 'pending', 'payment_type' => 'CARD', 'amount' => 100],
            ['status' => 'failed', 'payment_type' => 'CARD', 'amount' => 50],
        ]);

        $this->assertSame(75.0, $summary['paid_amount']);
        $this->assertSame(5.0, $summary['booking_fee']);
        $this->assertSame(10.0, $summary['special_discount']);
        $this->assertSame(7.5, $summary['promo_discount']);
        $this->assertSame('SAVE10', $summary['promo_code']);
        $this->assertSame(92.5, $summary['total_credits']);
    }

    public function test_negative_amounts_cannot_reduce_existing_credits(): void
    {
        $summary = (new OrderPaymentSummaryService())->summarize([
            ['status' => 'succeeded', 'payment_type' => 'CARD', 'amount' => -10],
            ['status' => 'discount', 'payment_type' => 'PROMOCODE', 'amount' => -5],
        ]);

        $this->assertSame(0.0, $summary['total_credits']);
    }

    public function test_uncaptured_authorization_does_not_reduce_the_balance(): void
    {
        $summary = (new OrderPaymentSummaryService())->summarize([
            ['status' => 'succeeded', 'payment_type' => 'CARD', 'amount' => 75],
            ['status' => 'uncaptured', 'payment_type' => 'CARD', 'amount' => 100],
            ['status' => 'requires_capture', 'payment_type' => 'CARD', 'amount' => 25],
            ['status' => 'discount', 'payment_type' => 'DISCOUNT', 'amount' => 10],
        ]);

        $this->assertSame(75.0, $summary['paid_amount']);
        $this->assertSame(125.0, $summary['authorized_amount']);
        $this->assertSame(85.0, $summary['total_credits']);
    }

    public function test_partial_and_full_refunds_reduce_paid_amount_once(): void
    {
        $summary = (new OrderPaymentSummaryService())->summarize([
            [
                'status' => 'partial_refunded',
                'payment_type' => 'CARD',
                'amount' => 100,
                'refund_amount' => 30,
            ],
            [
                'status' => 'refunded',
                'payment_type' => 'REFUND',
                'amount' => 30,
            ],
        ]);

        $this->assertSame(30.0, $summary['refunded_amount']);
        $this->assertSame(70.0, $summary['paid_amount']);
        $this->assertSame(70.0, $summary['total_credits']);
    }
}
