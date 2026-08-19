<?php

namespace Tests\Unit;

use App\Http\Controllers\API\PaymentController;
use App\Http\Controllers\OrderController as AdminOrderController;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class StripeCurrencyAmountTest extends TestCase
{
    public function test_jpy_uses_zero_decimal_stripe_amounts_in_both_directions(): void
    {
        $controller = new PaymentController();
        $toStripe = new ReflectionMethod($controller, 'stripeAmount');
        $fromStripe = new ReflectionMethod($controller, 'fromStripeAmount');
        $normalize = new ReflectionMethod($controller, 'normalizeCurrencyAmount');

        $this->assertSame(8021, $toStripe->invoke($controller, 8021.35, 'JPY'));
        $this->assertSame(8021.0, $fromStripe->invoke($controller, 8021, 'jpy'));
        $this->assertSame(0.0, $normalize->invoke($controller, 0.31, 'JPY'));
        $this->assertSame(4456.0, $normalize->invoke($controller, 4456.31, 'JPY'));
    }

    public function test_cad_still_uses_two_decimal_stripe_amounts(): void
    {
        $controller = new PaymentController();
        $toStripe = new ReflectionMethod($controller, 'stripeAmount');
        $fromStripe = new ReflectionMethod($controller, 'fromStripeAmount');
        $normalize = new ReflectionMethod($controller, 'normalizeCurrencyAmount');

        $this->assertSame(802135, $toStripe->invoke($controller, 8021.35, 'CAD'));
        $this->assertSame(8021.35, $fromStripe->invoke($controller, 802135, 'cad'));
        $this->assertSame(0.31, $normalize->invoke($controller, 0.31, 'CAD'));
    }

    public function test_admin_payment_flows_use_currency_aware_amounts(): void
    {
        $controller = new AdminOrderController();
        $toStripe = new ReflectionMethod($controller, 'stripeAmount');
        $fromStripe = new ReflectionMethod($controller, 'fromStripeAmount');

        $this->assertSame(7999, $toStripe->invoke($controller, 79.99, 'CAD'));
        $this->assertSame(7999, $toStripe->invoke($controller, 7999, 'JPY'));
        $this->assertSame(79.99, $fromStripe->invoke($controller, 7999, 'CAD'));
        $this->assertSame(7999.0, $fromStripe->invoke($controller, 7999, 'JPY'));
    }
}
