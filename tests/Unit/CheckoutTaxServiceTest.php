<?php

namespace Tests\Unit;

use App\Services\CheckoutTaxService;
use PHPUnit\Framework\TestCase;

class CheckoutTaxServiceTest extends TestCase
{
    public function test_it_calculates_percent_and_fixed_order_fees(): void
    {
        $service = new CheckoutTaxService();

        $this->assertSame(13.0, $service->calculate(100, 'PERCENT', 13));
        $this->assertSame(8.5, $service->calculate(100, 'FIXED_PER_ORDER', 8.5));
    }

    public function test_it_rejects_negative_and_unknown_fee_effects(): void
    {
        $service = new CheckoutTaxService();

        $this->assertSame(0.0, $service->calculate(100, 'PERCENT', -10));
        $this->assertSame(0.0, $service->calculate(100, 'UNKNOWN', 20));
    }
}
