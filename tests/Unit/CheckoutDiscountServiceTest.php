<?php

namespace Tests\Unit;

use App\Services\CheckoutDiscountService;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\TestCase;

class CheckoutDiscountServiceTest extends TestCase
{
    public function test_special_discount_requires_an_eligible_tour_date(): void
    {
        $service = new CheckoutDiscountService();
        $rule = [
            'is_discount' => true,
            'charge' => 'NONE',
            'notice_days' => 2,
        ];

        $today = CarbonImmutable::parse('2026-07-25');

        $this->assertTrue(
            $service->isSpecialDiscountEligible($rule, CarbonImmutable::parse('2026-07-27'), $today)
        );
        $this->assertFalse(
            $service->isSpecialDiscountEligible($rule, CarbonImmutable::parse('2026-07-26'), $today)
        );
    }

    public function test_percent_and_fixed_discounts_are_capped_at_the_item_price(): void
    {
        $service = new CheckoutDiscountService();

        $this->assertSame(10.0, $service->specialDiscount(100, [
            'discount_type' => 'PERCENT',
            'discount_value' => 10,
        ]));
        $this->assertSame(40.0, $service->specialDiscount(40, [
            'discount_type' => 'FIXED',
            'discount_value' => 100,
        ]));
    }
}
