<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Stripe\Stripe;
use Stripe\Webhook;
use Stripe\PaymentIntent;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->server('HTTP_STRIPE_SIGNATURE');
        //$endpointSecret = config('services.stripe.webhook_secret');
        $endpointSecret = env('STRIPE_SECRET');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }

        if ($event->type === 'payment_intent.succeeded') {
            $paymentIntent = $event->data->object;
            $orderId = $paymentIntent->metadata->order_id;
            $orderNum = $paymentIntent->metadata->order_number;

            // Update your order status in DB
            $order = Order::where('id', $orderId)->where('order_number', $orderNum)->first();
            if ($order) {
                $order->payment_status  = 1;
                $order->status          = 1;
                $order->transaction_id  = $paymentIntent->id;
                $order->payment_id      = $paymentIntent->id;
                $order->save();
            }
        }

        return response()->json(['status' => 'success']);
    }

    public function createOrUpdate(Request $request)
    {
        try {
            \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

            $amount     = floatval($request->amount) * 100; // cents
            $currency   = $request->currency ?? 'USD';
            $order_id   = $request->order_id;
            $order_num  = $request->order_number;
            $description= $request->description;

            // Get or create the latest order/cart (you can adjust logic here)
            $order = Order::find($order_id);

            if (!$order) {
                return response()->json([
                    'error' => 'Invalid order ID'
                ], 404);
            }

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
                            'description' => $description,
                            'automatic_payment_methods' => ['enabled' => true],
                            'metadata' => [
                                'order_id'  => $order_id,
                                'order_num' => $order_num // This lets webhook identify the order
                            ]
                        ]);
                        $order->payment_intent_id = $paymentIntent->id;
                        $order->save();
                    }
                } catch (\Exception $e) {
                    // If retrieval fails, create new
                    $paymentIntent = \Stripe\PaymentIntent::create([
                        'amount' => $amount,
                        'currency' => $currency,
                        'description' => $description,
                        'automatic_payment_methods' => ['enabled' => true],
                        'metadata' => [
                            'order_id'  => $order_id,
                            'order_num' => $order_num // This lets webhook identify the order
                        ]
                    ]);
                    $order->payment_intent_id = $paymentIntent->id;
                    $order->save();
                }
            } else {
                // Create new if no ID present
                $paymentIntent = \Stripe\PaymentIntent::create([
                    'amount' => $amount,
                    'currency' => $currency,
                    'description' => $description,
                    'automatic_payment_methods' => ['enabled' => true],
                    'metadata' => [
                        'order_id'  => $order_id,
                        'order_num' => $order_num // This lets webhook identify the order
                    ]
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

    public function verifyPayment(Request $request)
    {
        $request->validate([
            'client_secret' => 'required|string',
        ]);

        //config('services.stripe.secret')
        Stripe::setApiKey(env('STRIPE_SECRET'));

        try {
            // Extract the payment intent ID from client_secret
            $clientSecret = $request->client_secret;
            $intentId = explode('_secret_', $clientSecret)[0];

            // Retrieve payment intent
            $paymentIntent = PaymentIntent::retrieve($intentId);

            if ($paymentIntent->status === 'succeeded') {
                // Update booking in DB if not already updated
                $booking = Order::where('payment_intent_id', $paymentIntent->id)->first();

                if ($booking && $booking->status !== 1) {
                    $booking->order_status   = 1;
                    $booking->payment_status = 1;
                    $booking->payment_method = $paymentIntent->payment_method_types[0] ?? 'card';
                    $booking->updated_at     = now();
                    $booking->save();
                }

                return response()->json([
                    'status' => 'succeeded',
                    'booking' => $booking,
                ]);
            }

            return response()->json([
                'status' => 'failed',
                'message' => 'Payment not successful',
            ], 400);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
