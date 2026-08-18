<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Mail\AdminBookingMail;
use App\Mail\EmailManager;
use App\Models\Addon;
use App\Models\EmailTemplate;
use App\Models\Order;
use App\Models\OrderActions;
use App\Models\OrderEmailHistory;
use App\Models\OrderPayment;
use App\Models\Pickup;
use App\Models\PickupLocation;
use App\Models\Promo;
use App\Models\StripeWebhookLog;
use App\Models\StripeWebhookReceipt;
use App\Models\TourPricing;
use App\Models\User;
use App\Notifications\NewOrderNotification;
use App\Services\OrderPaymentSummaryService;
use App\Services\CheckoutTotalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Stripe\PaymentIntent;
use Stripe\SetupIntent;
use Stripe\Stripe;
use Stripe\Webhook;
use Stripe\PaymentMethod;
use Stripe\Exception\SignatureVerificationException;

class PaymentController extends Controller
{
    public function handleWebhook(Request $request)
    {
        Log::info('handleWebhook');

        $payload    = $request->getContent();
        $sigHeader  = $request->header('Stripe-Signature');
        $secret     = env('STRIPE_WEBHOOK_SECRET');

        if (blank($secret)) {
            Log::critical('Stripe webhook rejected because STRIPE_WEBHOOK_SECRET is not configured.');

            return response()->json([
                'error' => 'Stripe webhook is not configured',
            ], 503);
        }

        orderLogAdvanced(null, 'webhook', 'entry', 'info', 'Webhook received', [
            'payload' => $payload
        ]);

        $logData = [
            'event_id' => null,
            'event_type' => null,
            'payment_intent_id' => null,
            'payload' => $payload,
            'status' => 'received',
            'message' => null
        ];
        $order = null;
        $orderId = null;
        $receipt = null;

        try {
            $event = Webhook::constructEvent(
                $payload,
                $sigHeader,
                $secret
            );

            $receipt = StripeWebhookReceipt::firstOrCreate(
                ['event_id' => $event->id],
                [
                    'event_type' => $event->type,
                    'status' => 'processing',
                ]
            );
            $processingIsFresh = $receipt->status === 'processing'
                && $receipt->updated_at
                && $receipt->updated_at->gt(now()->subMinutes(5));
            if (!$receipt->wasRecentlyCreated && ($receipt->status === 'processed' || $processingIsFresh)) {
                return response()->json([
                    'status' => 'duplicate',
                    'event_id' => $event->id,
                ]);
            }
            if (!$receipt->wasRecentlyCreated) {
                $receipt->update([
                    'event_type' => $event->type,
                    'status' => 'processing',
                    'processed_at' => null,
                ]);
            }

            $eventObject = $event->data->object;
            Stripe::setApiKey(env('STRIPE_SECRET'));

            orderLogAdvanced(null, 'webhook', 'signature_verified', 'success', 'Signature verified', [
                'event_id' => $event->id ?? null,
                'type' => $event->type ?? null
            ]);

            // ✅ Metadata
            if ($event->type === 'charge.refunded') {
                $paymentIntentId = $eventObject->payment_intent ?? null;
                if ($paymentIntentId) {
                    $paymentIntent = PaymentIntent::retrieve(
                        $paymentIntentId
                    );
                    $orderId  = $paymentIntent->metadata->orderId ?? null;
                    $orderNum = $paymentIntent->metadata->orderNumber ?? null;
                }
            } else {
                $orderId  = $eventObject->metadata->orderId ?? null;
                $orderNum = $eventObject->metadata->orderNumber ?? null;
            }

            $logData['event_id'] = $event->id;
            $logData['event_type'] = $event->type;
            $logData['payment_intent_id'] = $eventObject->id ?? null;

            if (!$orderId || !$orderNum) {

                orderLogAdvanced(null, 'webhook', 'metadata_invalid', 'failed', 'Invalid metadata', [
                    'order_id' => $orderId,
                    'order_number' => $orderNum
                ]);

                $logData['status'] = 'failed';
                $logData['message'] = "Invalid metadata for order ID: $orderId, Order Number: $orderNum";
                StripeWebhookLog::create($logData);

                return response()->json(['error' => "Invalid metadata for order ID: $orderId, Order Number: $orderNum"], 400);
            }

            // Find Order
            $order = Order::where('id', $orderId)
                ->where('order_number', $orderNum)
                ->first();

            if (!$order) {

                orderLogAdvanced(null, 'webhook', 'order_not_found', 'failed', 'Order not found', [
                    'order_id' => $orderId
                ]);

                $logData['status'] = 'failed';
                $logData['message'] = 'Order not found';
                $logData['order_id']= $orderId;
                StripeWebhookLog::create($logData);

                return response()->json(['error' => 'Order not found'], 404);
            }

            orderLogAdvanced($order, 'payment', 'webhook_event', 'info', 'Webhook event received', [
                'event_type' => $event->type,
                'payment_intent_id' => $eventObject->id ?? null
            ]);

            // Handle events
            switch ($event->type) {
                case 'charge.refunded':

                    $order->failure_message = 'Payment refunded';
                    $this->syncRefundFromWebhook($eventObject, $order);
                    $logData['status']  = 'refunded';
                    $logData['message'] = 'Payment refunded';
                    break;

                case 'payment_intent.created':

                    $logData['status'] = 'created';
                    $logData['message'] = 'Payment created';
                    break;

                case 'payment_intent.succeeded':

                    $this->saveCardDetails( $eventObject );
                    $this->syncPaymentStatusFromWebhook($eventObject);
                    $this->redeemPromoOnce($order);
                    $this->recordInternalPaymentAction($order, $eventObject, 'succeeded');

                    $logData['status'] = 'success';
                    $logData['message'] = 'Payment successful';
                    break;

                case 'payment_intent.payment_failed':

                    $order->failure_message = $eventObject->last_payment_error->message ?? 'Payment failed';
                    $this->recordInternalPaymentAction(
                        $order,
                        $eventObject,
                        'failed',
                        $order->failure_message
                    );

                    $logData['status']  = 'failed';
                    $logData['message'] = $order->failure_message;
                    break;

                case 'payment_intent.canceled':

                    $logData['status']  = 'cancelled';
                    $logData['message'] = 'Payment cancelled';
                    break;

                case 'payment_intent.requires_action':

                    $logData['status']  = 'requires_action';
                    $logData['message'] = 'Payment requires additional action';
                    break;  

                case 'payment_intent.processing':

                    $logData['status']  = 'pending';
                    $logData['message'] = 'Payment pending';
                    break;  
                    
                case 'payment_intent.amount_capturable_updated':

                    $this->saveCardDetails( $eventObject );
                    $this->syncPaymentStatusFromWebhook($eventObject);
                    $this->recordInternalPaymentAction($order, $eventObject, 'authorized');

                    $logData['status'] = 'authorized';
                    $logData['message'] = 'Payment authorized, awaiting capture';


                    
                    break;                      

                default:
                    $logData['status']  = 'ignored';
                    $logData['message'] = 'Unhandled event: ' . $event->type;
                    $logData['order_id']= $order->id;

                    StripeWebhookLog::create($logData);
                    $receipt->update(['status' => 'processed', 'processed_at' => now()]);

                    return response()->json(['status' => 'ignored']);
            }

            $order->transaction_id = $eventObject->id;
            $order->save();

            orderLogAdvanced($order, 'payment', 'webhook_processed', 'success', 'Webhook processed', [
                'event_type' => $event->type
            ]);

            $logData['order_id'] = $order->id;
            StripeWebhookLog::create($logData);
            $receipt->update(['status' => 'processed', 'processed_at' => now()]);

            return response()->json(['status' => 'success']);

        } catch (SignatureVerificationException $e) {

            orderLogAdvanced(null, 'webhook', 'signature_failed', 'failed', 'Invalid signature');

            $logData['status'] = 'failed';
            $logData['message'] = 'Invalid signature';
            $logData['order_id'] = $order->id ?? $orderId;

            StripeWebhookLog::create($logData);
            $receipt?->update(['status' => 'failed']);

            return response()->json(['error' => 'Invalid signature'], 400);

        } catch (\Exception $e) {

            orderLogAdvanced(null, 'webhook', 'exception', 'error', $e->getMessage());

            $logData['status'] = 'error';
            $logData['message'] = $e->getMessage();
            $logData['order_id'] = $order->id ?? $orderId;

            StripeWebhookLog::create($logData);
            $receipt?->update(['status' => 'failed']);

            return response()->json(['error' => 'Server error'], 500);
        }
    }

    private function recordInternalPaymentAction(
        Order $order,
        mixed $intent,
        string $result,
        ?string $failureReason = null
    ): void {
        if (strtolower((string) $order->source) !== 'internal') {
            return;
        }

        $customer = $order->customer;
        $customerName = $customer?->name ?: 'Customer';
        $currency = strtoupper((string) ($intent->currency ?? $order->currency ?? ''));
        $stripeAmount = $result === 'succeeded'
            ? ($intent->amount_received ?? $intent->amount ?? 0)
            : ($intent->amount ?? 0);
        $amount = number_format(
            $this->fromStripeAmount((float) $stripeAmount, $currency),
            $this->isZeroDecimalCurrency($currency) ? 0 : 2,
            '.',
            ''
        );

        $notes = match ($result) {
            'authorized' => "{$customerName} paid {$currency} {$amount} for order {$order->order_number} (payment authorized)",
            'succeeded' => "{$customerName} payment of {$currency} {$amount} was successful for order {$order->order_number}",
            'failed' => "{$customerName} payment of {$currency} {$amount} failed for order {$order->order_number}: "
                . ($failureReason ?: 'Payment failed'),
            default => null,
        };

        if (!$notes) {
            return;
        }

        OrderActions::create([
            'order_id' => $order->id,
            'performed_by' => $customer?->id,
            'notes' => $notes,
        ]);
    }

    public function createSetupIntent($action = 'paynow')
    {
        Log::info('createSetupIntent');

        try {
            Stripe::setApiKey(env('STRIPE_SECRET'));

            $params = [];
            if ($action === 'paynow' ) { 
                $params['automatic_payment_methods'] = ['enabled' => true];
            }
            else {
                $params['payment_method_types'] = ['card'];
            }

            $setupIntent = SetupIntent::create($params);

            return response()->json([
                'clientSecret' => $setupIntent->client_secret,
                'action' => $action
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Unable to create SetupIntent',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function createOrUpdate(Request $request)
    {
        Log::info('createOrUpdate');

        try {
            Stripe::setApiKey(env('STRIPE_SECRET'));

            if ($request->action !== 'paynow') {
                $order = Order::withoutGlobalScopes()->find($request->order_id);
                if (!$order) {
                    return response()->json(['error' => 'Invalid order ID'], 404);
                }

                $params = [
                    'automatic_payment_methods' => ['enabled' => true],
                    'usage' => 'off_session',
                    'metadata' => [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                    ],
                ];
                if ($order->stripe_customer_id) {
                    $params['customer'] = $order->stripe_customer_id;
                }

                $setupIntent = SetupIntent::create($params);
                $order->payment_intent_id = $setupIntent->id;
                $order->payment_intent_client_secret = $setupIntent->client_secret;
                $order->save();

                return response()->json([
                    'clientSecret' => $setupIntent->client_secret,
                    'setupIntentId' => $setupIntent->id,
                ]);
            }

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
            $paymentSummary = (new OrderPaymentSummaryService())->summarize(
                $order->payments()->get()
            );
            $grossAmount = round((float) $order->orderTours()->sum('total_amount'), 2);
            $payableAmount = $this->normalizeCurrencyAmount(
                max($grossAmount - $paymentSummary['total_credits'], 0),
                (string) $order->currency
            );
            if ($payableAmount <= 0.01) {
                return response()->json([
                    'error' => 'Order is already fully paid',
                ], 422);
            }
            $amount = $this->stripeAmount($payableAmount, $order->currency);
            $currency = strtolower((string) ($order->currency ?: 'CAD'));

            $params = [
                'amount' => $amount,
                'currency' => $currency,
                'description' => $description,
                'statement_descriptor_suffix' => $order_num,
                'metadata' => [
                    'order_id'  => $order_id,
                    'order_num' => $order_num
                ]
            ];

            if ($request->action === 'paynow' ) { 
                $params['automatic_payment_methods'] = ['enabled' => true];
                $params['capture_method'] = 'manual';
                $params['setup_future_usage'] = 'off_session';
            }
            else {
                $params['payment_method_types'] = ['card'];
            }

            if ($order && $order->payment_intent_id) {
                try {
                    $paymentIntent = PaymentIntent::retrieve($order->payment_intent_id);

                    if ($paymentIntent->status === 'requires_payment_method') {
                        // Update amount if needed
                        if ($paymentIntent->amount !== $amount) {
                            $paymentIntent = PaymentIntent::update(
                                $paymentIntent->id,
                                ['amount' => $amount]
                            );
                        }
                    } else {
                        // Create new PaymentIntent if status is not reusable
                        $paymentIntent = PaymentIntent::create($params);
                        $order->payment_intent_client_secret = $paymentIntent->client_secret;
                        $order->payment_intent_id = $paymentIntent->id;
                        $order->save();
                    }
                } catch (\Exception $e) {
                    // If retrieval fails, create new
                    $paymentIntent = PaymentIntent::create($params);
                    $order->payment_intent_client_secret = $paymentIntent->client_secret;
                    $order->payment_intent_id = $paymentIntent->id;
                    $order->save();
                }
            } else {
                // Create new if no ID present
                $paymentIntent = PaymentIntent::create($params);                
                $order->payment_intent_client_secret = $paymentIntent->client_secret;
                $order->payment_intent_id = $paymentIntent->id;
                $order->save();
            }

            return response()->json([
                'clientSecret' => $paymentIntent->client_secret,
                'paymentIntentId' => $paymentIntent->id,
                'amount' => $payableAmount,
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
        Log::info('verifyPayment');

        $request->validate([
            'client_secret' => 'required|string',
        ]);

        Stripe::setApiKey(env('STRIPE_SECRET'));

        try {
            $clientSecret = $request->client_secret;
            $action_name = $request->action_name;
            $intentId = explode('_secret_', $clientSecret)[0];

            $payment_status = 3;
            $balance_amount = 0;
            $payment_method = null;
            $order_status   = 3;
            $booked_amount  = 0;
            $balance_amount = 0;

            $flag_payment_status = 0;

            // ================= CARD DETAILS (NO CAPTURE, NO WEBHOOK) =================
            try {

                if ($action_name === "book") {
                    $paymentIntent = PaymentIntent::retrieve($intentId);

                    $booking = Order::with([
                            'tour',
                            'tour.location',
                            'tour.detail',
                            'customer'
                        ])
                        ->where('payment_intent_id', $paymentIntent->id)
                        ->first();

                    if($booking->order_status === 6) { // For cancellation case, if user try to pay again with same PI, then show order cancelled message instead of order confirmed message
                        return response()->json(data: [
                            'status'  => 'cancelled',
                            'message' => 'Your order was already cancelled! Please chceck your email for order details.',
                            'booking' => [],
                        ]); 
                    }

                    if($paymentIntent->status === "requires_capture"){
                        $payment_status = 3;
                    }
                    else if ($paymentIntent->status === 'succeeded') {
                        $payment_status = 1;
                    }
                    else {
                        $payment_status = 0;
                    }
                    
                    \Log::warning($paymentIntent->status);

                    $payment_method = $paymentIntent->payment_method_types[0] ?? 'card';
                    if($paymentIntent->last_payment_error?->payment_method?->type) {
                        $payment_method = $paymentIntent->last_payment_error->payment_method->type;
                    }                    

                    $total_amount   = $booking->total_amount;
                    if($paymentIntent->status === 'succeeded' || $paymentIntent->status === 'requires_capture') {
                        $balance_amount = $booking->balance_amount;
                        $booked_amount = $booking->booked_amount;
                        $flag_payment_status = 1;
                    }
                    else {
                        $balance_amount = $total_amount;
                        $booked_amount  = 0;
                        $order_status   = 1;
                    }


                } else {
                    // Reserve flow → Retrieve SetupIntent
                    $setupIntent = \Stripe\SetupIntent::retrieve($intentId);

                    $payment_status = 3; // Not paid yet
                    $payment_method = ''; // future use
                    $flag_payment_status = 1;

                    $booking = Order::with([
                                    'tour',
                                    'tour.location',
                                    'tour.detail',
                                    'customer'
                                ])->where('payment_intent_id', $setupIntent->id)->first();

                    $total_amount   = $booking->total_amount;
                    $balance_amount = $total_amount;
                    $booked_amount  = 0;
                }
                if($booking->order_status === 5 || $booking->order_status === 6) { // For confirmed case, if user try to pay again with same PI, then show order confirmed message instead of order confirmed message.
                    $order_status = $booking->order_status;
                }

                if (isset($paymentIntent) && !empty($paymentIntent->payment_method)) {

                    $paymentMethod = PaymentMethod::retrieve(
                        $paymentIntent->payment_method
                    );

                    OrderPayment::updateOrCreate(
                        [
                            'payment_intent_id' => $paymentIntent->id,
                        ],
                        [
                            'order_id'          => $booking->id,
                            'payment_intent_id' => $paymentIntent->id,
                            'transaction_id'    => $paymentIntent->latest_charge ?? null,
                            'payment_type'      => strtoupper($paymentMethod->type),
                            'payment_method'    => $paymentMethod->type,
                            'amount'            => $this->fromStripeAmount(
                                (float) ($paymentIntent->amount_received ?: $paymentIntent->amount),
                                (string) ($paymentIntent->currency ?: $booking->currency)
                            ),
                            'currency'          => strtoupper((string) ($paymentIntent->currency ?: $booking->currency)),
                            'card_brand'        => $paymentMethod->card->brand ?? null,
                            'card_last4'        => $paymentMethod->card->last4 ?? null,
                            'card_exp_month'    => $paymentMethod->card->exp_month ?? null,
                            'card_exp_year'     => $paymentMethod->card->exp_year ?? null,
                            'status'            => $paymentIntent->status === 'requires_capture' ? 'uncaptured' : $paymentIntent->status,
                            'action'            => $action_name,
                            'response_payload'  => json_encode($paymentIntent),
                            'collection_date'   => now(),
                        ]
                    );
                }

                if (isset($paymentIntent) && in_array($paymentIntent->status, ['requires_capture', 'succeeded'], true)) {
                    $summary = (new OrderPaymentSummaryService())->summarize(
                        $booking->payments()->get()
                    );
                    $grossAmount = round((float) $booking->orderTours()->sum('total_amount'), 2);
                    $balance_amount = $this->normalizeCurrencyAmount(
                        max($grossAmount - $summary['total_credits'], 0),
                        (string) $booking->currency
                    );
                    $booked_amount = $summary['paid_amount'];
                    $payment_status = $paymentIntent->status === 'requires_capture'
                        ? 3
                        : ($balance_amount <= 0.01 ? 1 : 0);
                }
            } catch (\Exception $e) {
                \Log::warning(
                    'Card details not saved for PI ' . ($paymentIntent->id ?? 'N/A') . ' : ' . $e->getMessage()
                );
            }
            // ========================================================================
            
            // Update booking
            $booking->total_amount   = $total_amount;
            $booking->balance_amount = $balance_amount;
            $booking->booked_amount  = $booked_amount;
            $booking->order_status   = $order_status;
            $booking->payment_status = $payment_status;
            $booking->payment_method = $payment_method;
            $booking->updated_at     = now();
            $booking->save();

            if (!$booking) {
                return response()->json([
                    'status'  => 'failed',
                    'message' => 'Order not found!',
                ], 400);
            }

            if($flag_payment_status === 0 ) {
                return response()->json(data: [
                    'status'  => 'failed',
                    'message' => 'Your previous payment was failed!',
                    'booking' => [],
                ]); 
            }
            
            $tour_pricing = json_decode($booking->order_tour->tour_pricing);
            $pricing=[]; $total = 0;
            if(!empty($tour_pricing) && is_array($tour_pricing)) {
                foreach($tour_pricing as $tp) {
                    $tourPricing = TourPricing::find($tp->tour_pricing_id);
                    $label = str_ireplace('Group', 'Participants', $tourPricing->label);
                    //$total = ($tp->quantity * $tp->price);
                    $pricing[] = [
                        'lable'         => $label,
                        'price_type'    => $tp->price_type,
                        'qty'           => $tp->quantity,
                        'price'         => $tp->price,
                        'actual_price'  => isset($tp->actual_price) ? $tp->actual_price : $tp->price,
                        'discount'      => isset($tp->discount) ? $tp->discount : 0,
                        'total'         => $tp->total_price,
                        'gross_total_price' => (float) ($tp->gross_total_price ?? $tp->total_price),
                        'newly_added_quantity' => (int) ($tp->newly_added_quantity ?? 0),
                        'newly_added_price' => (float) ($tp->newly_added_price ?? $tp->actual_price ?? $tp->price),
                        'is_newly_added' => (bool) ($tp->is_newly_added ?? false),
                    ];
                }
            }

            $extra_pricing = json_decode($booking->order_tour->tour_extra);
            $extra=[]; $total = 0;
            if(!empty($extra_pricing) && is_array($extra_pricing)) {
                foreach($extra_pricing as $ep) {
                    $extraAddon = Addon::find($ep->tour_extra_id);
                    //$total = ($ep->quantity * $ep->price);
                    $extra[] = [
                        'lable' => $extraAddon->name,
                        'qty'   => $ep->quantity,
                        'price' => $ep->price,
                        'total' => $ep->total_price,
                        'gross_total_price' => (float) ($ep->gross_total_price ?? $ep->total_price),
                        'newly_added_quantity' => (int) ($ep->newly_added_quantity ?? 0),
                        'newly_added_price' => (float) ($ep->newly_added_price ?? $ep->price),
                        'is_newly_added' => (bool) ($ep->is_newly_added ?? false),
                    ];
                }
            }

            $fees_pricing = json_decode($booking->order_tour->tour_fees);
            $fees = [];
            if (!empty($fees_pricing) && is_array($fees_pricing)) {
                foreach ($fees_pricing as $fp) {
                    $labelText = isset($fp->type) && $fp->type == 'PERCENT' ? ' (' . $fp->value . '%)' : ' (' . $fp->value . ')';
                    $labelText = $fp->label . $labelText;

                    $fees[] = [
                        'lable' => $labelText, // fixed spelling
                        'price' => $fp->price,
                        'total' => $fp->price,
                        'newly_added_amount' => round((float) ($fp->newly_added_amount ?? 0), 2),
                    ];
                }
            }

            // [{"tour_id":24,"discount":10,"label":"Discount","type":"FIXED","price":20}]
            $discount = json_decode($booking->order_tour->discount);
            $discounts = [];
            if (!empty($discount) && is_array($discount)) {
                foreach ($discount as $dp) {
                    $discounts[] = [
                        'lable' => $dp->label,
                        'type'  => $dp->type,
                        'price' => $dp->price,
                        'total' => $dp->price
                    ];
                }
            }

            \Log::warning("A====================================");


            $image = uploaded_asset($booking->tour?->main_image->id ?? 0, 'medium');
            $pickName = '';
            if($booking->customer && $booking->customer->pickup_name){
                $pickName = $booking->customer->pickup_name;
            } elseif($booking->customer && $booking->customer->pickup_id) {
                $pickLocation = PickupLocation::find($booking->customer->pickup_id);
                $pickName = $pickLocation?->location . " - " . $pickLocation?->address . " - " . $pickLocation?->time;
            }

            \Log::warning("B====================================");

            /* If already partially paid or added discount/promo etc in backend */
            $paymentSummary = (new OrderPaymentSummaryService())->summarize(
                $booking->payments()->get()
            );
            $paidAmount = $paymentSummary['paid_amount'];
            $ledgerPaymentAmount = round(
                $paymentSummary['paid_amount'] + $paymentSummary['authorized_amount'],
                2
            );
            $promoCode = $paymentSummary['promo_discount'];
            $currentPaymentAmount = 0.0;
            $currentPaymentIsInLedger = false;
            if (isset($paymentIntent) && in_array(
                $paymentIntent->status,
                ['succeeded', 'requires_capture'],
                true
            )) {
                $currentPaymentRow = $booking->payments()
                    ->where('payment_intent_id', $paymentIntent->id)
                    ->whereIn('status', ['succeeded', 'uncaptured', 'requires_capture'])
                    ->first();

                if ($currentPaymentRow && !in_array(
                    strtoupper((string) $currentPaymentRow->payment_type),
                    ['BOOKINGFEE', 'DISCOUNT', 'PROMOCODE', 'REFUND'],
                    true
                )) {
                    $currentPaymentAmount = round(max(
                        (float) $currentPaymentRow->amount - (float) $currentPaymentRow->refund_amount,
                        0
                    ), 2);
                    $currentPaymentIsInLedger = true;
                } else {
                    // Stripe has confirmed the payment, but the webhook/ledger
                    // write may still be completing. Use the verified intent for
                    // the success-screen figures in the meantime.
                    $currentPaymentAmount = max($this->fromStripeAmount(
                        (float) ($paymentIntent->amount_received ?: $paymentIntent->amount),
                        (string) ($paymentIntent->currency ?: $booking->currency)
                    ), 0);
                }
            }
            $previouslyPaid = round(max(
                $ledgerPaymentAmount - ($currentPaymentIsInLedger ? $currentPaymentAmount : 0),
                0
            ), 2);
            $totalPaymentAmount = round(
                $previouslyPaid + $currentPaymentAmount,
                2
            );
            $hasNewlyAddedLines = collect($pricing)->contains(
                    fn ($item) => (int) ($item['newly_added_quantity'] ?? 0) > 0
                )
                || collect($extra)->contains(
                    fn ($item) => (int) ($item['newly_added_quantity'] ?? 0) > 0
                )
                || collect($fees)->contains(
                    fn ($item) => (float) ($item['newly_added_amount'] ?? 0) > 0
                );
            $isAdditionalPayment = $hasNewlyAddedLines && $currentPaymentAmount > 0;
            $currentPayment = ($isAdditionalPayment || $previouslyPaid > 0.01)
                ? $currentPaymentAmount
                : 0.0;
            $totalPaid = round(
                $totalPaymentAmount
                + $paymentSummary['special_discount']
                + $paymentSummary['promo_discount'],
                2
            );
            $grossAmount = round((float) $booking->orderTours()->sum('total_amount'), 2);
            $balanceAmount = $this->normalizeCurrencyAmount(
                max($grossAmount - $totalPaid, 0),
                (string) $booking->currency
            );
            $paymentHistory = $booking->payments()
                ->orderBy('collection_date')
                ->orderBy('created_at')
                ->get()
                ->filter(function ($payment) {
                    $status = strtolower((string) $payment->status);

                    return in_array($status, [
                        'succeeded',
                        'uncaptured',
                        'requires_capture',
                        'discount',
                        'partial_refunded',
                        'refunded',
                    ], true);
                })
                ->map(function ($payment) {
                    $type = strtoupper((string) $payment->payment_type);
                    $labels = [
                        'PROMOCODE' => 'Promo Code',
                        'DISCOUNT' => 'Special Discount',
                        'CASH' => 'Cash',
                        'CREDITCARD' => 'Credit Card',
                        'CARD' => 'Card',
                        'BOOKINGFEE' => 'Booking Fee',
                        'REFUND' => 'Refund',
                    ];
                    $isDiscount = in_array($type, ['PROMOCODE', 'DISCOUNT'], true);
                    $occurredAt = $payment->collection_date ?: $payment->created_at;

                    return [
                        'id' => $payment->id,
                        'type' => $type,
                        'label' => $labels[$type] ?? ucfirst(strtolower((string) $payment->payment_method ?: $type)),
                        'status' => $payment->status,
                        'amount' => round((float) $payment->amount, 2),
                        'currency' => $payment->currency,
                        'reference' => $payment->transaction_id ?: $payment->payment_intent_id,
                        'payment_method' => $payment->payment_method,
                        'card_brand' => $payment->card_brand,
                        'card_last4' => $payment->card_last4,
                        'collection_type' => $payment->collection_type,
                        'occurred_at' => $occurredAt
                            ? date(DATE_ATOM, strtotime((string) $occurredAt))
                            : null,
                        'is_discount' => $isDiscount,
                        'is_refund' => $type === 'REFUND',
                    ];
                })
                ->values();

            $detail = [
                'id'                => $booking->order_number,
                'action_name'       => $booking->action_name,
                'order_number'      => $booking->order_number,
                'number_of_guests'  => $booking->number_of_guests,
                'paid_amount'       => $paidAmount ?? 0,
                'total_payment_amount' => $totalPaymentAmount,
                'previously_paid'   => $previouslyPaid,
                'current_payment'   => $currentPayment,
                'is_additional_payment' => $isAdditionalPayment,
                'promo_code'        => $promoCode ?? 0,
                'promo_code_value'  => $paymentSummary['promo_code'],
                'total_paid'        => $totalPaid ?? 0,
                'total_amount'      => $grossAmount,
                'gross_amount'      => $grossAmount,
                'balance_amount'    => $balanceAmount,
                'payment_summary'   => [
                    ...$paymentSummary,
                    'gross_amount' => $grossAmount,
                    'balance_amount' => $balanceAmount,
                ],
                'payment_history'   => $paymentHistory,
                'currency'          => $booking->currency,
                'payment_method'    => ucfirst($booking->payment_method),
                'customer'          => $booking->customer,
                'pickup'            => $pickName,
                'tour_date'         => date('D, M d, Y', strtotime($booking->order_tour->tour_date)),
                'tour_time'         => $booking->order_tour->tour_time,
                'created_at'        => date('Y-m-d', strtotime($booking->created_at)),
                'tour'      => [
                    'image'         => $image,
                    'title'         => $booking->tour?->title,
                    'address'       => $booking->tour?->location?->address,
                    'pricing'       => $pricing,
                    'extra'         => $extra,
                    'fees'          => $fees,
                    'discount'      => $discounts,
                    't_and_c'       => $booking->tour?->terms_and_conditions,
                    'order_email'   => $booking->tour?->order_email,
                ],
            ];
            \Log::warning("C====================================");
            
            // Atomically claim each email. A concurrent callback or thank-you
            // page refresh will see the claimed flag and will not send again.
            $customerEmailClaimed = Order::withoutGlobalScopes()
                ->whereKey($booking->id)
                ->where(fn ($query) => $query->where('email_sent', false)->orWhereNull('email_sent'))
                ->update(['email_sent' => true]) === 1;

            if ($customerEmailClaimed) {
                if (self::sendOrderDetailMail($detail, $action_name)) {
                    OrderActions::insert([
                        'order_id' => $booking->id,
                        'performed_by' => $booking->customer->id,
                        'notes' => $booking->customer->name." Pending order mail sent {$booking->order_number}",
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $booking->email_sent = true;
                } else {
                    Order::withoutGlobalScopes()->whereKey($booking->id)->update(['email_sent' => false]);
                    $booking->email_sent = false;
                }
            }

            $adminEmailClaimed = Order::withoutGlobalScopes()
                ->whereKey($booking->id)
                ->where(fn ($query) => $query->where('admin_email_sent', false)->orWhereNull('admin_email_sent'))
                ->update(['admin_email_sent' => true]) === 1;

            if ($adminEmailClaimed) {
                if (self::sendOrderDetailMail($detail, 'admin')) {
                    OrderActions::insert([
                        'order_id' => $booking->id,
                        'performed_by' => $booking->customer->id,
                        'notes' => " New order mail sent to Admin {$booking->order_number}",
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $booking->admin_email_sent = true;

                    $admin = User::find($booking->tour?->user_id);
                    if ($admin) {
                        $admin->notify(new NewOrderNotification($booking));
                        Log::info('NewOrderNotification');
                    }
                } else {
                    Order::withoutGlobalScopes()->whereKey($booking->id)->update(['admin_email_sent' => false]);
                    $booking->admin_email_sent = false;
                }
            }
            return response()->json([
                    'status'  => 'succeeded',
                    'booking' => $detail,
                ]);  
        }
        catch (\Exception $e) {
            Log::error('VerifyPayment Error: ' . $e->getMessage());
            return response()->json([
                'status'  => 'failed',
                'message' => $e->getMessage()
            ], 500);
        }
           
    }

    public function saveCard(Request $request)
    {
        Log::info('saveCard');

        orderLogAdvanced($request->order_id, 'payment', 'save_card_start', 'info', 'Saving card from frontend', [
            'order_id' => $request->order_id,
            'payment_method_id' => $request->payment_method_id
        ]);

        Stripe::setApiKey(env('STRIPE_SECRET'));

        $order_id = $request->order_id;

        $paymentMethod = \Stripe\PaymentMethod::retrieve(
            $request->payment_method_id
        );
        if($paymentMethod->card){
            $brand = $paymentMethod->card->brand;
            $last4 = $paymentMethod->card->last4;
            $expMonth = $paymentMethod->card->exp_month;
            $expYear = $paymentMethod->card->exp_year;
            $payment_method = 'card';
            $payment_type = 'CARD';
        } else{
            $payment_method = 'link';
            $payment_type = 'LINK';
            $brand = NULL;
            $last4 = NULL;
            $expMonth = NULL;
            $expYear = NULL;
        }
        

        $order = Order::find($order_id);
        $payment = OrderPayment::where('order_id', $order_id)
            ->whereNotIn('payment_type', ['PROMOCODE', 'DISCOUNT', 'BOOKINGFEE'])
            ->when(
                $order?->payment_intent_id,
                fn ($query, $intentId) => $query->where('payment_intent_id', $intentId)
            )
            ->latest('id')
            ->first();

        // Older/in-flight checkouts may not yet have copied the intent ID to
        // the order. Still restrict the fallback to an actual card payment row
        // so promo and discount credits can never be overwritten.
        if (!$payment) {
            $payment = OrderPayment::where('order_id', $order_id)
                ->whereNotIn('payment_type', ['PROMOCODE', 'DISCOUNT', 'BOOKINGFEE'])
                ->whereIn('status', ['reserve', 'pending', 'uncaptured', 'requires_capture'])
                ->latest('id')
                ->first();
        }

        if ($payment) {
            $payment->update([
                'payment_intent_id' => $request->payment_method_id,
                'card_last4'        => $last4,
                'card_brand'        => $brand,
                'card_exp_month'    => $expMonth,
                'card_exp_year'     => $expYear,
                'payment_method'    => $payment_method,
                'payment_type'      => $payment_type
            ]);

            $order = $order ?: $payment->order;
            $order->payment_method_id =  $request->payment_method_id;
            $order->save();

            orderLogAdvanced($order, 'payment', 'card_saved', 'success', 'Card saved successfully', [
                'brand' => $brand,
                'last4' => $last4,
                'payment_method_id' => $request->payment_method_id
            ]);
        } else {
            orderLogAdvanced($order_id, 'payment', 'payment_missing', 'failed', 'OrderPayment not found', [
                'order_id' => $order_id
            ]);
        }
        

        
        return response()->json([
            'status' => true,
            'brand' => $brand,
            'last4' => $last4,
        ]);
    }

    public static function sendOrderDetailMail($detail, $action_name = 'book')
    {
        Log::info('sendOrderDetailMail start');
        try{
            $order_id = $detail['order_number'];
            $order = Order::where('order_number',$order_id)->first();

            orderLogAdvanced($order?->id, 'email', 'start', 'info', 'Send order email started', [
                'action' => $action_name,
                'order_number' => $order_id
            ]);
            if($action_name === 'admin'){
                $identifier = 'admin_order_booking';
            } else{
                $identifier = $action_name == 'reserve' ? 'order_reserve' : 'order_pending';
            }
            
            $email_template = EmailTemplate::where('identifier', $identifier)->first();

            orderLogAdvanced($order?->id, 'email', 'template_loaded', 'success', 'Email template loaded', [
                'identifier' => $identifier
            ]);
            $template = $email_template->body;
            $template_footer = $email_template->footer;
            $template_subject = $email_template->subject;
            $header = $email_template->header;

            $pickup_address = '';
            $system_logo = get_setting('system_logo');
            $logo = uploaded_asset($system_logo);

            $customer = $detail['customer'];
            // dd($customer );
            if(!$customer) {
                $customer = $order->orderUser;
            }
 
            if(!$customer){
                // $customer = User::find(4);
                orderLogAdvanced($order?->id, 'email', 'customer_missing', 'error', 'Customer not found');
                return false;
            }

            orderLogAdvanced($order?->id, 'email', 'customer_loaded', 'success', 'Customer resolved', [
                'email' => $customer->email ?? null
            ]);
            Log::info('sendOrderDetailMail identifier');
            $orderTour  = $order->orderTours()->first();


            $tour       = $orderTour->tour;
            //echo '<pre>'; print_r($orderTour->tour); exit;
            $payment = $detail['payment_method'];

            orderLogAdvanced($order?->id, 'email', 'tour_loaded', 'success', 'Tour data loaded', [
                'tour_id' => $orderTour->tour_id ?? null
            ]);
            $emailPaymentSummary = (new OrderPaymentSummaryService())->summarize(
                $order->payments()->get()
            );
            $emailSubTotal = 0.0;
            $emailTaxTotal = 0.0;
            $emailAdjustmentSubTotal = 0.0;
            $emailAdjustmentTaxTotal = 0.0;

            foreach ($order->orderTours as $summaryOrderTour) {
                foreach (json_decode($summaryOrderTour->tour_pricing ?: '[]', true) ?: [] as $pricingRow) {
                    $quantity = (int) ($pricingRow['quantity'] ?? 0);
                    $actualPrice = (float) ($pricingRow['actual_price'] ?? $pricingRow['price'] ?? 0);
                    $emailSubTotal += (float) ($pricingRow['gross_total_price']
                        ?? (($pricingRow['price_type'] ?? 'PER_PERSON') === 'FIXED'
                            ? ($quantity > 0 ? $actualPrice : 0)
                            : $quantity * $actualPrice));
                    $emailAdjustmentSubTotal += max(
                        (int) ($pricingRow['newly_added_quantity'] ?? 0),
                        0
                    ) * (float) ($pricingRow['newly_added_price'] ?? $actualPrice);
                }

                foreach (json_decode($summaryOrderTour->tour_extra ?: '[]', true) ?: [] as $extraRow) {
                    $emailSubTotal += (float) ($extraRow['total_price']
                        ?? ((int) ($extraRow['quantity'] ?? 0) * (float) ($extraRow['price'] ?? 0)));
                    $emailAdjustmentSubTotal += max(
                        (int) ($extraRow['newly_added_quantity'] ?? 0),
                        0
                    ) * max((float) ($extraRow['newly_added_price'] ?? $extraRow['price'] ?? 0), 0);
                }

                foreach (json_decode($summaryOrderTour->tour_fees ?: '[]', true) ?: [] as $feeRow) {
                    $emailTaxTotal += (float) ($feeRow['price'] ?? 0);
                    $emailAdjustmentTaxTotal += max(
                        (float) ($feeRow['newly_added_amount'] ?? 0),
                        0
                    );
                }
            }

            Log::info('EmailPaymentSummary', $emailPaymentSummary);

            $emailSubTotal = round($emailSubTotal, 2);
            $emailDiscount = (float) $emailPaymentSummary['special_discount'];
            $emailPromo = (float) $emailPaymentSummary['promo_discount'];
            $emailBookingFee = (float) ($emailPaymentSummary['booking_fee'] > 0
                ? $emailPaymentSummary['booking_fee']
                : ($order->booking_fee ?? $order->bookingFee->value('value') ?? 0));
            $emailHst = round($emailTaxTotal, 2);
            $emailGrossTotal = round(max(
                $emailSubTotal - $emailDiscount - $emailPromo + $emailBookingFee + $emailHst,
                0
            ), 2);
            $emailBalance = round(max(
                $emailGrossTotal
                    - (float) $emailPaymentSummary['paid_amount']
                    - (float) $emailPaymentSummary['authorized_amount'],
                0
            ), 2);
            $emailPaid = round(
                (float) $emailPaymentSummary['paid_amount']
                    + (float) $emailPaymentSummary['authorized_amount'],
                2
            );
            $emailAdjustmentSubTotal = round($emailAdjustmentSubTotal, 2);
            $emailAdjustmentTaxTotal = round($emailAdjustmentTaxTotal, 2);
            $emailAdjustmentTotal = round(
                $emailAdjustmentSubTotal + $emailAdjustmentTaxTotal,
                2
            );
            $isAdjustmentEmail = $emailAdjustmentTotal > 0.01 && (
                $emailPaymentSummary['paid_amount'] > 0
                || $emailPaymentSummary['authorized_amount'] > 0
                || strtolower((string) $order->action_name) === 'reserve'
            );
            if ($isAdjustmentEmail) {
                $adjustmentWasPaid = (new CheckoutTotalService())
                    ->hasMatchingAdjustmentPayment(
                        $emailAdjustmentTotal,
                        $order->payments()->get()
                    );
                $emailSubTotal = $emailAdjustmentSubTotal;
                $emailDiscount = 0.0;
                $emailPromo = 0.0;
                $emailBookingFee = 0.0;
                $emailHst = $emailAdjustmentTaxTotal;
                $emailGrossTotal = $emailAdjustmentTotal;
                $emailPaid = $adjustmentWasPaid ? $emailAdjustmentTotal : 0.0;
                $emailBalance = $adjustmentWasPaid ? 0.0 : $emailAdjustmentTotal;
            }
            $emailPromoLabel = 'Promo Code' . ($emailPaymentSummary['promo_code']
                ? ' (' . e($emailPaymentSummary['promo_code']) . ')'
                : '');

            $TOUR_PAYMENT_HISTORY = '
            <style>
            @media only screen and (max-width: 640px) {
                .wrapper {
                width: 100% !important;
                padding: 0 10px !important;
                }
                .table, .header_table {
                width: 100% !important;  
                }
                .table td {
                display: block;
                width: 100% !important;
                text-align: left !important;
                }
                .table h3, .table small {
                text-align: left !important;
                }
            }
            </style>
            </head>
            <body style="margin:0; padding:0; font-family: \'Lato\', Helvetica, Arial, sans-serif; background-color: #f9f9f9;">
            <div class="wrapper" style="width:640px; margin:0 auto;">

            <table width="100%" bgcolor="#ffffff" cellpadding="0" cellspacing="0" border="0" align="center" class="header_table" style="width:100%; max-width:640px;">
                <tr>
                <td style="padding: 30px 30px 15px;">
                    <h3 style="font-size:19px; margin: 0;"><strong>Payment Summary</strong></h3>
                </td>
                </tr>
            </table>

            <table width="100%" bgcolor="#ffffff" cellpadding="0" cellspacing="0" border="0" align="center" class="table" style="border-collapse:collapse; background-color:#fff; width:100%; max-width:640px; border-left:30px solid #fff; border-right:30px solid #fff; border-bottom:30px solid #fff;">
                <tbody>
                <tr>
                    <td style="border-top:1pt solid #000; text-align:left; padding:5px 0;">
                        <small style="font-size:14px; text-transform:uppercase;">Sub Total</small>
                    </td>
                    <td style="border-top:1pt solid #000; text-align:right;">
                        <strong>' . price_format_with_currency($emailSubTotal, $order->currency) . '</strong>
                    </td>
                </tr>'
                . ($emailDiscount > 0 ? '<tr style="color:red;">
                    <td style="border-top:1pt solid #000; text-align:left; padding:5px 0;"><small style="font-size:14px; text-transform:uppercase;">Special Discount</small></td>
                    <td style="border-top:1pt solid #000; text-align:right;"><strong>-' . price_format_with_currency($emailDiscount, $order->currency) . '</strong></td>
                </tr>' : '')
                . ($emailPromo > 0 ? '<tr style="color:red;">
                    <td style="border-top:1pt solid #000; text-align:left; padding:5px 0;"><small style="font-size:14px; text-transform:uppercase;">' . $emailPromoLabel . '</small></td>
                    <td style="border-top:1pt solid #000; text-align:right;"><strong>-' . price_format_with_currency($emailPromo, $order->currency) . '</strong></td>
                </tr>' : '')
                . ($emailBookingFee > 0 ? '<tr>
                    <td style="border-top:1pt solid #000; text-align:left; padding:5px 0;"><small style="font-size:14px; text-transform:uppercase;">Booking Fee</small></td>
                    <td style="border-top:1pt solid #000; text-align:right;"><strong>' . price_format_with_currency($emailBookingFee, $order->currency) . '</strong></td>
                </tr>' : '')
                . '<tr>
                    <td style="border-top:1pt solid #000; text-align:left; padding:5px 0;"><small style="font-size:14px; text-transform:uppercase;">HST / Tax</small></td>
                    <td style="border-top:1pt solid #000; text-align:right;"><strong>' . price_format_with_currency($emailHst, $order->currency) . '</strong></td>
                </tr>
                <tr>
                    <td style="border-top:2pt solid #000; text-align:left; padding:5px 0;">
                        <small style="font-size:14px; text-transform:uppercase;">Total</small>
                    </td>
                    <td style="border-top:2pt solid #000; text-align:right;">
                        <h3 style="font-size:19px; margin:0;"><strong>' . price_format_with_currency($emailGrossTotal, $order->currency) . '</strong></h3>
                    </td>
                </tr>
                <tr style="color:green;">
                    <td style="border-top:1pt solid #000; text-align:left; padding:5px 0;"><small style="font-size:14px; text-transform:uppercase;">Total Paid</small></td>
                    <td style="border-top:1pt solid #000; text-align:right;"><strong>' . price_format_with_currency($emailPaid, $order->currency) . '</strong></td>
                </tr>
                <tr style="color:' . ($emailBalance > 0.01 ? 'red' : 'green') . ';">
                    <td style="border-top:1pt solid #000; text-align:left; padding:5px 0;"><small style="font-size:14px; text-transform:uppercase;">Balance</small></td>
                    <td style="border-top:1pt solid #000; text-align:right;"><strong>' . price_format_with_currency($emailBalance, $order->currency) . '</strong></td>
                </tr>
                </tbody>
            </table>';


            $TOUR_ITEM_SUMMARY = '';
            // Promo is an order-level credit. Allocate it only once when an
            // order contains more than one tour.
            $remainingEmailPromo = $emailPromo;

            foreach ($order->orderTours as $order_tour) {
                $subtotal = 0;
                $subtotal2 = 0;
                $tourDiscountTotal = 0;
                $adjustmentRows = '';
                $_tourId = $order_tour->tour_id;
                $tour_pricing = !empty($order_tour->tour_pricing) ? json_decode($order_tour->tour_pricing, true) : [];
                $tour_extra = !empty($order_tour->tour_extra) ? json_decode($order_tour->tour_extra, true) : [];
                $tour_discount = !empty($order_tour->discount) ? json_decode($order_tour->discount, true) : [];
                $tour_fees = !empty($order_tour->tour_fees) ? json_decode($order_tour->tour_fees, true) : [];
                if ($isAdjustmentEmail) {
                    $tour_pricing = collect($tour_pricing)
                        ->filter(fn ($row) => (int) ($row['newly_added_quantity'] ?? 0) > 0)
                        ->map(function ($row) {
                            $row['quantity'] = (int) $row['newly_added_quantity'];
                            $row['price'] = (float) ($row['newly_added_price'] ?? $row['actual_price'] ?? $row['price'] ?? 0);
                            $row['actual_price'] = $row['price'];
                            $row['discount'] = 0;
                            $row['total_price'] = $row['quantity'] * $row['price'];
                            return $row;
                        })->values()->all();
                    $tour_extra = collect($tour_extra)
                        ->filter(fn ($row) => (int) ($row['newly_added_quantity'] ?? 0) > 0)
                        ->map(function ($row) {
                            $row['quantity'] = (int) $row['newly_added_quantity'];
                            $row['price'] = (float) ($row['newly_added_price'] ?? $row['price'] ?? 0);
                            $row['total_price'] = $row['quantity'] * $row['price'];
                            return $row;
                        })->values()->all();
                    $tour_fees = collect($tour_fees)
                        ->filter(fn ($row) => (float) ($row['newly_added_amount'] ?? 0) > 0)
                        ->map(function ($row) {
                            $row['price'] = (float) $row['newly_added_amount'];
                            return $row;
                        })->values()->all();
                    $tour_discount = [];
                }

                if($order->tour && $order->sub_tour_id){
                    
                    $tourTitle = $order->tour->title . "<br>" . $order_tour->tour->title;
                    $tourTitleFormatted = $order->tour->title . "<br> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;" . $order_tour->tour->title;
                   
                } else{
                    $tourTitle = $order_tour->tour->title;
                    $tourTitleFormatted = $order_tour->tour->title;
                }
                
                $TOUR_ITEM_SUMMARY .= '
                <table width="100%" bgcolor="#ffffff" cellpadding="0" cellspacing="0" border="0" align="center" class="header_table">
                    <tbody>
                    <tr>
                        <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; text-align: left; padding: 30px 30px 15px; width:640px;">
                            <h3 style="font-size:19px"><strong>' . $tourTitle . '</strong></h3>
                        </td>
                    </tr>
                    </tbody>
                </table>

                <table width="100%" bgcolor="#ffffff" cellpadding="0" cellspacing="0" border="0" align="center" class="table" style="border-width:0 30px 30px; border-color: #fff; border-style: solid; background-color:#fff">
                    <tbody>
                        <tr>
                            <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; width: 10%; border-bottom:2pt solid #000; text-align: left;padding: 5px 0px;">
                                <small style="font-size:11px; font-weight:400; text-transform: uppercase; color:#000">#</small>
                            </td>
                            <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; width: 50%; border-bottom:2pt solid #000; text-align: left;padding: 5px 0px;">
                                <small style="font-size:11px; font-weight:400; text-transform: uppercase; color:#000">Description</small>
                            </td>
                            <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; width: 20%; border-bottom:2pt solid #000; text-align: left;padding: 5px 0px;">
                                &nbsp;
                            </td>
                            <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; width: 20%; border-bottom:2pt solid #000; text-align: right;padding: 5px 0px;">
                                <small style="font-size:11px; font-weight:400; text-transform: uppercase; color:#000">Total</small>
                            </td>
                        </tr>';
                
                // Pricing Rows
                $i = 1;
                $subTotalRequired = 1;
                $subTotalRequired = 0;
                $rowCount = 0;
                foreach ($tour_pricing as $result) {
                    // $result = getTourPricingDetails($tour_pricing, $pricing->id);
                    $qty = $result['quantity'] ?? 0;
                    $price = $result['price'] ?? 0;
                    $actual_price = isset($result['actual_price']) ? $result['actual_price'] : $result['price'];
                    $discount = $result['discount'] ?? 0;
                    $total = $result['total_price'] ?? 0;
                    $gt_total = (float) ($result['gross_total_price']
                        ?? ($result['price_type'] === 'FIXED' ? $actual_price : $actual_price * $qty));
                    if ($qty > 0) {
                        $rowCount++;
                        $subtotal += $total;
                        $subtotal2+= $gt_total;
                        $price_text = $price ? price_format_with_currency($gt_total, $order->currency) : 'Free';
                        $TOUR_ITEM_SUMMARY .= '
                        <tr>
                            <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; border-top:1pt solid #ddd; text-align: left;padding: 5px 0px;">' . $qty . '</td>
                            <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; border-top:1pt solid #ddd; text-align: left;padding: 5px 0px;">' . ucwords($result['label']) . '</td>
                            <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; border-top:1pt solid #ddd; text-align: left;padding: 5px 0px;">' . price_format_with_currency($actual_price, $order->currency) . '</td>
                            <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; border-top:1pt solid #ddd; text-align: right;padding: 5px 0px;">' . $price_text . '</td>
                        </tr>';
                    }
                }
                if ($rowCount > 1) {
                   $subTotalRequired = 1;
                }

                // $TOUR_ITEM_SUMMARY .= '
                //     <tr>
                //         <td>&nbsp;</td>
                //         <td>&nbsp;</td>
                //         <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; border-top:2pt solid #000; text-align: left;padding: 5px 0px;">
                //             <small style="font-size:11px; font-weight:400; text-transform: uppercase;">
                //                 <strong>Sub Total </strong>
                //             </small>
                //         </td>
                //         <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; border-top:2pt solid #000; text-align: right;padding: 5px 0px;">
                //                 ' . price_format_with_currency($subtotal2, $order->currency) . '
                //         </td>
                //     </tr>';

                // Extras Rows
                foreach ($tour_extra as $extra) {
                    // $result = getTourExtraDetails($tour_extra, $extra->id);
                    $qty = $extra['quantity'] ?? 0;
                    $price = $extra['price'] ?? 0;
                    // $total = $qty * $price;
                    $total = $extra['total_price'] ?? 0;
                    if ($qty > 0) {
                        $subTotalRequired = 1;
                        $subtotal += $total;
                        $TOUR_ITEM_SUMMARY .= '
                        <tr>
                            <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; border-top:1pt solid #ddd; text-align: left;padding: 5px 0px;">' . $qty . '</td>
                            <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; border-top:1pt solid #ddd; text-align: left;padding: 5px 0px;">' . $extra['label'] . ' </td>
                            <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; border-top:1pt solid #ddd; text-align: left;padding: 5px 0px;">' . price_format_with_currency($price, $order->currency) . '</td>
                            <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; border-top:1pt solid #ddd; text-align: right;padding: 5px 0px;">' . price_format_with_currency($total, $order->currency) . '</td>
                        </tr>';
                    }
                }

                // Discount
                foreach ($tour_pricing as $result) {
                    $qty = $result['quantity'] ?? 0;
                    $discount = $result['discount'] ?? 0;
                    $dis_total = $result['price_type'] === 'FIXED' ? $discount : ($discount * $qty);
                    if ($qty > 0 && $discount > 0) {
                        $tourDiscountTotal += $dis_total;
                        $subTotalRequired = 1;
                    }
                }

                if ($tourDiscountTotal > 0) {
                    $adjustmentRows .= '
                    <tr>
                        <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; border-top:1pt solid #ddd; text-align: left;padding: 5px 0px;"></td>
                        <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; border-top:1pt solid #ddd; text-align: left;padding: 5px 0px;"></td>
                        <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; border-top:1pt solid #ddd; text-align: left;padding: 5px 0px;color:#f64747;">Special Discount</td>
                        <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; border-top:1pt solid #ddd; text-align: right;padding: 5px 0px;color:#f64747;">-' . price_format_with_currency($tourDiscountTotal, $order->currency) . '</td>
                    </tr>';
                }

                $tourPromo = min(
                    $remainingEmailPromo,
                    max($subtotal - $tourDiscountTotal, 0)
                );
                $remainingEmailPromo = max($remainingEmailPromo - $tourPromo, 0);

                if ($tourPromo > 0) {
                    $subTotalRequired = 1;
                    $adjustmentRows .= '
                    <tr>
                        <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; border-top:1pt solid #ddd; text-align: left;padding: 5px 0px;"></td>
                        <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; border-top:1pt solid #ddd; text-align: left;padding: 5px 0px;"></td>
                        <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; border-top:1pt solid #ddd; text-align: left;padding: 5px 0px;color:#f64747;">' . $emailPromoLabel . '</td>
                        <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; border-top:1pt solid #ddd; text-align: right;padding: 5px 0px;color:#f64747;">-' . price_format_with_currency($tourPromo, $order->currency) . '</td>
                    </tr>';
                }

                if($subTotalRequired){
                    $TOUR_ITEM_SUMMARY .= '
                        <tr>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; border-top:2pt solid #000; text-align: left;padding: 5px 0px;">
                                <small style="font-size:11px; font-weight:400; text-transform: uppercase;">
                                    <strong>Sub Total </strong>
                                </small>
                            </td>
                            <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; border-top:2pt solid #000; text-align: right;padding: 5px 0px;">
                                    ' . price_format_with_currency($subtotal, $order->currency) . '
                            </td>
                        </tr>' . $adjustmentRows;

                }

                // Discounts reduce the taxable base, while the displayed
                // subtotal remains the original item subtotal.
                $subtotal = max($subtotal - $tourDiscountTotal - $tourPromo, 0);

                // Use the tax snapshot saved at checkout. Recalculating from
                // the tour's current tax rules can change historical emails.
                $taxRows = '';
                if (!empty($tour_fees)) {
                    foreach ($tour_fees as $tax) {
                        $taxAmount = (float) ($tax['price'] ?? 0);
                        $subtotal += $taxAmount;
                        $taxLabel = e($tax['label'] ?? 'Tax');
                        if (($tax['type'] ?? null) === 'PERCENT' && isset($tax['value'])) {
                            $taxLabel .= ' (' . (float) $tax['value'] . '%)';
                        }
                        $taxRows .= '
                        <tr>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; border-top:2pt solid #000; text-align: left;padding: 5px 0px;">
                                <small style="font-size:11px; font-weight:400; text-transform: uppercase; color:#000;">' . $taxLabel . '</small>
                            </td>
                            <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; border-top:2pt solid #000; text-align: right;padding: 5px 0px;">
                                ' . price_format_with_currency($taxAmount, $order->currency) . '
                            </td>
                        </tr>';
                    }
                }

                // Total Row
                $TOUR_ITEM_SUMMARY .= $taxRows . '

                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; border-top:2pt solid #000; text-align: left;padding: 5px 0px;">
                            <h3 style="color:#000; margin:0; font-size:15px"><strong>Total</strong></h3>
                        </td>
                        <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; border-top:2pt solid #000; text-align: right;padding: 5px 0px;">
                            <h3 style="color:#000; margin:0; font-size:15px"><strong>' . price_format_with_currency($subtotal, $order->currency) . '</strong></h3>
                        </td>
                    </tr>';

                $paymentSummary = (new OrderPaymentSummaryService())->summarize(
                    $order->payments()->get()
                );
                $paid = round(
                    $paymentSummary['paid_amount'] + $paymentSummary['authorized_amount'],
                    2
                );
                if ($paid > 0) {                    
                    // paid amount
                    $TOUR_ITEM_SUMMARY .= '
                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; border-top:2pt solid #000; text-align: left;padding: 5px 0px;">
                            <h3 style="color:green; margin:0; font-size:15px"><strong>Total Paid</strong></h3>
                        </td>
                        <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; border-top:2pt solid #000; text-align: right;padding: 5px 0px;">
                            <h3 style="color:green; margin:0; font-size:15px"><strong>' . price_format_with_currency($paid, $order->currency) . '</strong></h3>
                        </td>
                    </tr>'; 
                }

                $balanceColor = $emailBalance > 0.01 ? 'red' : 'green';
                $TOUR_ITEM_SUMMARY .= '
                <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; border-top:2pt solid #000; text-align: left;padding: 5px 0px;">
                        <h3 style="color:' . $balanceColor . '; margin:0; font-size:15px"><strong>Balance</strong></h3>
                    </td>
                    <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; border-top:2pt solid #000; text-align: right;padding: 5px 0px;">
                        <h3 style="color:' . $balanceColor . '; margin:0; font-size:15px"><strong>' . price_format_with_currency($emailBalance, $order->currency) . '</strong></h3>
                    </td>
                </tr>';
                
                $TOUR_ITEM_SUMMARY .=  '</tbody>
                </table>';
            }
            orderLogAdvanced($order?->id, 'email', 'summary_built', 'success', 'Tour summary generated');
            $pickup_address = '';
            if( $order->customer->pickup_name ) {
                $pickup_address = $order->customer->pickup_name;
            }
            else if($order->customer->pickup_id) {
                $pickup_address = $order->customer?->pickup?->location . ' ( '.$order->customer?->pickup?->address.' )';
            }
            // if($pickup_address) {
            //     $pickup_address = '
            //       <small style="font-size:11px; font-weight:400; text-transform: uppercase; color:#fff;">Pick up</small>
            //       <h3 style="color: #fff; margin-top: 5px; font-size: 15px; margin-bottom: 5px;">
            //         <strong>' . $pickup_address . '</strong>
            //       </h3>';
            // }

            // $to_address = $tour->location->destination ?? '';
            // $to_address.= $tour->location->address ? ' ('.$tour->location->address.')' : '';

            if($order->tour && $order->sub_tour_id){
                
                $to_address = $order->tour->location->destination ?? '';
                $to_address.= $order->tour->location->address ? ' ('.$order->tour->location->address.')' : '';

                $tourLocationAddress = $order->tour->location->address;
            }else{
                
                $to_address = $tour->location->destination ?? '';
                $to_address.= $tour->location->address ? ' ('.$tour->location->address.')' : '';
                $tourLocationAddress = $tour->location->address;

            }
            $orderPaymentSummary = (new OrderPaymentSummaryService())->summarize(
                $order->payments()->get()
            );
            $order_paid = round(
                $orderPaymentSummary['paid_amount'] + $orderPaymentSummary['authorized_amount'],
                2
            );
            $replacements = [   
                "[[CUSTOMER_NAME]]"         => $customer->name ?? '',
                "[[CUSTOMER_EMAIL]]"        => $customer->email ?? '',
                "[[CUSTOMER_PHONE]]"        => '+'.$customer->phone ?? '',

                "[[TOUR_TITLE]]"            => $tourTitleFormatted,
                // "[[TOUR_MAP]]"              => $to_address,
                "[[TOUR_ADDRESS]]"          => $to_address,
                "[[TOUR_MAP]]"              => $pickup_address,
                "[[PICKUP_ADDRESS]]"        => $pickup_address,
                "[[TOUR_PAYMENT_HISTORY]]"  => $TOUR_PAYMENT_HISTORY,
                "[[TOUR_ITEM_SUMMARY]]"     => $TOUR_ITEM_SUMMARY,
                "[[TOUR_TERMS_CONDITIONS]]" => $tour->terms_and_conditions,

                "[[APP_LOGO]]"              => $logo,
                "[[APP_NAME]]"              => get_setting('site_name'),
                "[[COMPANY_NAME]]"          => get_setting('site_name'),
                "[[APP_URL]]"               => get_setting('app_url'),
                "[[APP_EMAIL]]"             => get_setting('app_email'),
                "[[APP_PHONE]]"             => get_setting('app_phone'),
                "[[APP_ADDRESS]]"           => get_setting('app_address'),
                "[[ORDER_NUMBER]]"          => $order->order_number ?? '',
                "[[ORDER_STATUS]]"          => $order->status,
                "[[ORDER_TOUR_DATE]]"       => $order->order_tour->tour_date ? date('l, F j, Y', strtotime($order->order_tour->tour_date)) : '',
                "[[ORDER_TOUR_TIME]]"       => $order->order_tour->tour_time ? date('H:i A', strtotime($order->order_tour->tour_time)) : '',
                "[[ORDER_TOTAL]]"           => price_format_with_currency($emailGrossTotal, $order->currency) ?? '',
                "[[ORDER_BALANCE]]"         => price_format_with_currency($emailBalance, $order->currency) ?? '',
                "[[ORDER_BALANCE_COLOR]]"   => (abs($emailBalance) < 0.01) ? '008000' : 'f64747',
                "[[ORDER_PAID]]"            => price_format_with_currency($order_paid, $order->currency) ?? '',
                "[[ORDER_BOOKING_FEE]]"     => price_format_with_currency($order->booking_fee, $order->currency) ?? '',
                "[[ORDER_CREATED_DATE]]"    => date('M d, Y', strtotime($order->created_at)) ?? '',
                "[[YEAR]]"                  => date('Y'),
            ];

            Log::info('order_email_sent' . 477);
            $body = strtr($template, $replacements);
            $footer = strtr($template_footer, $replacements);
            $subject = strtr($template_subject, $replacements);

            // $event = [
            //             'uid' => $customer->email,
            //             'start' => $order->order_tour->tour_date ? date('l, F j, Y', strtotime($order->order_tour->tour_date)) : '', // local time
            //             'end' => $order->order_tour->tour_time ? date('H:i A', strtotime($order->order_tour->tour_time)) : '',
            //             'title' => $tour->title,
            //             'description' => $email_template->subject,
            //             'location' => $tour->location->address,
            //         ];
                    
            $event = [
                'uid' => $customer->email,
                'start' => $order->order_tour->tour_date . ' ' . $order->order_tour->tour_time, // "2025-10-02 6:00 PM"
                'end' => $order->order_tour->tour_date . ' ' . date(
                    'g:i A',
                    strtotime('+2 hours', strtotime($order->order_tour->tour_time))
                ),
                'title' => $tour->title,
                'description' => $email_template->subject,
                'location' => $to_address,
            ];

            Log::info('order_mail_send 676' . env('MAIL_FROM_ADDRESS'));
            Log::info('order_mail_send 676' . env('MAIL_FROM_ADMIN_ADDRESS'));
            orderLogAdvanced($order?->id, 'email', 'mail_sending', 'info', 'Sending email now', [
                'to' => $action_name == 'admin' ? 'admin' : $customer->email
            ]);
          
            if($action_name == 'admin'){

                $admin_subject = "[[ORDER_NUMBER]] : New Order Received";
                $subject = strtr($admin_subject, $replacements);

                $notifiableAdmins = User::where('email_notification', 1)
                        ->pluck('email')
                        ->toArray();

                Log::info('order_mail_send 676' . env('MAIL_FROM_ADMIN_ADDRESS'));

            // Always include fallback addresses from .env
            $defaultEmails = [
                // env('MAIL_FROM_ADDRESS'),
                env('MAIL_FROM_ADMIN_ADDRESS')
            ];

            // Merge both lists and remove duplicates
            $recipients = array_values(array_filter(array_unique(array_merge($defaultEmails, $notifiableAdmins))));

                $mailSend = self::order_mail_send($recipients,$subject, $header,  $body, $footer, $event, 'admin', $order?->id);
            } else{
                $mailSend = self::order_mail_send($customer->email,$subject, $header,  $body, $footer, $event, 'customer', $order?->id);
            }

            if ($mailSend === false) {
                return false;
            }
            
            orderLogAdvanced($order?->id, 'email', 'mail_sent', 'success', 'Email sent successfully', [
                'message_id' => $mailSend
            ]);
            Log::info('OrderEmailHistorythishere' . $mailSend);
            Log::info('OrderEmailHistory' . $mailSend);
            OrderEmailHistory::create([
                'order_id'  => $order->id,
                'to_email'  => $action_name == 'admin' ? 'Admin' : $customer->email,
                'from_email'=> env('MAIL_FROM_ADDRESS'),
                'subject'   => $subject,
                'body'      => $header.$body.$footer,
                'message_id' => $mailSend
            ]);
            orderLogAdvanced($order?->id, 'email', 'history_saved', 'success', 'Email history stored');

            return true;
        }
        catch(\Exception $e){
            
            Log::info($e);

            orderLogAdvanced($order?->id ?? null, 'email', 'failed', 'error', 'Email sending failed', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
 
    }

    public static function order_mail_send($email,$subject, $header,  $body, $footer, $event = null, $recipient = 'customer', $orderId =  null)
    {
         Log::info('order_mail_send' . 718);

         orderLogAdvanced($orderId, 'mail', 'start', 'info', 'Email sending started', [
            'email' => $email,
            'recipient' => $recipient,
            'subject' => $subject,
        ]);
        if (env('MAIL_FROM_ADDRESS') != null) {
            
            $array['view'] = 'emails.newsletter';
            $array['subject'] = $subject;

            $array['header'] = $header;
            $array['from'] = env('MAIL_FROM_ADDRESS');
            $array['content'] =  $header.$body.$footer;
            $array['event'] = $event;

 
            try {


                $mailer = Mail::mailer('mailgun');
                // $mailer = Mail::mailer();


                // Send email and capture message info
                // $sentMessage = $mailer->to($email)->send(new EmailManager($array));

                

                if($recipient == 'admin'){
                    Log::info('Admin recipients:', $email);
                    orderLogAdvanced($orderId, 'mail', 'admin_mail', 'info', 'Sending admin email', [
                        'bcc' => $email
                    ]);
                     $sentMessage = $mailer->to(env('MAIL_FROM_ADDRESS'))->bcc($email)->send(new AdminBookingMail($array, $event['uid']));
                } else{
                    orderLogAdvanced($orderId, 'mail', 'customer_mail', 'info', 'Sending customer email', [
                        'to' => $email
                    ]);
                        $sentMessage = $mailer->to($email)->send(new EmailManager($array));
                        
                        
                }

                $messageId = null;
                if ($sentMessage instanceof \Illuminate\Mail\SentMessage) {
                    $symfonySent = $sentMessage->getSymfonySentMessage();
                    if ($symfonySent && method_exists($symfonySent, 'getMessageId')) {
                        $messageId = $symfonySent->getMessageId();
                        $messageId = trim($messageId, '<>');
                    }
                }
                 orderLogAdvanced($orderId, 'mail', 'sent', 'success', 'Email sent successfully', [
                    'message_id' => $messageId
                ]);
                // Some transports successfully send without exposing a
                // provider message ID.
                return $messageId ?: 'sent';
                
                 
            } catch (\Exception $e) {
                Log::error('Order email send failed', [
                    'order_id' => $orderId,
                    'recipient' => $recipient,
                    'error' => $e->getMessage(),
                ]);
                return false;
            }
        } else {
            orderLogAdvanced($orderId, 'mail', 'config_missing', 'error', 'MAIL_FROM_ADDRESS missing');
            return false;
        }
       
    }

    /*
    |--------------------------------------------------------------------------
    | SAVE CARD DETAILS
    |--------------------------------------------------------------------------
    */

    private function saveCardDetails($intent)
    {

        orderLogAdvanced(null, 'webhook', 'card_save_start', 'info', 'Webhook card save started', [
            'payment_intent_id' => $intent->id ?? null
        ]);
        Log::info('saveCardDetails');


        try {

            Stripe::setApiKey(env('STRIPE_SECRET'));

            if (empty($intent->payment_method)) {

                 orderLogAdvanced(null, 'webhook', 'missing_payment_method', 'failed', 'Payment method missing in intent', [
                    'payment_intent_id' => $intent->id
                ]);

                Log::warning(
                    'Payment method missing for PI: ' .
                    $intent->id
                );

                return;
            }

            $paymentMethod = PaymentMethod::retrieve(
                $intent->payment_method
            );

            if (
                !isset($paymentMethod->card) ||
                $paymentMethod->type !== 'card'
            ) {
                orderLogAdvanced(null, 'webhook', 'non_card_payment', 'info', 'Non-card payment method', [
                    'type' => $paymentMethod->type ?? null
                ]);
                return;
            }

            $cardDetails = [
                'type' => $paymentMethod->type ?? null,
                'brand' => $paymentMethod->card->brand ?? null,
                'last4' => $paymentMethod->card->last4 ?? null,
                'exp_month' => $paymentMethod->card->exp_month ?? null,
                'exp_year' => $paymentMethod->card->exp_year ?? null,
            ];

            /*
            |--------------------------------------------------------------------------
            | FIND ORDER
            |--------------------------------------------------------------------------
            */

            $orderPayment = OrderPayment::where(
                'payment_intent_id',
                $intent->id
            )->first();

            if (!$orderPayment) {

                orderLogAdvanced(null, 'webhook', 'order_payment_missing', 'failed', 'OrderPayment not found for intent', [
                    'payment_intent_id' => $intent->id
                ]);

                Log::warning(
                    'OrderPayment not found for PI: ' .
                    $intent->id
                );

                return;
            }

            /*
            |--------------------------------------------------------------------------
            | UPDATE PAYMENT
            |--------------------------------------------------------------------------
            */
            // 'pending','succeeded','failed','refunded','partial_refunded','uncaptured','reserve','capture_canceled'

            $status = match ($intent->status) {
                'requires_capture' => 'uncaptured',
                'succeeded'        => 'succeeded',
                'canceled'         => 'capture_canceled',
                default            => $intent->status,
            };

            $orderPayment->update([
                'payment_method' => $cardDetails['type'] ?? null,
                'card_brand' => $cardDetails['brand'] ?? null,
                'card_last4' => $cardDetails['last4'] ?? null,
                'card_exp_month' => $cardDetails['exp_month'] ?? null,
                'card_exp_year' => $cardDetails['exp_year'] ?? null,
                'status' => $status,
            ]);

            /*
            |--------------------------------------------------------------------------
            | UPDATE ORDER
            |--------------------------------------------------------------------------
            */

            $order = Order::find($orderPayment->order_id);

            if ($order) {
                $order->card_info =
                    json_encode($cardDetails);

                $order->save();
            }
            orderLogAdvanced($order, 'webhook', 'card_saved', 'success', 'Card details saved via webhook', [
                'payment_intent_id' => $intent->id,
                'status' => $status,
                'brand' => $cardDetails['brand'],
                'last4' => $cardDetails['last4']
            ]);

            Log::info(
                'Card details saved successfully for PI: ' .
                $intent->id
            );

        } catch (\Exception $e) {

             orderLogAdvanced(null, 'webhook', 'card_save_error', 'error', $e->getMessage(), [
                'payment_intent_id' => $intent->id ?? null
            ]);
            Log::error(
                'Webhook card save error: ' .
                $e->getMessage()
            );
        }
    }

    /**
 * Sync OrderPayment and Order status from Stripe PaymentIntent.
 */
    private function syncPaymentStatusFromWebhook($intent): void
    {
        $orderPayment = OrderPayment::where(
            'payment_intent_id',
            $intent->id
        )->first();

        if (!$orderPayment) {
            return;
        }

        $order = Order::find($orderPayment->order_id);

        if (!$order) {
            return;
        }

        $status = match ($intent->status) {
            'requires_capture' => 'uncaptured',
            'succeeded'        => 'succeeded',
            'canceled'         => 'capture_canceled',
            default            => null,
        };

        if (!$status) {
            return;
        }

        // Special case: Pending -> Uncaptured
        if (
            $orderPayment->status === 'pending' &&
            $order->order_status == 1 &&
            $intent->status === 'requires_capture'
        ) {

            $orderPayment->update([
                'status' => 'uncaptured',
            ]);

            $order->update([
                'order_status' => 3,
            ]);

            $this->reconcileOrderBalance($order, true);

            orderLogAdvanced(
                $order,
                'payment',
                'pending_to_uncaptured',
                'success',
                'Pending payment converted to uncaptured from Stripe webhook.'
            );

            return;
        }

        // Normal status update
        if ($orderPayment->status !== $status) {

            $orderPayment->update([
                'status' => $status,
            ]);

            orderLogAdvanced(
                $order,
                'payment',
                'payment_status_updated',
                'success',
                "Payment status updated to {$status}."
            );
        }

        if (in_array($intent->status, ['requires_capture', 'succeeded'], true)) {
            $this->reconcileOrderBalance(
                $order,
                $intent->status === 'requires_capture'
            );
        }
    }

    private function reconcileOrderBalance(Order $order, bool $isAuthorized = false): void
    {
        $summary = (new OrderPaymentSummaryService())->summarize(
            $order->payments()->get()
        );
        $grossAmount = round((float) $order->orderTours()->sum('total_amount'), 2);
        $balance = $this->normalizeCurrencyAmount(
            max($grossAmount - $summary['total_credits'], 0),
            (string) $order->currency
        );

        $order->update([
            'booked_amount' => $summary['paid_amount'],
            'balance_amount' => $balance,
            'payment_status' => $isAuthorized ? 3 : ($balance <= 0.01 ? 1 : 0),
        ]);

        orderLogAdvanced($order, 'payment', 'balance_reconciled', 'success', 'Order balance reconciled', [
            'paid_amount' => $summary['paid_amount'],
            'authorized_amount' => $summary['authorized_amount'],
            'discounts' => $summary['special_discount'] + $summary['promo_discount'],
            'balance_amount' => $balance,
        ]);
    }

    private function syncRefundFromWebhook(mixed $charge, Order $order): void
    {
        $paymentIntentId = $charge->payment_intent ?? null;
        $payment = $paymentIntentId
            ? $order->payments()->where('payment_intent_id', $paymentIntentId)->first()
            : null;

        if (!$payment) {
            return;
        }

        $stripeCurrency = (string) ($charge->currency ?? $order->currency);
        $refundedAmount = $this->fromStripeAmount(
            (float) ($charge->amount_refunded ?? 0),
            $stripeCurrency
        );
        $refundedAmount = min($refundedAmount, (float) $payment->amount);
        $refund = collect($charge->refunds->data ?? [])->last();
        $refundId = $refund->id ?? null;
        $refundAmount = $this->fromStripeAmount(
            (float) ($refund->amount ?? 0),
            (string) ($refund->currency ?? $stripeCurrency)
        );

        $payment->update([
            'refund_id' => $refundId ?? $payment->refund_id,
            'refund_amount' => $refundedAmount,
            'refunded_at' => now(),
            'refund_reason' => $refund->reason ?? $payment->refund_reason,
            'status' => $refundedAmount >= (float) $payment->amount
                ? 'refunded'
                : 'partial_refunded',
        ]);

        if ($refundId) {
            OrderPayment::updateOrCreate(
                [
                    'transaction_id' => $refundId,
                    'payment_type' => 'REFUND',
                ],
                [
                    'order_id' => $order->id,
                    'payment_intent_id' => $paymentIntentId,
                    'transaction_id' => $refundId,
                    'payment_method' => $payment->payment_method,
                    'payment_type' => 'REFUND',
                    'amount' => $refundAmount,
                    'currency' => strtoupper((string) ($charge->currency ?? $order->currency)),
                    'status' => 'refunded',
                    'action' => 'refund',
                    'reason' => $refund->reason ?? null,
                    'response_payload' => json_encode($refund),
                    'collection_date' => now(),
                ]
            );
        }

        $this->reconcileOrderBalance($order);
    }

    private function redeemPromoOnce(Order $order): void
    {
        $promoPayment = $order->payments()
            ->where('payment_type', 'PROMOCODE')
            ->where('status', 'discount')
            ->where(function ($query) {
                $query->whereNull('action')
                    ->orWhere('action', '!=', 'redeemed');
            })
            ->first();

        if (!$promoPayment || !$promoPayment->transaction_id) {
            return;
        }

        $promo = Promo::where('code', $promoPayment->transaction_id)->first();
        if (!$promo) {
            return;
        }

        $promo->increment('used_count');
        $promoPayment->update(['action' => 'redeemed']);

        orderLogAdvanced($order, 'promo', 'redeemed', 'success', 'Promo redemption finalized', [
            'promo_code' => $promo->code,
        ]);
    }

    private function stripeAmount(float $amount, string $currency): int
    {
        return $this->isZeroDecimalCurrency($currency)
            ? (int) round($amount)
            : (int) round($amount * 100);
    }

    private function fromStripeAmount(float $amount, string $currency): float
    {
        return $this->isZeroDecimalCurrency($currency)
            ? round($amount, 0)
            : round($amount / 100, 2);
    }

    private function normalizeCurrencyAmount(float $amount, string $currency): float
    {
        return $this->isZeroDecimalCurrency($currency)
            ? round($amount, 0)
            : round($amount, 2);
    }

    private function isZeroDecimalCurrency(string $currency): bool
    {
        $zeroDecimalCurrencies = [
            'bif', 'clp', 'djf', 'gnf', 'jpy', 'kmf',
            'krw', 'mga', 'pyg', 'rwf', 'ugx',
            'vnd', 'vuv', 'xaf', 'xof', 'xpf',
        ];

        return in_array(strtolower($currency), $zeroDecimalCurrencies, true);
    }
    
}
