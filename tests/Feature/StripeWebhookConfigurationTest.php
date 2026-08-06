<?php

namespace Tests\Feature;

use Tests\TestCase;

class StripeWebhookConfigurationTest extends TestCase
{
    public function test_webhook_fails_safely_when_signing_secret_is_missing(): void
    {
        $originalSecret = env('STRIPE_WEBHOOK_SECRET');
        putenv('STRIPE_WEBHOOK_SECRET');
        unset($_ENV['STRIPE_WEBHOOK_SECRET'], $_SERVER['STRIPE_WEBHOOK_SECRET']);

        try {
            $this->postJson('/api/stripe/webhook', [])
                ->assertStatus(503)
                ->assertExactJson([
                    'error' => 'Stripe webhook is not configured',
                ]);
        } finally {
            if ($originalSecret !== null && $originalSecret !== false) {
                putenv('STRIPE_WEBHOOK_SECRET='.$originalSecret);
                $_ENV['STRIPE_WEBHOOK_SECRET'] = $originalSecret;
                $_SERVER['STRIPE_WEBHOOK_SECRET'] = $originalSecret;
            }
        }
    }
}
