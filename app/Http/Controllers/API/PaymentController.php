<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Mail\EmailManager;
use App\Models\Addon;
use App\Models\TourPricing;
use App\Models\EmailTemplate;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Stripe\Webhook;


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

        Stripe::setApiKey(env('STRIPE_SECRET'));

        try {
            $clientSecret = $request->client_secret;
            $intentId = explode('_secret_', $clientSecret)[0];

            // Retrieve payment intent from Stripe
            $paymentIntent = PaymentIntent::retrieve($intentId);

            if ($paymentIntent->status === 'succeeded') {

                $cacheKey = 'booking_' . $paymentIntent->id;

                // Try retrieving from cache or load and store it
                $booking = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($paymentIntent) {
                    return Order::with([
                        'tour',
                        'tour.location',
                        'tour.detail',
                        'customer'
                    ])->where('payment_intent_id', $paymentIntent->id)->first();
                });

                if ($booking && $booking->order_status !== 1) {
                    $booking->order_status   = 1;
                    $booking->payment_status = 1;
                    $booking->payment_method = $paymentIntent->payment_method_types[0] ?? 'card';
                    $booking->updated_at     = now();
                    $booking->save();

                    // Refresh cache after updating the order
                    // Cache::put($cacheKey, $booking->fresh(['tour.location', 'tour.detail', 'tour.addons', 'tour.fees', 'tour.pickups', 'customer']), now()->addMinutes(10));
                }
                
                $tour_pricing = json_decode($booking->order_tour->tour_pricing);
                $pricing=[]; $total = 0;
                foreach($tour_pricing as $tp) {
                    $tourPricing = TourPricing::find($tp->tour_pricing_id);
                    $total = ($tp->quantity * $tp->price);
                    $pricing[] = [
                        'lable' => $tourPricing->label,
                        'qty'   => $tp->quantity,
                        'price' => $tp->price,
                        'total' => $total
                    ];
                }

                $extra_pricing = json_decode($booking->order_tour->tour_extra);
                $extra=[]; $total = 0;
                foreach($extra_pricing as $ep) {
                    $extraAddon = Addon::find($ep->tour_extra_id);
                    $total = ($ep->quantity * $ep->price);
                    $extra[] = [
                        'lable' => $extraAddon->name,
                        'qty'   => $ep->quantity,
                        'price' => $ep->price,
                        'total' => $total
                    ];
                }

                $metas=[]; 
                if($booking->orderMetas) {
                    foreach($booking->orderMetas as $om) {
                        $metas[] = [
                            'lable' => $om->name,
                            'qty'   => '',
                            'price' => $om->value,
                            'total' => $om->value,
                        ];
                    }
                }


                $image = uploaded_asset($booking->tour->main_image->id, 'medium');

                $detail = [
                    'order_number'      => $booking->order_number,
                    'number_of_guests'  => $booking->number_of_guests,
                    //'total_amount'      => price_format_with_currency($booking->total_amount, $booking->currency) ?? '',
                    'total_amount'      => $booking->total_amount ?? '',
                    'currency'          => $booking->currency,
                    'payment_method'    => ucfirst($booking->payment_method),
                    'customer'          => $booking->customer,
                    'tour_date'         => date('D, M d, Y', strtotime($booking->order_tour->tour_date)),
                    'tour_time'         => $booking->order_tour->tour_time,
                    'created_at'        => date('Y-m-d', strtotime($booking->created_at)),
                    'tour'      => [
                        'image'         => $image,
                        'title'         => $booking->tour?->title,
                        'address'       => $booking->tour?->location->address,
                        'pricing'       => $pricing,
                        'extra'         => $extra,
                        'metas'         => $metas,
                        't_and_c'       => $booking->tour?->terms_and_conditions,
                        'order_email'   => $booking->tour?->order_email,
                    ],
                ];

                $order = Order::where('order_number',$booking->order_number)->first();

                Log::info('order_email_sent' . $order->email_sent);
                if ($order && $booking->tour->order_email && !$order->email_sent) {                    
                    $mailsent = self::sendOrderDetailMail($detail);
                    Log::info('order_email_sentqwwqdwqqdqwdqw' . $order->email_sent);
                    $order->email_sent = true;
                    $order->save();
                }
                Log::info('order_email_sentqwwqdwq' . $order->email_sent);

                return response()->json([
                        'status'  => 'succeeded',
                        'booking' => $detail,
                    ]);

                

            }

            return response()->json([
                'status'  => 'failed',
                'message' => 'Payment not successful',
            ], 400);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'failed',
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    public static function sendOrderDetailMail($detail)
    {
        
        try{
            $order_id = $detail['order_number'];
            // $order_template_id = $request->order_template_id;
            $order = Order::where('order_number',$order_id)->first();
            $email_template = EmailTemplate::where('identifier', 'order_detail')->first();

            $template = $email_template->body;

            $template_footer = $email_template->footer;

            $template_subject = $email_template->subject;

            $header = $email_template->header;

            $system_logo = get_setting('system_logo');
            $logo = uploaded_asset($system_logo);

            $customer = $detail['customer'];
            // dd($customer );
            if(!$customer){
                $customer = $order->orderUser;
            }
 
            if(!$customer){
                // $customer = User::find(4);

                return response()->json([
                    'success' => false,
                    'message' => "customer not found"
                ], 404);
            }
            
            $orderTour  = $order->orderTours()->first();

            
            $tour       = $orderTour->tour;
            //echo '<pre>'; print_r($orderTour->tour); exit;
            $payment = $detail['payment_method'];


            $TOUR_PAYMENT_HISTORY =    '
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
                                      <h3 style="font-size:19px; margin: 0;"><strong>Payment History</strong></h3>
                                    </td>
                                  </tr>
                                </table>

                                <table width="100%" bgcolor="#ffffff" cellpadding="0" cellspacing="0" border="0" align="center" class="table" style="border-collapse:collapse; background-color:#fff; width:100%; max-width:640px; border-left:30px solid #fff; border-right:30px solid #fff; border-bottom:30px solid #fff;">
                                  <tbody>
                                    <tr>
                                      <td style="width: 50%; border-bottom:2pt solid #000; text-align: left;padding: 5px 0px;">
                                        <small style="font-size:10px; font-weight:400; text-transform: uppercase; color:#000">Payment Type</small>
                                      </td>
                                      <td style="width: 30%; border-bottom:2pt solid #000; text-align: left;padding: 5px 0px;">
                                        <small style="font-size:10px; font-weight:400; text-transform: uppercase; color:#000">Date</small>
                                      </td>
                                      <td style="width: 20%; border-bottom:2pt solid #000; text-align: right;padding: 5px 0px;">
                                        <small style="font-size:10px; font-weight:400; text-transform: uppercase; color:#000">Amount</small>
                                      </td>
                                    </tr>

                                    <tr>
                                      <td style="border-top:1pt solid #000; text-align: left;padding: 5px 0px;" valign="top">Credit card</td>
                                      <td style="border-top:1pt solid #000; text-align: left;padding: 5px 0px;" valign="top">' . $detail["created_at"] . '</td>
                                      <td style="border-top:1pt solid #000; text-align: right;padding: 5px 0px; font-size:10px;" valign="top"><strong>' . $detail["total_amount"] . '</strong></td>
                                    </tr>

                                    <tr>
                                      <td style="border-top:2pt solid #000; border-bottom:2pt solid #000;">&nbsp;</td>
                                      <td style="border-top:2pt solid #000; border-bottom:2pt solid #000; text-align: left;padding: 5px 0px;">
                                        <small style="font-size:10px; font-weight:400; text-transform: uppercase; color:#000;">Total</small>
                                      </td>
                                      <td style="border-top:2pt solid #000; border-bottom:2pt solid #000; text-align: right;padding: 5px 0px;">
                                        <h3 style="color: #000;font-size:15px; margin:0;"><strong>' . $detail["total_amount"] . '</strong></h3>
                                      </td>
                                    </tr>
                                  </tbody>
                                </table>';

                    

            // $TOUR_ITEM_SUMMARY = '
            //         <table width="640" bgcolor="#ffffff" cellpadding="0" cellspacing="0" border="0" align="center" class="header_table" style="width:640px;">
            //             <tbody>
            //             <tr>
            //                 <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; text-align: left; padding: 30px 30px 15px; width:640px;">
            //                     <h3 style="font-size:19px"><strong>Item Summary</strong></h3>
            //                 </td>
            //             </tr>
            //             </tbody>
            //         </table>
        
            //         <table width="640" bgcolor="#ffffff" cellpadding="0" cellspacing="0" border="0" align="center" class="table" style="border-width:0 30px 30px; border-color: #fff; border-style: solid; background-color:#fff">
            //             <tbody>
            //                 <tr>
            //                     <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; width: 10%; border-bottom:2pt solid #000; text-align: left;padding: 5px 0px;">
            //                         <small style="font-size:10px; font-weight:400; text-transform: uppercase; color:#000">#</small>
            //                     </td>
            //                     <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; width: 50%; border-bottom:2pt solid #000; text-align: left;padding: 5px 0px;">
            //                         <small style="font-size:10px; font-weight:400; text-transform: uppercase; color:#000">Description</small>
            //                     </td>
            //                     <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; width: 20%; border-bottom:2pt solid #000; text-align: left;padding: 5px 0px;">
            //                         <small style="font-size:10px; font-weight:400; text-transform: uppercase; color:#000">
            //                             &nbsp;
            //                         </small>
            //                     </td>
            //                     <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; width: 20%; border-bottom:2pt solid #000; text-align: right;padding: 5px 0px;">
            //                         <small style="font-size:10px; font-weight:400; text-transform: uppercase; color:#000">Total</small>
            //                     </td>
            //                 </tr>

            //                 <tr>
            //                     <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; border-top:1pt solid #ddd; text-align: left;padding: 5px 0px;">
            //                         5
            //                     </td>
            //                     <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; border-top:1pt solid #ddd; text-align: left;padding: 5px 0px;">
            //                         Adult (13+)
            //                     </td>
            //                     <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; border-top:1pt solid #ddd; text-align: left;padding: 5px 0px;">
            //                         $42.95
            //                     </td>
            //                     <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; border-top:1pt solid #ddd; text-align: right;padding: 5px 0px;">
            //                         $214.75
            //                     </td>
            //                 </tr>

            //                 <tr>
            //                     <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; border-top:2pt solid #000;;padding: 5px 0px;padding: 5px 0px;">
            //                         &nbsp;
            //                     </td>
            //                     <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; border-top:2pt solid #000;;padding: 5px 0px;padding: 5px 0px;">
            //                         &nbsp;
            //                     </td>
            //                     <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; border-top:2pt solid #000; text-align: left;padding: 5px 0px;padding: 5px 0px;">
            //                         <small style="font-size:10px; font-weight:400; text-transform: uppercase; color:#000;">HST ON</small>
            //                     </td>
            //                     <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; border-top:2pt solid #000; text-align: right;padding: 5px 0px;padding: 5px 0px;">
            //                         $27.92
            //                     </td>
            //                 </tr>

            //                 <tr>
            //                     <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; ">
            //                         &nbsp;
            //                     </td>
            //                     <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; ">
            //                         &nbsp;
            //                     </td>
            //                     <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; border-top:2pt solid #000; text-align: left;padding: 5px 0px;">
            //                         <small style="font-size:10px; font-weight:400; text-transform: uppercase; color:#000;">Total</small>
            //                     </td>
            //                     <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; border-top:2pt solid #000; text-align: right;padding: 5px 0px;">
            //                         <h3 style="color:#000; margin:0; font-size:19px"><strong>$242.67</strong></h3>
            //                     </td>
            //                 </tr>
            //             </tbody>
            //         </table>';




            $TOUR_ITEM_SUMMARY = '';

            foreach ($order->orderTours as $order_tour) {
                $subtotal = 0;
                $_tourId = $order_tour->tour_id;
                $tour_pricing = !empty($order_tour->tour_pricing) ? json_decode($order_tour->tour_pricing, true) : [];
                $tour_extra = !empty($order_tour->tour_extra) ? json_decode($order_tour->tour_extra, true) : [];
                
                $TOUR_ITEM_SUMMARY .= '
                <table width="640" bgcolor="#ffffff" cellpadding="0" cellspacing="0" border="0" align="center" class="header_table" style="width:640px;">
                    <tbody>
                    <tr>
                        <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; text-align: left; padding: 30px 30px 15px; width:640px;">
                            <h3 style="font-size:19px"><strong>' . $order_tour->tour->title . ' - Item Summary</strong></h3>
                        </td>
                    </tr>
                    </tbody>
                </table>

                <table width="640" bgcolor="#ffffff" cellpadding="0" cellspacing="0" border="0" align="center" class="table" style="border-width:0 30px 30px; border-color: #fff; border-style: solid; background-color:#fff">
                    <tbody>
                        <tr>
                            <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; width: 10%; border-bottom:2pt solid #000; text-align: left;padding: 5px 0px;">
                                <small style="font-size:10px; font-weight:400; text-transform: uppercase; color:#000">#</small>
                            </td>
                            <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; width: 50%; border-bottom:2pt solid #000; text-align: left;padding: 5px 0px;">
                                <small style="font-size:10px; font-weight:400; text-transform: uppercase; color:#000">Description</small>
                            </td>
                            <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; width: 20%; border-bottom:2pt solid #000; text-align: left;padding: 5px 0px;">
                                &nbsp;
                            </td>
                            <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; width: 20%; border-bottom:2pt solid #000; text-align: right;padding: 5px 0px;">
                                <small style="font-size:10px; font-weight:400; text-transform: uppercase; color:#000">Total</small>
                            </td>
                        </tr>';
                
                // Pricing Rows
                $i = 1;
                foreach ($tour_pricing as $result) {
                    // $result = getTourPricingDetails($tour_pricing, $pricing->id);
                    $qty = $result['quantity'] ?? 0;
                    $price = $result['price'] ?? 0;
                    $total = $qty * $price;
                    if ($qty > 0) {
                        $subtotal += $total;
                        $TOUR_ITEM_SUMMARY .= '
                        <tr>
                            <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; border-top:1pt solid #ddd; text-align: left;padding: 5px 0px;">' . $qty . '</td>
                            <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; border-top:1pt solid #ddd; text-align: left;padding: 5px 0px;">' . ucwords($result['label']) . '</td>
                            <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; border-top:1pt solid #ddd; text-align: left;padding: 5px 0px;">' . price_format_with_currency($price, $order->currency) . '</td>
                            <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; border-top:1pt solid #ddd; text-align: right;padding: 5px 0px;">' . price_format_with_currency($total, $order->currency) . '</td>
                        </tr>';
                    }
                }

                // Extras Rows
                foreach ($tour_extra as $extra) {
                    // $result = getTourExtraDetails($tour_extra, $extra->id);
                    $qty = $extra['quantity'] ?? 0;
                    $price = $extra['price'] ?? 0;
                    $total = $qty * $price;
                    if ($qty > 0) {
                        $subtotal += $total;
                        $TOUR_ITEM_SUMMARY .= '
                        <tr>
                            <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; border-top:1pt solid #ddd; text-align: left;padding: 5px 0px;">' . $qty . '</td>
                            <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; border-top:1pt solid #ddd; text-align: left;padding: 5px 0px;">' . $extra['label'] . ' (Extra)</td>
                            <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; border-top:1pt solid #ddd; text-align: left;padding: 5px 0px;">' . price_format_with_currency($price, $order->currency) . '</td>
                            <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; border-top:1pt solid #ddd; text-align: right;padding: 5px 0px;">' . price_format_with_currency($total, $order->currency) . '</td>
                        </tr>';
                    }
                }

                // Taxes
                $taxRows = '';
                if ($order_tour->tour->taxes_fees) {
                    foreach ($order_tour->tour->taxes_fees as $tax) {
                        $taxAmount = get_tax($subtotal, $tax->fee_type, $tax->tax_fee_value);
                        $subtotal += $taxAmount;
                        $taxRows .= '
                        <tr>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; border-top:2pt solid #000; text-align: left;padding: 5px 0px;">
                                <small style="font-size:10px; font-weight:400; text-transform: uppercase; color:#000;">' . $tax->label . '</small>
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
                            <small style="font-size:10px; font-weight:400; text-transform: uppercase; color:#000;">Total</small>
                        </td>
                        <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; border-top:2pt solid #000; text-align: right;padding: 5px 0px;">
                            <h3 style="color:#000; margin:0; font-size:19px"><strong>' . price_format_with_currency($subtotal, $order->currency) . '</strong></h3>
                        </td>
                    </tr>
                    </tbody>
                </table>';
            }


                    
            $replacements = [
                "[[CUSTOMER_NAME]]"         => $customer->name ?? '',
                "[[CUSTOMER_EMAIL]]"        => $customer->email ?? '',
                "[[CUSTOMER_PHONE]]"        => $customer->name ?? '',

                "[[TOUR_TITLE]]"            => $tour->title ?? '',
                "[[TOUR_MAP]]"              => $tour->location->address ?? '',
                "[[TOUR_ADDRESS]]"          => $tour->location->address ?? '',
                "[[TOUR_PAYMENT_HISTORY]]"  => $TOUR_PAYMENT_HISTORY,
                "[[TOUR_ITEM_SUMMARY]]"     => $TOUR_ITEM_SUMMARY,
                "[[TOUR_TERMS_CONDITIONS]]"  => $tour->terms_and_conditions,

                "[[APP_LOGO]]"              => $logo,
                "[[APP_NAME]]"              => get_setting('site_name'),
                "[[COMPANY_NAME]]"          => get_setting('site_name'),
                "[[APP_URL]]"               => get_setting('app_url'),
                "[[APP_EMAIL]]"             => get_setting('app_email'),
                "[[APP_PHONE]]"             => get_setting('app_phone'),
                "[[APP_ADDRESS]]"           => get_setting('app_address'),
                "[[GOOGLE_INVITE]]"         => '<a href="https://calendar.google.com/calendar/render?action=TEMPLATE&text=Laravel+Project+Kickoff&dates=20250810T093000Z/20250810T103000Z&details=Join+Zoom+Meeting:+https://zoom.us/j/1234567890&location=Zoom&sf=true&output=xml" target="_blank">
  Add to Google Calendar
</a>',

                "[[ORDER_NUMBER]]"          => $order->order_number ?? '',
                "[[ORDER_STATUS]]"          => $order->status,
                "[[ORDER_TOUR_DATE]]"       => $order->order_tour->tour_date ? date('l, F j, Y', strtotime($order->order_tour->tour_date)) : '',
                "[[ORDER_TOUR_TIME]]"       => $order->order_tour->tour_time ? date('H:i A', strtotime($order->order_tour->tour_time)) : '',
                "[[ORDER_TOTAL]]"           => price_format_with_currency($order->total_amount, $order->currency) ?? '',
                "[[ORDER_BALANCE]]"         => price_format_with_currency($order->balance_amount, $order->currency) ?? '',
                "[[ORDER_BOOKING_FEE]]"     => price_format_with_currency($order->booking_fee, $order->currency) ?? '',
                "[[ORDER_CREATED_DATE]]"    => date('M d, Y', strtotime($order->created_at)) ?? '',
            ];


            Log::info('order_email_sentqwwqdwq' . 477);
            $body = strtr($template, $replacements);
            $footer = strtr($template_footer, $replacements);
            $subject = strtr($template_subject, $replacements);

            Log::info('order_mail_send' . $customer->email);
            $mailSend = self::order_mail_send($customer->email,$subject, $header,  $body, $footer);

            return response()->json([
                    'success' => false,
                    'message' => $mailSend
                ], 404);
 

            if($mailSend){
                return true;
            } else {
                return false;
            }
        }
        catch(\Exception $e){
            Log::info('order_email_sentqwwqdwq' . 498);
            Log::info($e);
            return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 404);
        }
 
    }

    public static function order_mail_send($email,$subject, $header,  $body, $footer)
    {
        
        if (env('MAIL_FROM_ADDRESS') != null) {
            $array['view'] = 'emails.newsletter';
            $array['subject'] = $subject;
            $array['header'] = $header;
            $array['from'] = env('MAIL_FROM_ADDRESS');
            $array['content'] =  $header.$body.$footer;
 
            try {
                if(Mail::to($email)->queue(new EmailManager($array))){
                    return true;
                    return response()->json(['status' => 'success']);
                }else{
                     return false;
                     return response()->json(['status' => 'Failed']);
                }
                 
            } catch (\Exception $e) {

                return response()->json([
                    'success' => false,
                    'message' => 'wefwfew'
                ], 404);
                dd($e);
            }
        }
       
    }
}
