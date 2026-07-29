<?php

namespace Tests\Unit;

use App\Services\StripeIdempotencyService;
use PHPUnit\Framework\TestCase;

class StripeIdempotencyServiceTest extends TestCase
{
    public function test_same_attempt_produces_same_key(): void
    {
        $service = new StripeIdempotencyService();

        $first = $service->checkoutIntentKey(10, 'payment', 'full', 125.5, 'CAD', 2);
        $retry = $service->checkoutIntentKey(10, 'payment', 'full', 125.5, 'CAD', 2);

        $this->assertSame($first, $retry);
        $this->assertLessThanOrEqual(255, strlen($first));
    }

    public function test_new_attempt_or_amount_produces_a_different_key(): void
    {
        $service = new StripeIdempotencyService();

        $base = $service->checkoutIntentKey(10, 'payment', 'full', 100, 'CAD', 1);

        $this->assertNotSame(
            $base,
            $service->checkoutIntentKey(10, 'payment', 'full', 100, 'CAD', 2)
        );
        $this->assertNotSame(
            $base,
            $service->checkoutIntentKey(10, 'payment', 'full', 101, 'CAD', 1)
        );
    }
}
