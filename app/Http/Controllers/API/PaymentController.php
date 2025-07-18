<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function createOrUpdate(Request $request)
    {
        try {
            \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

            $amount     = $request->amount ?? 1000; // cents
            $currency   = $request->currency;
            $order_id   = $request->order_id;

            // Get or create the latest order/cart (you can adjust logic here)
            $order = Order::find($order_id);

            if ($order && $order->payment_intent_id) {
                try {
                    $paymentIntent = \Stripe\PaymentIntent::retrieve($order->payment_intent_id);

                    if ($paymentIntent->status === 'requires_payment_method') {
                        // Update amount if needed
                        if ($paymentIntent->amount !== $amount) {
                            $paymentIntent->amount = $amount;
                            $paymentIntent->save();
                        }
                    } else {
                        // Create new PaymentIntent if status is not reusable
                        $paymentIntent = \Stripe\PaymentIntent::create([
                            'amount' => $amount,
                            'currency' => $currency,
                            'automatic_payment_methods' => ['enabled' => true],
                        ]);
                        $order->payment_intent_id = $paymentIntent->id;
                        $order->save();
                    }
                } catch (\Exception $e) {
                    // If retrieval fails, create new
                    $paymentIntent = \Stripe\PaymentIntent::create([
                        'amount' => $amount,
                        'currency' => $currency,
                        'automatic_payment_methods' => ['enabled' => true],
                    ]);
                    $order->payment_intent_id = $paymentIntent->id;
                    $order->save();
                }
            } else {
                // Create new if no ID present
                $paymentIntent = \Stripe\PaymentIntent::create([
                    'amount' => $amount,
                    'currency' => $currency,
                    'automatic_payment_methods' => ['enabled' => true],
                ]);
                $order->payment_intent_id = $paymentIntent->id;
                $order->save();
            }

            return response()->json([
                'clientSecret' => $paymentIntent->client_secret,
            ]);

        } catch (\Exception $e) {
            Log::error('Stripe PaymentIntent error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Unable to process payment',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

}
