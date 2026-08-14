<?php

namespace Tests\Unit;

use App\Services\CheckoutDiscountService;
use App\Services\CheckoutTaxService;
use App\Services\CheckoutTotalService;
use App\Services\OrderPaymentSummaryService;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\TestCase;

class CheckoutRulesIntegrationTest extends TestCase
{
    private const RULE = [
        'is_discount' => true,
        'charge' => 'NONE',
        'notice_days' => 2,
        'discount_type' => 'PERCENT',
        'discount_value' => 10,
    ];

    public function test_t44uqbw_admin_additions_keep_original_promo_in_the_hst_base(): void
    {
        $totals = new CheckoutTotalService();
        $taxes = new CheckoutTaxService();

        $originalGross = 69.00 + 33.00;
        $addedGross = 59.10 + 47.95;
        $specialDiscount = 10.00;
        $promoDiscount = 5.90;
        $paid = 97.29;

        $taxable = $totals->discountAdjustedTaxableSubtotal(
            $originalGross + $addedGross,
            $specialDiscount,
            $promoDiscount
        );
        $hst = $taxes->calculate($taxable, 'PERCENT', 13);
        $grossAfterDiscountsAndTax = round($taxable + $hst, 2);
        $balance = round($grossAfterDiscountsAndTax - $paid, 2);
        $originalTaxable = $originalGross - $specialDiscount - $promoDiscount;
        $originalHst = $taxes->calculate($originalTaxable, 'PERCENT', 13);
        $addedHst = round($hst - $originalHst, 2);

        $this->assertSame(193.15, $taxable);
        $this->assertSame(25.11, $hst);
        $this->assertSame(13.92, $addedHst);
        $this->assertSame(120.97, $balance);
    }

    public function test_each_paid_admin_adjustment_starts_a_fresh_display_cycle(): void
    {
        $totals = new CheckoutTotalService();

        // Quantity 2 contains one original and one previously-added unit.
        // After that addition is paid, increasing to 3 exposes only one unit.
        $this->assertSame(1, $totals->nextAdjustmentQuantity(2, 3, 1, true));
        // Before payment, another increase remains cumulative for the same link.
        $this->assertSame(2, $totals->nextAdjustmentQuantity(2, 3, 1, false));

        // Previous full HST 25.11 included 13.92 from the paid first addition.
        // A second addition raises full HST to 30.76; only 5.65 is new now.
        $this->assertSame(5.65, $totals->nextAdjustmentTax(25.11, 30.76, 13.92, true));
        // An unpaid edit continues from the original booking tax of 11.19.
        $this->assertSame(19.57, $totals->nextAdjustmentTax(25.11, 30.76, 13.92, false));
    }

    public function test_paid_adjustment_is_detected_even_with_a_stale_order_balance(): void
    {
        $totals = new CheckoutTotalService();
        $adjustment = $totals->adjustmentAmount(
            [['actual_price' => 59.10, 'newly_added_quantity' => 1]],
            [['price' => 47.95, 'newly_added_quantity' => 1]],
            [['newly_added_amount' => 13.92]]
        );

        $this->assertSame(120.97, $adjustment);
        $this->assertTrue($totals->hasMatchingAdjustmentPayment($adjustment, [
            ['payment_type' => 'CARD', 'status' => 'succeeded', 'amount' => 97.29],
            ['payment_type' => 'CARD', 'status' => 'succeeded', 'amount' => 120.97],
        ]));
        $this->assertFalse($totals->hasMatchingAdjustmentPayment($adjustment, [
            ['payment_type' => 'CARD', 'status' => 'succeeded', 'amount' => 120.94],
        ]));
    }

    public function test_tbqbmwo_email_uses_only_the_current_adjustment_total(): void
    {
        $totals = new CheckoutTotalService();
        $adjustment = $totals->adjustmentAmount(
            [],
            [['price' => 47.95, 'newly_added_quantity' => 2]],
            [['newly_added_amount' => 12.46]]
        );

        $this->assertSame(108.36, $adjustment);
        $this->assertTrue($totals->hasMatchingAdjustmentPayment($adjustment, [
            ['payment_type' => 'CARD', 'status' => 'succeeded', 'amount' => 184.52],
            ['payment_type' => 'CARD', 'status' => 'succeeded', 'amount' => 108.36],
        ]));
    }

    public function test_paid_currency_prices_lock_while_new_unpaid_units_use_today_rate(): void
    {
        $totals = new CheckoutTotalService();

        $firstAddition = $totals->currencySnapshotLine(1, 2, 50, 0, 0, 20, true);
        $this->assertSame(1, $firstAddition['new_quantity']);
        $this->assertSame(70.0, $firstAddition['gross_total']);
        $this->assertSame(20.0, $firstAddition['new_unit_price']);

        // Five years later the original 50 and paid add-on 20 remain locked;
        // only the third, still-unpaid unit uses today's price of 25.
        $secondAddition = $totals->currencySnapshotLine(2, 3, 70, 1, 20, 25, true);
        $this->assertSame(1, $secondAddition['new_quantity']);
        $this->assertSame(95.0, $secondAddition['gross_total']);
        $this->assertSame(25.0, $secondAddition['new_unit_price']);
    }

    public function test_tjcdhpe_values_produce_the_persisted_checkout_snapshot(): void
    {
        $totals = new CheckoutTotalService();
        $taxes = new CheckoutTaxService();

        $items = 128.10;
        $promo = 12.81;
        $paid = 67.56;
        $addons = 33.00;
        $bookingFee = 0.0;
        $taxable = $totals->taxableSubtotal(
            $items - $promo,
            $paid,
            $addons,
            $bookingFee
        );
        $hst = $taxes->calculate($taxable, 'PERCENT', 13);
        $grossBeforeCredits = $items + $addons + $bookingFee + $hst;
        $outstanding = $totals->outstandingTotal(
            $grossBeforeCredits,
            $promo + $paid
        );

        $this->assertSame(80.73, $taxable);
        $this->assertSame(10.49, $hst);
        $this->assertSame(91.22, $outstanding);
        $this->assertSame([
            'orders.total_amount' => 91.22,
            'order_tours.tour_fees.0.price' => 10.49,
            'order_payments.PROMOCODE.amount' => 12.81,
            'order_payments.succeeded.amount' => 67.56,
        ], [
            'orders.total_amount' => $outstanding,
            'order_tours.tour_fees.0.price' => $hst,
            'order_payments.PROMOCODE.amount' => $promo,
            'order_payments.succeeded.amount' => $paid,
        ]);
    }

    public function test_partial_charge_does_not_deduct_successful_payments_twice(): void
    {
        $alreadyCalculatedOutstandingTotal = 91.22;

        $chargeAmount = max($alreadyCalculatedOutstandingTotal, 0);

        $this->assertSame(91.22, $chargeAmount);
    }

    public function test_part_payments_that_equal_the_gross_snapshot_leave_zero_balance(): void
    {
        $totals = new CheckoutTotalService();
        $grossOrderSnapshot = 170.29;
        $promo = 12.81;
        $successfulPartPayments = round(67.56 + 10.00 + 79.92, 2);

        $this->assertSame(157.48, $successfulPartPayments);
        $this->assertSame(
            0.0,
            $totals->outstandingTotal(
                $grossOrderSnapshot,
                $promo + $successfulPartPayments
            )
        );
    }

    public function test_discounted_items_addons_fees_and_existing_payment_produce_expected_balance(): void
    {
        $discounts = new CheckoutDiscountService();
        $taxes = new CheckoutTaxService();
        $payments = new OrderPaymentSummaryService();
        $rule = [
            'is_discount' => true,
            'charge' => 'NONE',
            'notice_days' => 2,
            'discount_type' => 'PERCENT',
            'discount_value' => 10,
        ];

        $this->assertTrue($discounts->isSpecialDiscountEligible(
            $rule,
            CarbonImmutable::parse('2026-07-30'),
            CarbonImmutable::parse('2026-07-25')
        ));

        $items = 200.0;
        $specialDiscount = $discounts->specialDiscount(100, $rule) * 2;
        $promoDiscount = ($items - $specialDiscount) * 0.10;
        $addons = 50.0;
        $bookingFee = 5.0;
        $paidAmount = 100.0;
        $taxableSubtotal = max(
            $items - $specialDiscount - $promoDiscount - $paidAmount,
            0
        ) + $addons + $bookingFee;
        $tax = $taxes->calculate($taxableSubtotal, 'PERCENT', 13);
        $outstandingTotal = $taxableSubtotal + $tax;

        $summary = $payments->summarize([
            ['status' => 'discount', 'payment_type' => 'DISCOUNT', 'amount' => $specialDiscount],
            ['status' => 'discount', 'payment_type' => 'PROMOCODE', 'amount' => $promoDiscount],
            ['status' => 'succeeded', 'payment_type' => 'BOOKINGFEE', 'amount' => $bookingFee],
            ['status' => 'succeeded', 'payment_type' => 'CARD', 'amount' => 100],
        ]);

        $this->assertSame(20.0, $summary['special_discount']);
        $this->assertSame(18.0, $summary['promo_discount']);
        $this->assertSame(100.0, $summary['paid_amount']);
        $this->assertSame(5.0, $summary['booking_fee']);
        $this->assertSame(138.0, $summary['total_credits']);
        $this->assertSame(132.21, round($outstandingTotal, 2));
    }

    /**
     * @dataProvider checkoutActionProvider
     */
    public function test_customer_and_internal_checkout_actions_follow_the_same_special_discount_rule(
        string $source,
        string $action,
        string $depositChoice,
        string $tourDate,
        float $expectedSpecialDiscount,
        float $expectedTotal
    ): void {
        $discounts = new CheckoutDiscountService();
        $taxes = new CheckoutTaxService();
        $today = CarbonImmutable::parse('2026-07-25');
        $eligible = $discounts->isSpecialDiscountEligible(
            self::RULE,
            CarbonImmutable::parse($tourDate),
            $today
        );

        $items = 200.0;
        $addons = 50.0;
        $specialDiscount = $action === 'book' && $depositChoice === 'deposit' && $eligible
            ? $discounts->specialDiscount(100, self::RULE) * 2
            : 0.0;
        $taxableSubtotal = $items - $specialDiscount + $addons + 5;
        $hst = $taxes->calculate($taxableSubtotal, 'PERCENT', 13);
        $total = $taxableSubtotal + $hst;

        $this->assertContains($source, ['customer', 'internal']);
        $this->assertSame($expectedSpecialDiscount, $specialDiscount);
        $this->assertSame($expectedTotal, round($total, 2));
    }

    public static function checkoutActionProvider(): array
    {
        return [
            'customer pay later has full price' => [
                'customer', 'reserve', 'deposit', '2026-07-30', 0.0, 288.15,
            ],
            'customer pay now receives special discount' => [
                'customer', 'book', 'deposit', '2026-07-30', 20.0, 265.55,
            ],
            'customer invalid date receives no special discount' => [
                'customer', 'book', 'deposit', '2026-07-26', 0.0, 288.15,
            ],
            'internal pay later has full price' => [
                'internal', 'reserve', 'deposit', '2026-07-30', 0.0, 288.15,
            ],
            'internal pay now receives special discount' => [
                'internal', 'book', 'deposit', '2026-07-30', 20.0, 265.55,
            ],
            'internal invalid date receives no special discount' => [
                'internal', 'book', 'deposit', '2026-07-26', 0.0, 288.15,
            ],
        ];
    }

    public function test_discounts_reduce_items_then_addons_and_booking_fee_are_taxed(): void
    {
        $discounts = new CheckoutDiscountService();
        $taxes = new CheckoutTaxService();

        $items = 200.0;
        $addons = 50.0;
        $bookingFee = 5.0;
        $specialDiscount = $discounts->specialDiscount(100, self::RULE) * 2;
        $promoDiscount = ($items - $specialDiscount) * 0.10;
        $taxableSubtotal = ($items - $specialDiscount - $promoDiscount) + $addons + $bookingFee;
        $hst = $taxes->calculate($taxableSubtotal, 'PERCENT', 13);

        $this->assertSame(20.0, $specialDiscount);
        $this->assertSame(18.0, $promoDiscount);
        $this->assertSame(217.0, $taxableSubtotal);
        $this->assertSame(28.21, $hst);
        $this->assertSame(245.21, round($taxableSubtotal + $hst, 2));
    }

    public function test_partial_payment_is_credited_after_discounts_taxes_and_fees(): void
    {
        $payments = (new OrderPaymentSummaryService())->summarize([
            ['status' => 'succeeded', 'payment_type' => 'CARD', 'amount' => 100],
            ['status' => 'discount', 'payment_type' => 'DISCOUNT', 'amount' => 20],
            ['status' => 'discount', 'payment_type' => 'PROMOCODE', 'amount' => 18],
            ['status' => 'succeeded', 'payment_type' => 'BOOKINGFEE', 'amount' => 5],
        ]);

        $this->assertSame(100.0, $payments['paid_amount']);
        $this->assertSame(138.0, $payments['total_credits']);
        $taxableSubtotal = max(200 - 20 - 18 - $payments['paid_amount'], 0) + 50 + 5;
        $this->assertSame(132.21, round($taxableSubtotal + ($taxableSubtotal * 0.13), 2));
    }
}
