<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Addon;
use App\Models\Order;
use App\Models\OrderActions;
use App\Models\OrderCustomer;
use App\Models\OrderMeta;
use App\Models\OrderPayment;
use App\Models\OrderTour;
use App\Models\PickupLocation;
use App\Models\Promo;
use App\Models\ScheduleDeleteSlot;
use App\Models\Tour;
use App\Models\TourPricing;
use App\Models\TourSchedule;
use App\Models\TourScheduleRepeats;
use App\Models\TourSpecialDeposit;
use App\Services\CheckoutDiscountService;
use App\Services\CheckoutTaxService;
use App\Services\CheckoutTotalService;
use App\Services\OrderPaymentSummaryService;
use App\Services\PricingService;
use App\Services\StripeIdempotencyService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Stripe\Customer;
use Stripe\Stripe;

class OrderController extends Controller
{
    /**
     * Summary of index
     * @param Request $request
     * @param mixed $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request, $id = 0)
    {
        if( !$id || $id == 0 ) {
            return response()->json([
                'message'    => 'User not found!',
                'status'    => false,
                'data'  => $request->all()
            ]);
        }

        $session_id = $request->input('session_id');

        $query = Order::where(function ($q) use ($id, $session_id) {
                $q->where('user_id', $id);

                if($session_id) {
                    $q->orWhere('session_id', $session_id);
                }
            })
            ->orderBy('created_at', 'DESC');

        //dd($query->toSql());
        //dd( getFullSql($query) );
        $orders = $query->paginate(10);

        $items = [];
        foreach ($orders->items() as $o) {
            $tours = '';
            foreach ($o->orderTours as $order_tour) {
                $tours.='<p><a href="https://tourbeez.com/tour/'. $order_tour->tour?->slug .'" target="_blank" class="alink">'.$order_tour->tour?->title.'</a></p>';
            }

            $items[] = [
                'id'  => $o->id,
                'order_number'  => $o->order_number,
                'title'         => $tours,
                'status'        => order_status($o->order_status),
                'total_amount'  => $o->total_amount,
                'created_at'    => date__format($o->created_at)
            ];
        }

        return response()->json([
            'orders'    => $items,
            'status'    => true,
        ]);
    }

    /**
     * Summary of view
     * @param Request $request
     * @param mixed $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function view(Request $request, $id = 0)
    {
        if( !$id || $id == 0 ) {
            return response()->json([
                'message'    => 'Order not found!',
                'status'    => false,
                'data'  => $request->all()
            ]);
        }

        //try {

            $cacheKey = 'booking_order_' . $id;

            // Try retrieving from cache or load and store it
            $booking = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($id) {
                return Order::with([
                    'tour',
                    'tour.location',
                    'tour.detail',
                    'customer'
                ])->findOrFail($id);
            });

            // if ($booking && $booking->order_status !== 1) {
            //     $booking->order_status   = 1;
            //     $booking->payment_status = 1;
            //     $booking->payment_method = $paymentIntent->payment_method_types[0] ?? 'card';
            //     $booking->updated_at     = now();
            //     $booking->save();

            //     // Refresh cache after updating the order
            //     // Cache::put($cacheKey, $booking->fresh(['tour.location', 'tour.detail', 'tour.addons', 'tour.fees', 'tour.pickups', 'customer']), now()->addMinutes(10));
            // }
            
            $tour_pricing = $booking->order_tour->tour_pricing ? json_decode($booking->order_tour->tour_pricing) : [];
            $pricing=[]; $total = 0;
            foreach($tour_pricing as $tp) {
                $tourPricing = TourPricing::find($tp->tour_pricing_id);
                $total = ($tp->quantity * $tp->price);
                $pricing[] = [
                    'lable' => $tourPricing->label,
                    'qty'   => $tp->quantity,
                    'actual_price' => isset($tp->actual_price) ? $tp->actual_price : $tp->price,
                    'discount' => isset($tp->discount) ? $tp->discount : 0,
                    'price' => $tp->price,
                    'total' => $total
                ];
            }

            $extra_pricing = $booking->order_tour->tour_extra ? json_decode($booking->order_tour->tour_extra) : [];
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

            $fees_pricing = json_decode($booking->order_tour->tour_fees);
            $fees = [];
            if (!empty($fees_pricing) && is_array($fees_pricing)) {
                foreach ($fees_pricing as $fp) {
                    $labelText = isset($fp->type) && $fp->type == 'PERCENT' ? ' (' . $fp->value . '%)' : ' (' . $fp->value . ')';
                    $labelText = $fp->label . $labelText;

                    $fees[] = [
                        'lable' => $labelText, // fixed spelling
                        'price' => $fp->price,
                        'total' => $fp->price
                    ];
                }
            }

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

            $image = uploaded_asset($booking->tour->main_image->id ?? 0, 'medium');
            $pickName = '';
            if($booking->customer && $booking->customer->pickup_name){
                $pickName = $booking->customer->pickup_name;
            } elseif($booking->customer && $booking->customer->pickup_id) {
                $pickLocation = PickupLocation::find($booking->customer->pickup_id);
                $pickName = $pickLocation->location . " - " . $pickLocation->address . " - " . $pickLocation->time;
            }

            /* If already partially paid or added discount/promo etc in backend */
            $paidAmount = $booking->payments()
                        ->where('status', 'succeeded')
                        ->where('payment_type', '<>', 'PROMOCODE')
                        ->sum('amount');
                
            $promoCode  = $booking->payments()
                        ->where('status', 'succeeded')
                        ->where('payment_type', 'PROMOCODE')
                        ->sum('amount');    

            $totalPaid  = $paidAmount + $promoCode;    

            $detail = [
                'order_number'      => $booking->order_number,
                'number_of_guests'  => $booking->number_of_guests,
                'paid_amount'       => $paidAmount ?? 0,
                'promo_code'        => $promoCode ?? 0,
                'total_paid'        => $totalPaid ?? 0,
                'total_amount'      => $booking->total_amount ?? 0,
                'balance_amount'    => $booking->balance_amount ?? 0,
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
                    'address'       => $booking->tour?->location->address,
                    'pricing'       => $pricing,
                    'extra'         => $extra,
                    'metas'         => $metas,
                    't_and_c'       => $booking->tour?->terms_and_conditions,
                    'fees'          => $fees,
                    'discount'      => $discounts,
                    'order_email'   => $booking->tour?->order_email,
                ],
            ];

            return response()->json([
                'status'  => 'succeeded',
                'booking' => $detail,
            ]);

        // } catch (\Exception $e) {
        //     return response()->json([
        //         'status'  => 'failed',
        //         'message' => $e->getMessage(),
        //     ], 500);
        // }
    }

    /**
     * Summary of getOrderDetailByOrderID
     * @param Request $request
     * @param mixed $orderID
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOrderDetailByOrderID( Request $request, $orderID )
    {
        try {
            $order = Order::find(decrypt($orderID));
        } catch (\Illuminate\Contracts\Encryption\DecryptException $exception) {
            // Customer checkout URLs use the order session ID as the path value.
            // A session can legitimately contain multiple orders, so the
            // fallback must select the newest cart instead of an older,
            // already-completed booking.
            $order = Order::where('session_id', $orderID)->latest('id')->first();
        }

        if (!$order) {
            return response()->json([
                'status' => false,
                'message' => 'Order not found.',
            ], 404);
        }

        $tour_pricing = $order->order_tour->tour_pricing ? json_decode($order->order_tour->tour_pricing) : [];
        $cartItems=[];
        foreach($tour_pricing as $tp) {
            $actual_price = isset($tp->actual_price) ? $tp->actual_price : $tp->price;
            $discount = isset($tp->discount) ? $tp->discount : 0;
            $cartItems[] = [
                'id'            => $tp->tour_pricing_id,
                'price'         => currencyConvert($tp->price, $order->currency, 'CAD'),
                'label'         => $tp->label,
                'price_type'    => $tp->price_type ?? '',
                'actual_price'  => currencyConvert( $actual_price, $order->currency, 'CAD'),
                'discount'      => currencyConvert( $discount, $order->currency, 'CAD'),
                'qty_used'      => 1,
                'quantity'      => $tp->quantity,
                'newly_added_quantity' => (int) ($tp->newly_added_quantity ?? 0),
                'is_newly_added' => (bool) ($tp->is_newly_added ?? false),
            ];
        }

        $paymentSummary = (new OrderPaymentSummaryService())->summarize(
            $order->payments()->get()
        );
        $promoAmount = $paymentSummary['promo_discount'];
        $discountAmount = $paymentSummary['special_discount'];
        

        $extra_pricing = $order->order_tour->tour_extra ? json_decode($order->order_tour->tour_extra) : [];
        $cartAdons=[]; $total = 0;
        foreach($extra_pricing as $ep) {
            $extraAddon = Addon::find($ep->tour_extra_id);
            $cartAdons[] = [
                "id"        => $ep->tour_extra_id,
                "price"     => currencyConvert($ep->price, $order->currency, 'CAD'),
                "label"     => $extraAddon->name,
                "quantity"  => $ep->quantity,
                "newly_added_quantity" => (int) ($ep->newly_added_quantity ?? 0),
                "is_newly_added" => (bool) ($ep->is_newly_added ?? false),
            ];
        }
         
        $tour_fees = $order->order_tour->tour_fees ? json_decode($order->order_tour->tour_fees) : [];
        $tourFees = [];

        if($tour_fees){
            foreach($tour_fees as $tf) {
                $tourFees[] = [
                    "id"    => $tf->tour_taxes_id,
                    "label" => $tf->label ?? '',
                    "type"  => $tf->type ?? '',
                    "value" => $tf->value ?? 0,
                    "calculated_amount" => (float) ($tf->price ?? 0),
                    "newly_added_amount" => (float) ($tf->newly_added_amount ?? 0),
                ];
            }
        }
        

        $tourPickups = [];
        if(!empty($order->tour->pickups) && isset($order->tour->pickups[0]) && $order->tour->pickups[0]?->name === 'No Pickup') {
            $tourPickups[] = 'No Pickup';
        }
        else if(!empty($order->tour->pickups) && isset($order->tour->pickups[0]) && $order->tour->pickups[0]?->name === 'Pickup') {
            $tourPickups[0] = 'Pickup';

            $comment = \DB::table('pickup_tour')
                        ->where('tour_id', $order->tour->id)
                        ->where('pickup_id', $order->tour->pickups[0]?->id)  // a single pickup ID
                        ->value('comment');


            $tourPickups[1] = $comment ?? "Enter the pickup location";
        }
        else if (!empty($order->tour->pickups) && isset($order->tour->pickups[0])) {
            $tourPickups = $order->tour->pickups[0]?->locations ?? [];
        }
        else {
            $tourPickups[] = 'No Pickup';
        }

        $customer = $order->customer;
        $image  = uploaded_asset($order->tour->main_image->id ?? 0, 'medium');   
        
        $paidAmount = $paymentSummary['paid_amount'];
        $bookingFee = $paymentSummary['booking_fee'];
        $totalPaid = $paymentSummary['total_credits'];
        $grossAmount = round((float) $order->orderTours()->sum('total_amount'), 2);
        $balanceAmount = round(max($grossAmount - $totalPaid, 0), 2);
        $isFullyPaid = $paidAmount > 0 && $balanceAmount <= 0.01;
        $depositRule = $order->tour->specialDeposit;
        $savedDiscount = collect(json_decode($order->order_tour->discount ?: '[]', true) ?: [])->first();
        if ($paidAmount > 0 && $depositRule && $savedDiscount) {
            $depositRule = clone $depositRule;
            $depositRule->discount_type = $savedDiscount['type'] ?? $depositRule->discount_type;
            $depositRule->discount_value = $savedDiscount['discount'] ?? $depositRule->discount_value;
        }
        
        $payment_intent_id = false;
        if(!empty($order->payment_intent_id) && str_contains( $order->payment_intent_id, 'seti_') && str_contains( $order->payment_method_id, 'pm_')){
            $payment_intent_id = true;
        }
        else if(!empty($order->payment_intent_id) && str_contains( $order->payment_intent_id, 'pi_') && str_contains( $order->payment_method_id, 'pm_')){
            $payment_intent_id = true;
        }

        $data = [
            "order_number"      => $order->order_number,
            "source"            => $order->source,
            "currency"          => $order->currency,
            'payment_status'    => $totalPaid > 0 ? 'paid' : 'unpaid',
            "total_amount"      => $balanceAmount,
            "balance_amount"    => $balanceAmount,
            "authorized_amount" => $paymentSummary['authorized_amount'],
            "is_payment_authorized" => $paymentSummary['authorized_amount'] > 0,
            "gross_amount"      => currencyConvert($grossAmount, $order->currency, 'CAD'),
            "is_fully_paid"     => $isFullyPaid,
            "promo_code"        => currencyConvert( $promoAmount, $order->currency, 'CAD'),
            "promo_code_value"  => $paymentSummary['promo_code'],
            "paid_amount"       => currencyConvert( $paidAmount, $order->currency, 'CAD'),
            "total_paid"        => currencyConvert( $totalPaid, $order->currency, 'CAD'),
            'payment_by'        => 'customer',
            "orderId"           => $order->id,
            "tourId"            => $order->tour_id,
            "tourTitle"         => $order->tour?->title,
            "tourSlug"          => $order->tour?->slug,
            "tourImage"         => $image,
            "selectedDate"      => $order->order_tour->tour_date,
            "selectedTime"      => $order->order_tour->tour_time,
            "tourPrice"         => currencyConvert($grossAmount, $order->currency, 'CAD'),
            "sessionId"         => $order->session_id ?? strtotime('now'),
            "userId"            => $order->user_id ?? 0,
            "minQty"            => $order->tour->detail->quantity_min,
            "maxQty"            => $order->tour->detail->quantity_max,
            "tourFees"          => $tourFees,
            "tourPickups"       => $tourPickups,
            "customer"          => $customer,
            "cartItems"         => $cartItems,
            "cartAdons"         => $cartAdons,
            "deposite_rule"     => $depositRule,
            "action_name"       => $order->action_name,
            "free_cancellation" => $order->tour->detail->free_cancellation,
            "exceptional_deal"  => $order->tour->detail->exceptional_deal,
            "lowest_price"      => $order->tour->detail->lowest_price,
            "kids_discount"     => $order->tour->detail->kids_discount,
            "full_refund"       => $order->tour->detail->full_refund,
            "isAlreadyProfile"  => $payment_intent_id,


        ];



        return response()->json([
            'status' => true,
            'data' => $data,
        ], 200);
    }
    

    /**
     * Summary of add_to_cart
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function add_to_cart(Request $request) 
    {
        Log::info('add_to_cart');

        // orderLogAdvanced(null, 'cart', 'entry', 'info', 'Add to cart started');

        $validated = $request->validate([
            'tourId'                    => 'required|integer|exists:tours,id',
            'selectedDate'              => 'required|date_format:Y-m-d',
            'selectedTime'              => 'nullable',
            'cartItems'                 => 'required|array|min:1',
            'cartItems.*.id'            => 'required|integer',
            'cartItems.*.label'         => 'required|string|min:1',
            'cartItems.*.quantity'      => 'required|integer|min:1',
            'cartItems.*.price'         => 'required',
            'cartItems.*.actual_price'  => 'nullable',
        ]);

        if (!$validated) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed.',
                'errors' => $request->validator->errors() ?? []
            ], 422);
        }

        if($request->subTourId === null) {
            $tour = Tour::with(['pricings'])->where('id', $request->tourId)->first();
            if(!$tour) {
                return response()->json([
                    'status' => false,
                    'message' => 'Tour not found.'
                ], 404);
            }
        }
        else if($request->tourId && $request->subTourId) {
            $tour = Tour::with(['pricings'])->where('id', $request->subTourId)->first();
            if(!$tour) {
                return response()->json([
                    'status' => false,
                    'message' => 'Sub tour not found.'
                ], 404);
            }
        }

        $order = Order::updateOrCreate(
        [
            'id' => $request->orderId ?? null, // condition: check if orderId exists
        ],[
            'tour_id'       => $request->tourId,
            'sub_tour_id'   => $request->subTourId ?? 0,
            'user_id'       => $request->userId ?? 0,
            'session_id'    => $request->sessionId, // optional if using guest carts
            'order_number'  => unique_order(),
            'currency'      => $request->currency,
            'total_amount'  => $request->tourPrice,
            'action_name'   => $request->btnAction,
            'order_status'  => 1,
            'created_at'    => date('Y-m-d H:i:s'),
            'updated_at'    => date('Y-m-d H:i:s'),
        ]);

        if($order) {
            orderLogAdvanced($order, 'cart', 'entry', 'info', 'Add to cart started');
            orderLogAdvanced($order, 'cart', 'order_created', 'success', 'Order created/updated');

            $orderId = $order->id;
            $quantity = 0;
            $pricing = [];
            $extra = [];
            $fees = [];
            $addon_price  = 8;
            $extra_price  = 0;
            $item_total=0;

            foreach ($validated['cartItems'] as $item) {

                if(isset($item['id']) && isset($item['quantity'])) {

                    $price          = floatval($item['price']);
                    $actual_price   = isset($item['actual_price']) ? floatval($item['actual_price']) : $price;
                    $qty            = intval($item['quantity']);

                    $item_price  = $tour->price_type == 'PER_PERSON' ? $price * $qty : $price;
                    $item_total += $item_price;
                    $quantity   += $qty;

                    $pricing[] = [
                        'tour_id'           => $request->subTourId ?? $request->tourId,
                        'tour_pricing_id'   => $item['id'],
                        'label'             => $item['label'],
                        'price_type'        => $tour->price_type,
                        'actual_price'      => round($actual_price, 2),
                        'price'             => round($price, 2),
                        'quantity'          => $item['quantity'],
                        'total_price'       => round($item_price, 2)
                    ];
                }
                
            }

            if( isset($request->cartAddons) && !empty($request->cartAddons) ) {
                foreach ($request->cartAddons as $addon) {
                    if(isset($addon['id']) && isset($addon['quantity'])) {

                        $price  = floatval($addon['price']);
                        $qty    = intval($addon['quantity']);

                        $extra_price  = $price * $qty;
                        $item_total  += $extra_price;

                        $extra[] = [
                            'tour_id'           => $request->subTourId ?? $request->tourId,
                            'tour_extra_id'     => $addon['id'],
                            'quantity'          => $addon['quantity'],
                            'label'             => $addon['label'],
                            'price'             => round($addon['price'], 2),
                            'total_price'       => round($extra_price, 2)
                        ];
                    }
                }
            }

            if( isset($request->tourFees) && !empty($request->tourFees) ) {
                foreach ($request->tourFees as $fee) {
                    if(isset($fee['id']) && isset($fee['value'])) {

                        $type  = ($fee['type']);
                        $value = is_numeric($fee['value']) ? intval($fee['value']) : 0;

                        $tax_fee    = $type === "PERCENT" ? ($item_total * $value)/100 : $value;
                        $item_total+= $tax_fee;

                        $fees[] = [
                            'tour_id'           => $request->subTourId ?? $request->tourId,
                            'tour_taxes_id'     => $fee['id'],
                            'label'             => $fee['label'],
                            'type'              => $type,
                            'value'             => $value,
                            'price'             => round($tax_fee, 2),
                        ];
                    }
                }
            }

            orderLogAdvanced($order, 'cart', 'calculation_done', 'info', 'Cart calculated', [
                'total' => $item_total,
                'guests' => $quantity
            ]);

            OrderTour::updateOrCreate(
                [
                    'order_id' => $orderId
                ],
                [
                    'order_id'          => $orderId,
                    'tour_id'           => $request->subTourId ?? $request->tourId, // mandatory
                    'tour_date'         => $validated['selectedDate'],
                    'tour_time'         => $validated['selectedTime'] ?? null,
                    'tour_pricing'      => json_encode($pricing),
                    'tour_extra'        => json_encode($extra),
                    'tour_fees'         => json_encode($fees),
                    'number_of_guests'  => $quantity,
                    'total_amount'      => round($item_total, 2),
                ]
            );

            $order->number_of_guests = $quantity;
            $order->total_amount = round($item_total, 2);
            $order->save();

            orderLogAdvanced($order, 'cart', 'completed', 'success', 'Cart updated');

            return response()->json([
                'status'        => true,
                'message'       => 'Item added in cart',
                'orderId'       => encrypt($orderId),
                'data'          => $order,
                'data_detail'   => $order->orderTours
            ], 200);
        }

        orderLogAdvanced(null, 'cart', 'failed', 'error', 'Order not created');

        return response()->json([
                'status'    => false,
                'message'   => 'Item not added in cart',
            ], 401);
    }

    private function savePromo($request) {
        Log::info('savePromo');
        orderLogAdvanced(null, 'promo', 'start', 'info', 'Promo validation started', [
            'promo_code' => $request->promo_code
        ]);
        if ($request->filled('promo_code')) {
            $promo = Promo::where('code', $request->promo_code)->first();
            if (!$promo) {
                orderLogAdvanced(null, 'promo', 'invalid', 'failed', 'Invalid promo code', [
                    'promo_code' => $request->promo_code
                ]);
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'promo_code' => ['Invalid promo code.'],
                ]);
            }

            $today = Carbon::today();
            $tourDate = Carbon::parse($request->selectedDate)->startOfDay();
            $validDays = array_map('intval', $promo->valid_days ?? []);
            $invalidPromo = $promo->status !== 'ISSUED'
                || ($promo->issue_date && $today->lt(Carbon::parse($promo->issue_date)))
                || ($promo->expiry_date && $today->gt(Carbon::parse($promo->expiry_date)))
                || ($promo->travel_from_date && $tourDate->lt(Carbon::parse($promo->travel_from_date)))
                || ($promo->travel_to_date && $tourDate->gt(Carbon::parse($promo->travel_to_date)))
                || ($promo->max_uses && $promo->used_count >= $promo->max_uses)
                || (!empty($validDays) && !in_array($tourDate->isoWeekday(), $validDays, true))
                || ($promo->internal && strtolower((string) $request->source) !== 'internal');

            if ($invalidPromo) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'promo_code' => ['Promo code is not eligible for this booking.'],
                ]);
            }

            orderLogAdvanced(null, 'promo', 'applied', 'success', 'Promo applied', [
                'promo_id' => $promo->id,
                'promo_code' => $promo->code
            ]);

            return $promo;  
        }
        return null;
    }

    private function addPromo($request, Promo $promo, Order $order, $itemTotal) 
    {
        if ($promo->min_amount && $itemTotal < (float) $promo->min_amount) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'promo_code' => ["A minimum item subtotal of {$promo->min_amount} is required."],
            ]);
        }

        if (
            str_contains($promo->value_type, 'LIMITPRODUCT')
            && (int) $promo->product_id !== (int) $order->tour_id
        ) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'promo_code' => ['Promo code is not valid for this tour.'],
            ]);
        }

        if (str_contains($promo->value_type, 'LIMITCATEGORY')) {
            $matchesCategory = $order->tour
                ? $order->tour->categories()->whereKey($promo->category_id)->exists()
                : false;
            if (!$matchesCategory) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'promo_code' => ['Promo code is not valid for this tour category.'],
                ]);
            }
        }

        $discount = 0;

        if ($promo->value_type === 'VALUE_LIMITPRODUCT') {
            $discount = min($promo->voucher_value, $itemTotal);
        }
        else if ($promo->value_type === 'VALUE') {
            $discount = min($promo->voucher_value, $itemTotal);
        }
        else if ($promo->value_type === 'VALUE_LIMITCATEGORY') {
            $discount = min($promo->voucher_value, $itemTotal);
        }
        else if ($promo->value_type === 'PERCENT_LIMITPRODUCT') {
            $discount = ($itemTotal * $promo->value_percent) / 100;
        }
        else if ($promo->value_type === 'PERCENT') {
            $discount = ($itemTotal * $promo->value_percent) / 100;
        }
        else if ($promo->value_type === 'PERCENT_LIMITCATEGORY') {
            $discount = ($itemTotal * $promo->value_percent) / 100;
        }

        // item total
        $itemTotal = max(0, ($itemTotal - $discount));

        if($promo) {
            OrderPayment::updateOrCreate(
                [
                    'order_id'          => $order->id,
                    'payment_type'      => "PROMOCODE",
                    'collection_type'   => 'Inside',
                ],
                [
                    'order_id'          => $order->id,
                    'amount'            => $discount,
                    'currency'          => $order->currency,
                    'current_rate'      => $request->current_rate,
                    'payment_type'      => "PROMOCODE",
                    'status'            => 'discount',
                    'collection_type'   => 'Inside',
                    'collection_date'   => Carbon::parse(now())->format('Y-m-d'),
                    'payment_intent_id' => null,
                    'transaction_id'    => $promo->code,
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);            
        }        

        return [
            'success'   => true,
            'discount'  => round($discount, 2),
            'item_total' => round($itemTotal, 2),
        ];
    }

    private function deleteDiscountFromPayment($order_id) {
        Log::info('Start deleting discount');
        OrderPayment::where('order_id', $order_id)->where('payment_type', 'DISCOUNT')->where('status', 'discount')->delete();
        Log::info('Promo deleted');
    }

    private function deletePromoFromPayment($order_id) {
        Log::info('Start deleting promo');
        OrderPayment::where('order_id', $order_id)->where('payment_type', 'PROMOCODE')->where('status', 'discount')->delete();
        Log::info('Discount deleted');
    }

    private function saveCustomer($request, $order, $data) {

        orderLogAdvanced($order, 'customer', 'start', 'info', 'Saving customer', [
            'email' => $data['email'] ?? null
        ]);
        try {
            // Save or update customer
            $customer = OrderCustomer::where('order_id', $order->id)->first() ?? new OrderCustomer();

            $customer->order_id     = $order->id;
            $customer->user_id      = $request->userId ?? 0;
            $customer->first_name   = $data['first_name'];
            $customer->last_name    = $data['last_name'];
            $customer->email        = $data['email'];
            $customer->phone        = $data['phone'];
            $customer->instructions = isset($data['instructions']) ? $data['instructions'] : '';
            $customer->pickup_id    = isset($data['pickup_id']) ?  $data['pickup_id'] : 0;
            $customer->pickup_name  = isset($data['pickup_name']) ? ucwords($data['pickup_name']) : '';          
            $customer->promo_code   = $request->promo_code;          
            $customer->save();
            orderLogAdvanced($order, 'customer', 'saved', 'success', 'Customer saved', [
                'customer_id' => $customer->id
            ]);

            return $customer;
            } catch (\Exception $e) {

            orderLogAdvanced($order, 'customer', 'error', 'error', $e->getMessage());

            throw $e; // don't swallow — important
        }
    }

    private function saveStripeCustomer($request, $order, $data) {
        Log::info('saveStripeCustomer');
        if (!$order->stripe_customer_id) {
            $name = $data['first_name'].' '.$data['last_name'];

            $stripeCustomer = Customer::create([
                'name'  => $name,
                'email' => $data['email'],
                'phone' => $data['phone'],
            ]);
        }
        else {
            $stripeCustomer = Customer::retrieve($order->stripe_customer_id);
        }
        return $stripeCustomer;
    }

    public function stripeAmount($amount, $currency)
    {
        $zeroDecimalCurrencies = [
            'bif','clp','djf','gnf','jpy','kmf',
            'krw','mga','pyg','rwf','ugx',
            'vnd','vuv','xaf','xof','xpf'
        ];

        if (in_array(strtolower($currency), $zeroDecimalCurrencies)) {
            return (int) round($amount);
        }

        return (int) round($amount * 100);
    }

    /**
     * Summary of update_cart
     * @param Request $request
     * @param mixed $id
     * @throws \Exception
     * @return \Illuminate\Http\JsonResponse
     */
    public function update_cart(Request $request, $id)
    {
        Log::info('update_cart');

        orderLogAdvanced(null, 'cart', 'entry', 'info', 'Update cart started', [
            'order_id' => $id
        ]);

        $validated = $request->validate([
            'tourId'        => 'required|integer|exists:tours,id',
            'selectedDate'  => 'required|date_format:Y-m-d',
            'selectedTime'  => 'nullable',

            'cartItems'                 => 'required|array|min:1',
            'cartItems.*.id'            => 'required|integer',
            'cartItems.*.actual_price'  => 'nullable|numeric',
            'cartItems.*.price'         => 'required|numeric',
            'cartItems.*.discount'      => 'nullable|numeric',
            'cartItems.*.quantity'      => 'required|integer|min:1',
            'cartItems.*.total_price'   => 'required|numeric',
            'cartItems.*.label'         => 'required|string',
            'cartItems.*.price_type'    => 'required|string',

            'formData.first_name'       => 'required|string|max:255',
            'formData.last_name'        => 'required|string|max:255',
            'formData.email'            => 'required|email|max:255',
            'formData.phone'            => 'required|string|max:20',
            'formData.instructions'     => 'nullable|string|max:500',
            'formData.pickup_id'        => 'nullable|numeric',
            'formData.pickup_name'      => 'nullable|string|max:255',
            'formData.adv_deposite'     => 'nullable|string|max:255',
            'formData.is_discount'      => 'nullable|string|max:255',
            'formData.booking_fee'      => 'nullable|numeric|max:255',

        ]);

        orderLogAdvanced(null, 'cart', 'validation', 'success', 'Validation passed');

        $order = Order::find($id);
        if (!$order) {
            orderLogAdvanced(null, 'cart', 'order_fetch', 'failed', 'Order not found', [
                'order_id' => $id
            ]);
            return response()->json([
                'status' => false,
                'message' => 'Order not found.'
            ], 404);
        }

        $tour = Tour::with(['pricings'])->find($request->tourId);
        if (!$tour) {
            orderLogAdvanced($order, 'cart', 'tour_fetch', 'failed', 'Tour not found', [
                'tour_id' => $request->tourId
            ]);
            return response()->json([
                'status' => false,
                'message' => 'Tour not found.'
            ], 404);
        }
        orderLogAdvanced($order, 'cart', 'init', 'success', 'Order & Tour loaded');

        $checkoutLock = Cache::lock("checkout:update:{$order->id}", 45);
        if (!$checkoutLock->get()) {
            return response()->json([
                'status' => false,
                'message' => 'This checkout is already being processed. Please wait and try again.',
            ], 409);
        }
        
        try {

            $data = $request->input('formData');
            $adv_deposite = $data['adv_deposite'] ?? 0;
            $booking_fee = $data['booking_fee'] ?? 0;            
            $existingPaymentSummary = (new OrderPaymentSummaryService())->summarize(
                $order->payments()->get()
            );
            $hasSettledPayments = $existingPaymentSummary['paid_amount'] > 0;
            $previousTour = $order->order_tour;
            $previousPricing = collect(json_decode($previousTour?->tour_pricing ?: '[]', true))
                ->keyBy(fn ($item) => (int) ($item['tour_pricing_id'] ?? 0));
            $previousExtras = collect(json_decode($previousTour?->tour_extra ?: '[]', true))
                ->keyBy(fn ($item) => (int) ($item['tour_extra_id'] ?? 0));
            $previousFees = json_decode($previousTour?->tour_fees ?: '[]', true) ?: [];
            $previousDiscounts = json_decode($previousTour?->discount ?: '[]', true) ?: [];

            Stripe::setApiKey(env('STRIPE_SECRET'));

            orderLogAdvanced($order, 'payment', 'stripe_init', 'success', 'Stripe initialized');

            $promo = $request->filled('promo_code') ? $this->savePromo($request) : [];
            $customer = $this->saveCustomer($request, $order, $data);
            $stripeCustomer = $this->saveStripeCustomer($request, $order, $data);            

            orderLogAdvanced($order, 'cart', 'customer_ready', 'success', 'Customer & Stripe customer ready', [
                'stripe_customer_id' => $stripeCustomer->id ?? null
            ]);
            // Initialize
            $quantity   = 0;
            $pricing    = [];
            $extra      = [];
            $fees       = [];
            $discount   = $hasSettledPayments ? $previousDiscounts : [];
            $discounts  = [];
            $diskounts  = [];
            $item_total = 0;
            $promo_total= 0;

            $depositRule = TourSpecialDeposit::where('use_deposit', 1)
                ->where('tour_id', $tour->id)
                ->first();
            if (!$depositRule) {
                $depositRule = TourSpecialDeposit::where('type', 'global')->first();
            }

            $discountService = new CheckoutDiscountService();
            $specialDiscountEligible = $order->action_name !== 'reserve'
                && $request->action_name === 'book'
                && $discountService->isSpecialDiscountEligible(
                    $depositRule,
                    Carbon::parse($validated['selectedDate']),
                    Carbon::today()
                );
            $isInternalOrder = strtolower((string) ($request->source ?? $order->source)) === 'internal';
            $stripeIdempotency = new StripeIdempotencyService();
            $intentAttemptSequence = $order->payments()
                ->where('payment_type', 'CARD')
                ->count() + 1;

            $payment_intent_id = null;
            if(!empty($order->payment_intent_id) && str_contains( $order->payment_intent_id, 'seti_') && str_contains( $order->payment_method_id, 'pm_')){
                $payment_intent_id = $order->payment_method_id;
            }
            else if(!empty($order->payment_intent_id) && str_contains( $order->payment_intent_id, 'pi_') && str_contains( $order->payment_method_id, 'pm_')){
                $payment_intent_id = $order->payment_method_id;
            }
            else if(!empty($order->payment_intent_id) && str_contains( $order->payment_intent_id, 'seti_')){
                $payment_intent_id = $order->payment_intent_id;
            }

            orderLogAdvanced($order, 'payment', 'pi_detect', 'info', 'Existing PI checked', [
                'payment_intent_id' => $payment_intent_id
            ]);

            // Cart Items
            foreach ($validated['cartItems'] as $item) {
                $qty            = $item['quantity'] ?? 1;
                $storedPricing  = $tour->pricings->firstWhere('id', (int) $item['id']);
                if (!$storedPricing) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'cartItems' => ["Pricing option {$item['id']} does not belong to this tour."],
                    ]);
                }

                $actual_price = $isInternalOrder
                    ? (float) ($item['actual_price'] ?? $item['price'])
                    : currencyConvert(
                        (float) $storedPricing->price,
                        $tour->currency ?? 'CAD',
                        $request->currency ?? 'CAD'
                    );
                $discount_price = (!$hasSettledPayments && $specialDiscountEligible)
                    ? $discountService->specialDiscount($actual_price, $depositRule)
                    : 0;
                $previousQty = (int) data_get($previousPricing->get((int) $item['id']), 'quantity', 0);
                $discountQty = $hasSettledPayments ? 0 : $qty;
                $linePriceType = $storedPricing->pricing_type
                    ?? $item['price_type']
                    ?? $tour->price_type;
                $lineUnits = $linePriceType === 'PER_PERSON' ? max($qty, 1) : 1;
                $previousLineDiscount = (float) data_get(
                    $previousPricing->get((int) $item['id']),
                    'discount',
                    0
                ) * ($linePriceType === 'PER_PERSON' ? $previousQty : 1);
                $storedDiscountTotal = $hasSettledPayments
                    ? $previousLineDiscount
                    : $discount_price * $discountQty;
                $storedUnitDiscount = $storedDiscountTotal / $lineUnits;
                $price = max($actual_price - $storedUnitDiscount, 0);
                $total = $linePriceType === 'PER_PERSON'
                    ? $actual_price * $qty
                    : $actual_price;
                $item_total    += $total;
                $quantity      += $qty;

                $pricing[] = [
                    'tour_id'           => $request->subTourId ?? $request->tourId,
                    'tour_pricing_id'   => $item['id'],
                    'label'             => $storedPricing->label,
                    'price_type'        => $linePriceType,
                    'quantity'          => $qty,
                    'actual_price'      => $actual_price,
                    'price'             => $price,
                    'discount'          => round($storedUnitDiscount, 2),
                    'total_price'       => $total,
                    'newly_added_quantity' => (int) data_get(
                        $previousPricing->get((int) $item['id']),
                        'newly_added_quantity',
                        0
                    ),
                    'is_newly_added' => (bool) data_get(
                        $previousPricing->get((int) $item['id']),
                        'is_newly_added',
                        false
                    ),
                ];                
                
                if ($discount_price > 0 && $discountQty > 0) {
                    if ($depositRule && $depositRule->is_discount && $depositRule->charge === 'NONE') {

                        $discount[] = [
                            'tour_id'  => $request->subTourId ?? $request->tourId,
                            'label'    => 'Special Discount',
                            'type'     => $depositRule->discount_type,
                            'quantity' => $discountQty,
                            'discount' => $depositRule->discount_value ?? 0,
                            'price'    => $linePriceType === 'PER_PERSON'
                                ? round($discount_price * $discountQty, 2)
                                : round($discount_price, 2),
                        ];

                        // Insert in to 
                        $diskounts[] = [
                            'order_id'          => $order->id,
                            'amount'            => $linePriceType === 'PER_PERSON'
                                ? round($discount_price * $discountQty, 2)
                                : round($discount_price, 2),
                            'currency'          => $order->currency,
                            'current_rate'      => $request->current_rate,
                            'payment_type'      => "DISCOUNT",
                            'status'            => 'discount',
                            'collection_type'   => 'Inside',
                            'collection_date'   => Carbon::parse(now())->format('Y-m-d'),
                            'payment_intent_id' => null,
                            'transaction_id'    => 'Special Discount',
                            'created_at'        => now(),
                            'updated_at'        => now(),
                        ];
                    }
                }
            }
            $cartItemsTotal = $item_total;

            /* If $diskounts provided */
            $cartDiskount = 0;
            if(!empty($diskounts)) {
                foreach ($diskounts as $diskount) {
                    $cartDiskount += $diskount['amount'];
                }
                $discountPayment = $diskounts[0];
                $discountPayment['amount'] = round(
                    $cartDiskount + ($hasSettledPayments ? $existingPaymentSummary['special_discount'] : 0),
                    2
                );
                OrderPayment::updateOrCreate(
                    [
                        'order_id' => $discountPayment['order_id'],
                        'payment_type' => $discountPayment['payment_type'],
                    ],
                    $discountPayment
                );
                $cartDiskount = $discountPayment['amount'];
                $cartItemsTotal = max($cartItemsTotal - $cartDiskount, 0);
            }
            else if (!$hasSettledPayments) {
                Log::info('Discounts does not acceptable because it\'s not in date range');
                $this->deleteDiscountFromPayment($order->id);
            }

            /* IF Valid */
            $cartPromo = 0;
            $discountedItemsTotal = $cartItemsTotal;
            if ($hasSettledPayments && $existingPaymentSummary['promo_discount'] > 0) {
                // A redeemed promo is part of the settled order snapshot. Do
                // not recalculate it against newly added items.
                $cartPromo = $existingPaymentSummary['promo_discount'];
                $discountedItemsTotal = max($cartItemsTotal - $cartPromo, 0);
            }
            else if($promo && $request->filled('promo_code')) {
                Log::info('promo requested and available');
                ['success' => $success, 'discount' => $discountAmount, 'item_total' => $itemTotal] 
                        = $this->addPromo($request, $promo, $order, $cartItemsTotal);
                $cartPromo = $discountAmount;
                $discountedItemsTotal = $itemTotal;
            }
            else if($order && !$hasSettledPayments) {
                Log::info('promo not requested but available');
                $this->deletePromoFromPayment($order->id);
            }

            // Add-ons
            $cartAddons = 0;
            if (!empty($request->cartAddons)) {
                foreach ($request->cartAddons as $addon) {
                    if (isset($addon['id'], $addon['quantity'], $addon['price'], $addon['total_price'], $addon['label']) && $addon['quantity'] != 0) {
                        $storedAddon = $tour->addonsAll->firstWhere('id', (int) $addon['id']);
                        if (!$storedAddon) {
                            throw \Illuminate\Validation\ValidationException::withMessages([
                                'cartAddons' => ["Add-on {$addon['id']} does not belong to this tour."],
                            ]);
                        }
                        $addonPrice = $isInternalOrder
                            ? (float) $addon['price']
                            : currencyConvert(
                                (float) $storedAddon->price,
                                $storedAddon->currency ?? $tour->currency ?? 'CAD',
                                $request->currency ?? 'CAD'
                            );
                        $addonTotal = round($addonPrice * (int) $addon['quantity'], 2);

                        $extra[] = [
                            'tour_id'           => $request->subTourId ?? $request->tourId,
                            'tour_extra_id'     => $addon['id'],
                            'quantity'          => $addon['quantity'],
                            'label'             => $storedAddon->name,
                            'price'             => round($addonPrice, 2),
                            'total_price'       => $addonTotal,
                            'newly_added_quantity' => (int) data_get(
                                $previousExtras->get((int) $addon['id']),
                                'newly_added_quantity',
                                0
                            ),
                            'is_newly_added' => (bool) data_get(
                                $previousExtras->get((int) $addon['id']),
                                'is_newly_added',
                                false
                            ),
                        ];
                        $item_total += $addonTotal;
                        $cartAddons += $addonTotal;
                    }
                }
            }

            /*
             * Credits reduce the item portion first. Add-ons and the booking
             * fee are then added, and percentage taxes (for example HST) are
             * calculated from that remaining taxable amount.
             */
            $paymentSummaryForTax = (new OrderPaymentSummaryService())->summarize(
                $order->payments()->get()
            );
            $paidBeforeTax = $paymentSummaryForTax['paid_amount'];
            $booking_fee = (float) ($data['booking_fee'] ?? 0);
            if ($booking_fee > 0 && get_setting('price_booking_fee')) {
                $bookingFeeType = get_setting('tour_booking_fee_type');
                if ($bookingFeeType === 'FIXED') {
                    $booking_fee = (float) get_setting('tour_booking_fee');
                } elseif ($bookingFeeType === 'PERCENT') {
                    $bookingFeeBase = $cartItemsTotal + $cartAddons;
                    $booking_fee = $bookingFeeBase * (float) get_setting('tour_booking_fee') / 100;
                }
            }

            // Cart Fees
            $cartFees = 0;
            if ($hasSettledPayments) {
                foreach ($previousFees as $fee) {
                    $feePrice = (float) ($fee['price'] ?? $fee['total'] ?? 0);
                    $fees[] = $fee;
                    $item_total += $feePrice;
                    $cartFees += $feePrice;
                }
            } elseif ($isInternalOrder && !empty($request->cartFees)) {
                foreach ($request->cartFees as $fee) {
                    if (isset($fee['id'], $fee['value'], $fee['label'])) {
                        $fees[] = [
                            'tour_id'           => $request->subTourId ?? $request->tourId,
                            'tour_taxes_id'     => $fee['id'],
                            'label'             => $fee['label'],
                            'type'              => $fee['type'],
                            'value'             => $fee['value'],
                            'price'             => $fee['price'],
                        ];
                        $item_total += floatval($fee['price']);
                        $cartFees += floatval($fee['price']);
                    }
                }
            } elseif (!$isInternalOrder) {
                $taxService = new CheckoutTaxService();
                $totalService = new CheckoutTotalService();
                $taxableSubtotal = $totalService->taxableSubtotal(
                    $discountedItemsTotal,
                    $paidBeforeTax,
                    $cartAddons,
                    $booking_fee
                );

                foreach ($tour->taxes_fees_resolved as $fee) {
                    $feeValue = $fee->fee_type === 'FIXED_PER_ORDER'
                        ? currencyConvert(
                            (float) $fee->tax_fee_value,
                            'USD',
                            $request->currency ?? 'CAD'
                        )
                        : (float) $fee->tax_fee_value;
                    $feePrice = $taxService->calculate(
                        $taxableSubtotal + $cartFees,
                        $fee->fee_type,
                        $feeValue
                    );

                    $fees[] = [
                        'tour_id' => $request->subTourId ?? $request->tourId,
                        'tour_taxes_id' => $fee->id,
                        'label' => $fee->label,
                        'type' => $fee->fee_type,
                        'value' => round($feeValue, 2),
                        'price' => $feePrice,
                    ];
                    $item_total += $feePrice;
                    $cartFees += $feePrice;
                }
            }

            $order_tour_data = [
                'tour_id'           => $request->subTourId ?? $request->tourId,
                'order_id'          => $order->id,
                'tour_date'         => $validated['selectedDate'],
                'tour_pricing'      => json_encode($pricing ?? []),
                'tour_extra'        => json_encode($extra ?? []),
                'tour_fees'         => json_encode($fees ?? []),
                'discount'          => json_encode($discount ?? []),
                'number_of_guests'  => $quantity,
                'total_amount'      => $item_total,
            ];

            orderLogAdvanced($order, 'cart', 'before_payment_logic', 'info', 'Starting payment logic', [
                'action' => $request->action_name
            ]);

            OrderTour::updateOrCreate(
                ['order_id' => $order->id],
                $order_tour_data
            );

            // Save Order Metas
            if (!empty($request->cartFees)) {
                foreach ($request->cartFees as $fee) {
                    if (
                        isset($fee['name'], $fee['value'])
                        && strtolower((string) $fee['name']) !== 'booking_fee'
                    ) {
                        OrderMeta::updateOrCreate(
                            [
                                'order_id' => $order->id,
                                'name'     => $fee['name'],
                            ],
                            ['value' => $fee['value']]
                        );
                    }
                }
            }

            if($booking_fee > 0 && get_setting('price_booking_fee')) {
                Log::info('booking fee', $booking_fee);
                OrderPayment::updateOrCreate(
                    [
                        'order_id'     => $order->id,
                        'payment_type' => "BOOKINGFEE"
                    ],
                    [
                        'order_id'          => $order->id,
                        'amount'            => $booking_fee ?? 0,
                        'currency'          => $order->currency,
                        'current_rate'      => $request->current_rate,
                        'payment_type'      => "BOOKINGFEE",
                        'status'            => 'succeeded',
                        'collection_type'   => 'Inside',
                        'collection_date'   => Carbon::parse(now())->format('Y-m-d'),
                        'payment_intent_id' => null,
                        'transaction_id'    => 'Fooking Fee',
                        'created_at'        => now(),
                        'updated_at'        => now(),
                    ]
                );

                // Booking fees are charges, not successful payment credits.
                $item_total += (float) $booking_fee;
            }

            // Keep the tour snapshot as the gross recalculated tour amount
            // (items + add-ons + booking fee + tax), before order-level credits.
            OrderTour::where('order_id', $order->id)->update([
                'total_amount' => round($item_total, 2),
            ]);

            $paymentSummary = (new OrderPaymentSummaryService())->summarize(
                $order->payments()->get()
            );
            $paidAmount = $paymentSummary['paid_amount'];
            $bookingFee = $paymentSummary['booking_fee'] ?: $booking_fee;
            $promoCode = $paymentSummary['promo_discount'];
            $discount = $paymentSummary['special_discount'];
            $totalPaid = $paymentSummary['total_credits'];

            $item_total = (new CheckoutTotalService())->outstandingTotal(
                $item_total,
                $totalPaid
            );
            Log::info("cartItemsTotal - $cartItemsTotal 
            -- cartDiscount - $cartDiskount 
            -- cartPromo - $cartPromo 
            -- cartAddons - $cartAddons 
            -- cartFees - $cartFees");
            Log::info("totalPaid - $paidAmount -- $promoCode -- $discount -- $bookingFee -- $item_total");

            // Final update to main order
            $previousOrderTotalAmount = $item_total;            

            $order->booking_fee         = $booking_fee;
            $order_actions_notes       = NULL;
            //$order->sub_tour_id        = $request->sub_tour_id;
            $order->action_name        = $request->action_name;
            $order->number_of_guests   = $quantity;
            $order->total_amount       = $item_total ?? 0;
            $order->balance_amount     = ($adv_deposite == 'deposit') ? $item_total : 0;
            $order->adv_deposite       = $adv_deposite;
            $order->currency           = $request->currency;
            $order->current_rate       = $request->current_rate;
            $order->source             = $request->filled('source')
                ? strtolower((string) $request->source)
                : ($order->source ?: 'tourbeez');
            $order->updated_at         = now();
            $order->save();

            $metaData = [
                'bookedDate'    => $order->created_at,
                'orderId'       => $order->id,
                'orderNumber'   => $order->order_number,
                'tourName'      => $tour->title,
                'tourDate'      => $tour->tour_date,
                'tourTime'      => $tour->tour_time,
                'customerId'    => $customer->id,
                'customerEmail' => $customer->email,
                'customerName'  => $customer->name,
                'planName'      => "TourBeez Plan",
                'status'        => 'Pending supplier',
                'source'        => $order->source,
                'totalAmount'   => $order->total_amount
            ];

            // A reserve (Book Now & Pay Later) always saves the customer's
            // payment method with a SetupIntent and must never create/confirm
            // a full-payment PaymentIntent. This is independent of whether the
            // tour's special-deposit rule is NONE, deposit, or full.
            if ($adv_deposite === "deposit" || $request->action_name === "reserve") {
                orderLogAdvanced($order, 'payment', 'deposit_mode', 'info', 'Deposit flow started');
                
                $chargeAmount = 0;              

                // Applying deposite rule if enabled
                if (isset($depositRule) && $depositRule->use_deposit) {
                    switch ($depositRule->charge) {
                        case 'FULL':
                            $chargeAmount = $order->total_amount;
                            break;

                        case 'DEPOSIT_PERCENT':
                            $chargeAmount = ($order->total_amount * ($depositRule->deposit_amount / 100));
                            break;

                        case 'DEPOSIT_FIXED':
                            $chargeAmount = $order->total_amount - $depositRule->deposit_amount;
                            break;

                        case 'DEPOSIT_FIXED_PER_ORDER':
                            $chargeAmount = $depositRule->deposit_amount; // per order, not per person
                            break;

                        case 'NONE':
                            $chargeAmount = $item_total;
                            break;
                    }

                    // ✅ Minimum notice check
                    if ($depositRule->use_minimum_notice && $depositRule->notice_days && $adv_deposite == "full") {
                        $daysUntilTour = \Carbon\Carbon::today()->diffInDays($tour->start_date, false);
                        if ($daysUntilTour < $depositRule->notice_days) {
                            $chargeAmount = $order->total_amount; // Force full
                        }
                    }
                    

                } else {
                    $chargeAmount = $order->total_amount;
                }

                // if ($request->filled('promo_code') && $promo) {

                //     $discountAmount = round(min($discountAmount, $item_total), 2);

                //     $chargeAmount = $chargeAmount - $discountAmount;
                // }

                // ✅ Update amounts in order
                if($request->action_name === "reserve"){
                    $chargeAmount = 0;
                }
                $order->booked_amount  = $chargeAmount;        // what’s being charged now
                $order->balance_amount = $order->total_amount - $order->booked_amount;

                //print_r($order); exit;
                if ($chargeAmount > 0) {

                    orderLogAdvanced($order, 'payment', 'pi_create', 'info', 'Creating PaymentIntent', [
                        'amount' => $chargeAmount
                    ]);
                    
                    $pi = isset($order->payment_intent_id) && !$payment_intent_id ? \Stripe\PaymentIntent::retrieve($order->payment_intent_id) : null;
                    if ($pi && $pi->status === 'requires_payment_method') {
                        $pi = \Stripe\PaymentIntent::update($pi->id, [
                            'amount' => $this->stripeAmount($chargeAmount, $order->currency),
                            'description' => '#' . $order->order_number . ' - ' . $tour->title,
                            'metadata' => $metaData,
                        ]);
                        $order->payment_intent_client_secret = $pi->client_secret;
                        $order->save();
                    } elseif(!$pi || ($pi->status !== "requires_capture" && $pi->status !== 'succeeded')) {
                        $pi = \Stripe\PaymentIntent::create([
                            'customer'  => $stripeCustomer->id,
                            'amount' => $this->stripeAmount($chargeAmount, $order->currency),
                            'currency' => $order->currency,
                            // 'receipt_email' => $data['email'],
                            'description' => '#' . $order->order_number . ' - ' . $tour->title,
                            'statement_descriptor_suffix' =>  $order->order_number,
                            'metadata'  => $metaData,
                            'capture_method' => 'manual',
                            'automatic_payment_methods' => ['enabled' => true],
                            'setup_future_usage'=> 'off_session',
                        ], [
                            'idempotency_key' => $stripeIdempotency->checkoutIntentKey(
                                $order->id,
                                'payment',
                                $adv_deposite,
                                $chargeAmount,
                                $order->currency,
                                $intentAttemptSequence
                            ),
                        ]);
                        orderLogAdvanced($order, 'payment', 'pi_created', 'success', 'PaymentIntent created', [
                            'pi' => $pi->id
                        ]);

                        $order->payment_intent_client_secret = $pi->client_secret;
                        $order->payment_intent_id = $pi->id;
                        $order->save(); 

                        //$order_payment = OrderPayment::create([
                        $order_payment = OrderPayment::updateOrCreate([
                            'order_id'          => $order->id,
                            'payment_intent_id' => $pi->id,
                            'action'            => $adv_deposite,
                            
                        ],[
                            'order_id'          => $order->id,
                            'amount'            => $chargeAmount, // ($adv_deposite == 'deposit') ? $chargeAmount : $order->total_amount,
                            'currency'          => $order->currency,
                            'current_rate'      => $request->current_rate,
                            'status'            => 'pending', // manual capture pending
                            'action'            => $adv_deposite,
                            'payment_type'      => "CARD",
                            'payment_intent_id' => $pi->id,
                            'transaction_id'    => null, // no charge yet until capture
                            'response_payload'  => json_encode($pi),
                        ]);
                    }
                    
                    // Retrieve card details from payment method if available
                    try {
                        $retrievedIntent = \Stripe\PaymentIntent::retrieve($pi->id);
                        if (
                            !empty($retrievedIntent->payment_method) &&
                            str_contains($retrievedIntent->payment_method, 'pm_')
                        ) {
                            $paymentMethod = \Stripe\PaymentMethod::retrieve(
                                $retrievedIntent->payment_method
                            );
                            $cardDetails = [];

                            if (
                                isset($paymentMethod->card) &&
                                $paymentMethod->type === 'card'
                            ) {

                                $cardDetails = [
                                    'type'      => $paymentMethod->type ?? null,
                                    'brand'     => $paymentMethod->card->brand ?? null,
                                    'last4'     => $paymentMethod->card->last4 ?? null,
                                    'exp_month' => $paymentMethod->card->exp_month ?? null,
                                    'exp_year'  => $paymentMethod->card->exp_year ?? null,
                                ];

                                $order->card_info = json_encode($cardDetails);
                                $order->save();
                            }

                            \Log::warning('PaymentIntent uncaptured - ' . $order->order_number . ' - ' . $pi->id);
                            
                            $order_payment = $order_payment ?? OrderPayment::where('payment_intent_id', $pi->id)->first();
                            OrderPayment::updateOrCreate([
                                'id' => $order_payment?->id
                            ], 
                            [
                                'status'            => 'pending',
                                'payment_method'    => $cardDetails['type'] ?? null,
                                'card_brand'        => $cardDetails['brand'] ?? null,
                                'card_last4'        => $cardDetails['last4'] ?? null,
                                'card_exp_month'    => $cardDetails['exp_month'] ?? null,
                                'card_exp_year'     => $cardDetails['exp_year'] ?? null,
                            ]);
                        } else {
                            \Log::warning( 'Payment method not attached yet for PI: ' . $retrievedIntent->id );
                        }                        
                    } catch (\Exception $cardError) {
                        \Log::warning('Unable to retrieve card details: ' . $cardError->getMessage());
                    }

                } else {
                    // No charge needed

                    orderLogAdvanced($order, 'payment', 'setup_intent', 'info', 'Preparing SetupIntent');
                    $si = null;
                    if (str_starts_with((string) $order->payment_intent_id, 'seti_')) {
                        $existingSetupIntent = \Stripe\SetupIntent::retrieve($order->payment_intent_id);
                        if ($existingSetupIntent->status === 'requires_payment_method') {
                            $si = \Stripe\SetupIntent::update($existingSetupIntent->id, [
                                'customer' => $stripeCustomer->id,
                                'metadata' => $metaData,
                            ]);
                        }
                    }

                    if (!$si) {
                        $si = \Stripe\SetupIntent::create([
                            'customer'  => $stripeCustomer->id,
                            'automatic_payment_methods' => [
                                'enabled' => true,
                            ],
                            'usage' => 'off_session',
                            'metadata'  => $metaData
                        ], [
                            'idempotency_key' => $stripeIdempotency->checkoutIntentKey(
                                $order->id,
                                'setup',
                                $adv_deposite,
                                0,
                                $order->currency,
                                $intentAttemptSequence
                            ),
                        ]);
                    }
                    orderLogAdvanced($order, 'payment', 'setup_created', 'success', 'SetupIntent created', [
                        'si' => $si->id
                    ]);               
                    $order->payment_intent_client_secret = $si->client_secret;
                    $order->payment_intent_id = $si->id;

                    \Log::warning('SetupIntent uncaptured - ' . $order->order_number . ' - ' . $si->id);
                    // OrderPayment::create([
                    OrderPayment::updateOrCreate([
                        'order_id'          => $order->id,
                        'payment_intent_id' => $si->id,
                        'action'            => $adv_deposite,
                        
                    ],[
                        'order_id'          => $order->id,
                        'payment_intent_id' => $si->id,
                        'transaction_id'    => null, // no charge yet until capture
                        'payment_method'    => 'card',
                        'payment_type'      => "CARD",
                        'card_brand'        => null,
                        'card_last4'        => null,
                        'card_exp_month'    => null,
                        'card_exp_year'     => null,
                        'amount'            => 0,
                        'currency'          => $order->currency,
                        'status'            => 'reserve', // manual capture pending
                        'action'            => $adv_deposite,
                        'response_payload'  => json_encode($si),
                    ]);
                }
            } else if($adv_deposite === "full") {

                orderLogAdvanced($order, 'payment', 'full_mode', 'info', 'Full payment flow');
                
                \Log::warning('full - ' . $order->order_number . ' - Stripe Customer: ' . $stripeCustomer->id);
                $order->booked_amount  = $order->total_amount;
                $order->balance_amount = 0;

                // ✅ Fetch and store card details (if available)
                try {
                    $pi = isset($order->payment_intent_id) ? \Stripe\PaymentIntent::retrieve($order->payment_intent_id) : null;
                    if(!$pi || ($pi->status !== "requires_capture" && $pi->status !== 'succeeded')) {
                        $pi = \Stripe\PaymentIntent::create([
                            'customer'  => $stripeCustomer->id,
                            'amount' => $this->stripeAmount($order->total_amount, $order->currency),
                            'currency' => $order->currency,
                            // 'receipt_email' => $data['email'],
                            'description' => '#' . $order->order_number . ' - ' . $tour->title,
                            'statement_descriptor_suffix' =>  $order->order_number,
                            'metadata'  => $metaData,
                            'automatic_payment_methods' => ['enabled' => true],
                            'capture_method' => 'manual',
                            'setup_future_usage'=> 'off_session',
                        ], [
                            'idempotency_key' => $stripeIdempotency->checkoutIntentKey(
                                $order->id,
                                'payment',
                                $adv_deposite,
                                $order->total_amount,
                                $order->currency,
                                $intentAttemptSequence
                            ),
                        ]);
                        orderLogAdvanced($order, 'payment', 'pi_created', 'success', 'Full payment PI created', [
                            'pi' => $pi->id
                        ]);

                        $order->payment_intent_client_secret = $pi->client_secret;
                        $order->payment_intent_id = $pi->id;
                        $order->save();

                        // $order_payment = OrderPayment::create([
                        $order_payment = OrderPayment::updateOrCreate([
                            'order_id'          => $order->id,
                            'payment_intent_id' => $pi->id,
                            'action'            => $adv_deposite,
                            
                        ],[
                            'order_id'          => $order->id,
                            'amount'            => $order->total_amount,
                            'currency'          => $order->currency,
                            'status'            => 'pending', // manual capture pending
                            'action'            => $adv_deposite,
                            'payment_intent_id' => $pi->id,
                            'payment_type'      => "CARD",
                            'transaction_id'    => null, // no charge yet until capture
                            'response_payload'  => json_encode($pi),
                        ]);
                    }
                    $cardDetails = [];
                    
                    $retrievedIntent = \Stripe\PaymentIntent::retrieve($pi->id);
                    if (
                        !empty($retrievedIntent->payment_method) &&
                        str_contains($retrievedIntent->payment_method, 'pm_')
                    ) {

                        $paymentMethod = \Stripe\PaymentMethod::retrieve(
                            $retrievedIntent->payment_method
                        );

                        if (
                            isset($paymentMethod->card) &&
                            $paymentMethod->type === 'card'
                        ) {

                            $cardDetails = [
                                'type'      => $paymentMethod->type ?? null,
                                'brand'     => $paymentMethod->card->brand ?? null,
                                'last4'     => $paymentMethod->card->last4 ?? null,
                                'exp_month' => $paymentMethod->card->exp_month ?? null,
                                'exp_year'  => $paymentMethod->card->exp_year ?? null,
                            ];

                            $order->card_info = json_encode($cardDetails);

                            $order->save();
                        }

                    } else {

                        \Log::warning(
                            'Payment method missing for full payment PI: ' .
                            $retrievedIntent->id
                        );
                    }

                    $order_payment = $order_payment ?? OrderPayment::where('payment_intent_id', $pi->id)->first();
                    OrderPayment::updateOrCreate(
                        ['id' => $order_payment?->id], 
                        [
                            'status'            => 'pending',
                            'payment_method'    => $cardDetails['type'] ?? null,
                            'card_brand'        => $cardDetails['brand'] ?? null,
                            'card_last4'        => $cardDetails['last4'] ?? null,
                            'card_exp_month'    => $cardDetails['exp_month'] ?? null,
                            'card_exp_year'     => $cardDetails['exp_year'] ?? null,
                        ]);
                    
                } catch (\Exception $cardError) {
                    \Log::warning('Unable to retrieve card details: ' . $cardError->getMessage());
                }
            } else if ($adv_deposite === "partial") {

                orderLogAdvanced($order, 'payment', 'partial_mode', 'info', 'Partial payment flow');

                \Log::warning('partial - ' . $order->order_number . ' - Stripe Customer: ' . $stripeCustomer->id);
                $paidAmount = $order->payments()
                    ->where('status', 'succeeded')
                    ->sum('amount');

                $order->total_amount = $previousOrderTotalAmount;
                $totalAmount  = $previousOrderTotalAmount ?? 0;
                // total_amount is already the outstanding amount after all
                // successful payments and discounts have been credited above.
                $chargeAmount = max($totalAmount, 0);
                $order->booked_amount = $chargeAmount;
                $order->balance_amount = 0;
                \Log::warning("Charge Amount - $chargeAmount");
                if ($chargeAmount <= 0) {
                    throw new \Exception('No remaining amount to charge.');
                }
                
                $pi = \Stripe\PaymentIntent::create([
                    'customer' => $stripeCustomer->id,
                    'amount'   => $this->stripeAmount($chargeAmount, $order->currency),
                    'currency' => $order->currency,
                    // 'receipt_email' => $data['email'],
                    'description' => '#' . $order->order_number . ' - ' . $tour->title . ' (Remaining Balance)',
                    'statement_descriptor_suffix' => $order->order_number,
                    'metadata' => $metaData,
                    'automatic_payment_methods' => ['enabled' => true],
                    'capture_method' => 'manual',
                    'setup_future_usage'=> 'off_session',
                ], [
                    'idempotency_key' => $stripeIdempotency->checkoutIntentKey(
                        $order->id,
                        'payment',
                        $adv_deposite,
                        $chargeAmount,
                        $order->currency,
                        $intentAttemptSequence
                    ),
                ]);

                orderLogAdvanced($order, 'payment', 'pi_created', 'success', 'Partial PI created', [
                    'pi' => $pi->id
                ]);

                // 3️⃣ Save PI details on order
                $order->payment_intent_client_secret = $pi->client_secret;
                $order->payment_intent_id = $pi->id;
                $cardDetails = [];

                // 4️⃣ Retrieve payment method details
                $retrievedIntent = \Stripe\PaymentIntent::retrieve($pi->id);
                if (
                    !empty($retrievedIntent->payment_method) &&
                    str_contains($retrievedIntent->payment_method, 'pm_')
                ) {

                    $paymentMethod = \Stripe\PaymentMethod::retrieve(
                        $retrievedIntent->payment_method
                    );

                    if (
                        isset($paymentMethod->card) &&
                        $paymentMethod->type === 'card'
                    ) {

                        $cardDetails = [
                            'type'      => $paymentMethod->type ?? null,
                            'brand'     => $paymentMethod->card->brand ?? null,
                            'last4'     => $paymentMethod->card->last4 ?? null,
                            'exp_month' => $paymentMethod->card->exp_month ?? null,
                            'exp_year'  => $paymentMethod->card->exp_year ?? null,
                        ];

                        $order->card_info = json_encode($cardDetails);
                        $order->save();
                    }

                } else {

                    \Log::warning(
                        'Payment method missing for partial payment PI: ' .
                        $retrievedIntent->id
                    );
                }               

                // 5️⃣ Store payment record
                // OrderPayment::create([
                OrderPayment::updateOrCreate([
                    'order_id'          => $order->id,
                    'payment_intent_id' => $pi->id,
                    'action'            => $adv_deposite,
                    
                ],[
                    'order_id'          => $order->id,
                    'payment_intent_id' => $pi->id,
                    'transaction_id'    => null, // no charge yet until capture
                    'payment_type'      => "CARD",
                    'payment_method'    => $cardDetails['type'] ?? 'card',
                    'card_brand'        => $cardDetails['brand'] ?? null,
                    'card_last4'        => $cardDetails['last4'] ?? null,
                    'card_exp_month'    => $cardDetails['exp_month'] ?? null,
                    'card_exp_year'     => $cardDetails['exp_year'] ?? null,
                    'amount'            => $chargeAmount,
                    'currency'          => $order->currency,
                    'status'            => 'pending', // manual capture pending
                    'action'            => $adv_deposite,
                    'response_payload'  => json_encode($pi),
                ]);

                $order_actions_notes = $customer->name." paid the remaining amount {$chargeAmount}";
            }

            $order->stripe_customer_id = $stripeCustomer->id;
            $order->save();

            // An internal order already exists before the customer opens the
            // payment link. Its action must be recorded only after Stripe
            // reports the actual payment result, not while preparing payment.
            if (strtolower((string) $order->source) !== 'internal') {
                $order_actions = [
                    'order_id'         => $order->id,
                    'performed_by'     => $customer->id,
                    'notes'            => $order_actions_notes ?? $customer->name." placed a new order {$order->order_number}",
                    'created_at'       => now(),
                    'updated_at'       => now()
                ];
                OrderActions::insert($order_actions);
            }

            orderLogAdvanced($order, 'cart', 'completed', 'success', 'Cart updated successfully');           

            return response()->json([
                'status'            => true,
                'message'           => 'Cart updated successfully',
                'data'              => $order,
                'data_detail'       => $order->orderTours,
                'stripe_customer_id'=> $order->stripe_customer_id,
                'payment_intent_id' => $order->payment_intent_id,
                'payment_intent_client_secret' => $order->payment_intent_client_secret,
                'setup_intent_client_secret' => str_starts_with(
                    (string) $order->payment_intent_id,
                    'seti_'
                ) ? $order->payment_intent_client_secret : null,
            ], 200);
        } catch (\Exception $e) {

            orderLogAdvanced($order ?? null, 'cart', 'error', 'failed', $e->getMessage(), [
                'line' => $e->getLine()
            ]);
            Log::error('Cart Update Error: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Cart Update Error: ' . $e->getMessage(),
            ], 500);
        } finally {
            $checkoutLock->release();
        }
    }

    /**
     * Summary of update_error
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update_error(Request $request) {
        Log::info('update_error');
         orderLogAdvanced($request->order_id, 'payment', 'update_error_start', 'info', 'Update error triggered', [
            'order_id' => $request->order_id
        ]);
        $validated = $request->validate([
            'order_id' => 'required|integer|exists:orders,id',
            'payment_intent_id' => 'required'
        ]);

        $order = Order::find($request->order_id);
        if (!$order) {
             orderLogAdvanced(null, 'payment', 'order_missing', 'failed', 'Order not found', [
                'order_id' => $request->order_id
            ]);
            return response()->json([
                'status' => false,
                'message' => 'Order not found.'
            ], 404);
        }

        $order->balance_amount     = $order->total_amount;
        $order->booked_amount       = 0;
        $order->updated_at         = now();
        $order->save();
        orderLogAdvanced($order, 'payment', 'reset', 'success', 'Payment reset after failure', [
            'payment_intent_id' => $request->payment_intent_id
        ]);

        return response()->json([
            'status'   => true,
            'message'  => 'Cart balance updated successfully',
            'data'     => $order,
        ], 200);

        $order_tour = $order->order_tour;
        $pricing = [];
        $discounts = [];
        $item_total = 0;
        $quantity = 0;
        // Cart Items

        $payment_intent_id = null;
        if(str_contains( $order->payment_intent_id, 'seti_') && str_contains( $order->payment_method_id, 'pm_')){
            $payment_intent_id = $order->payment_method_id;
        }
        else if(str_contains( $order->payment_intent_id, 'pi_') && str_contains( $order->payment_method_id, 'pm_')){
            $payment_intent_id = $order->payment_method_id;
        }

        foreach (json_decode($order_tour->tour_pricing) as $i => $item) {
            $qty            = $item->quantity ?? 1;
            $price          = $item->price ?? 0;
            $actual_price   = $item->actual_price ?? 0;
            $discount_price = $item->discount ?? 0;
            $total          = $item->total_price ?? 0;
            $item_total     += $total;
            $quantity       += $qty;

            $pricing[$i] = [
                'tour_id'           => $item->tour_id,
                'tour_pricing_id'   => $item->tour_pricing_id,
                'label'             => $item->label,
                'price_type'        => $item->price_type,
                'quantity'          => $qty,
                'actual_price'      => $actual_price,
                'price'             => $price,
                'discount'          => $discount_price,
                'total_price'       => $total,
            ];
            
            if($i === 0 && $payment_intent_id === null) {
                $depositRule = TourSpecialDeposit::where('use_deposit', 1)
                                ->where('tour_id', $order_tour->tour_id)
                                ->first();
                if(!$depositRule){
                    $depositRule = TourSpecialDeposit::where('type', 'global')->first();
                }

                if ($depositRule->is_discount && $depositRule->charge === 'NONE') {

                    if($depositRule->discount_type === 'PERCENT') {
                        $discount_price = ($actual_price * $depositRule->discount_value)/100;
                        $price = $actual_price - $discount_price;
                    }
                    else if($depositRule->discount_type === 'FIXED') {
                        $discount_price = $depositRule->discount_value;
                        $price = $actual_price - $discount_price;
                    }

                    $total = $price * $qty;

                    $pricing[$i] = [
                        'tour_id'           => $item->tour_id,
                        'tour_pricing_id'   => $item->tour_pricing_id,
                        'label'             => $item->label,
                        'price_type'        => $item->price_type,
                        'quantity'          => $qty,
                        'actual_price'      => $actual_price,
                        'price'             => $price,
                        'discount'          => $discount_price,
                        'total_price'       => $total,
                    ];

                    $discounts[] = [
                        'tour_id'  => $request->subTourId ?? $request->tourId,
                        'discount' => $depositRule->discount_value ?? 0,
                        'label'    => 'Special Discount',
                        'type'     => $depositRule->discount_type,
                        'price'    => ($discount_price * $qty),
                    ];
                }
            }
        }
        if(!empty($pricing)) {
            $order_tour->tour_pricing = json_encode($pricing);
        }
        if(!empty($discounts)) {
            $order_tour->discount = json_encode($discounts);
        }
        // $order_tour->save();

        
    }

    /**
     * Get session time 
     */
    public function getSessionTimes(Request $request)
    {
        Log::info('getSessionTimes');
        $carbonDate = Carbon::parse($request->date);

        $date = $request->date;

        $dayName = $carbonDate->format('l');
        $slots = [];

        $schedulesQuery = TourSchedule::where('tour_id', $request->tour_id);

        // ✅ Check if schedules exist without loading full collection
        if (!$schedulesQuery->exists()) {
            return response()->json([
                'status' => 'success',
                'data' => array_unique($slots),
                'schedule_set' => true
            ]);
        }

        // ✅ Fetch schedules only if needed
        $schedules = $schedulesQuery
            ->where(function ($query) use ($carbonDate) {
                $query->whereDate('session_start_date', '<=', $carbonDate)
                      ->whereDate('until_date', '>=', $carbonDate);
            })
            ->get();



        foreach ($schedules as $schedule) {

            $durationMinutes = match (strtolower($schedule->estimated_duration_unit)) {
                'minute', 'minutes' => $schedule->estimated_duration_num,
                'hour', 'hours' => $schedule->estimated_duration_num * 60,
                'day', 'days' => $schedule->estimated_duration_num * 60 * 24,
                'daily', 'daily' => $schedule->estimated_duration_num * 60 * 24,
                'weekly', 'weekly' => $schedule->estimated_duration_num * 60,
                'monthly', 'monthly' => $schedule->estimated_duration_num * 60 * 24 * 30,
                'yearly', 'yearly' => $schedule->estimated_duration_num * 60,

                 
                default => 0
            };

            $interval = match (strtolower($schedule->repeat_period)) {
                'minute', 'minutes' => $schedule->estimated_duration_num,
                'hour', 'hours' => $schedule->estimated_duration_num * 60,
                'day', 'days' => $schedule->estimated_duration_num * 60 * 24,
                'daily', 'daily' => $schedule->estimated_duration_num * 60 * 24,
                'weekly', 'weekly' => $schedule->estimated_duration_num * 60,
                'monthly', 'monthly' => $schedule->estimated_duration_num * 60 * 24 * 30,
                'yearly', 'yearly' => $schedule->estimated_duration_num * 60,
 

                 
                default => 0
            };

            // $startTime = $schedule->session_start_time ?? '00:00';
            $startTime = '00:00';
            // $endTime = $schedule->session_end_time ?? '23:59';
            $endTime = '23:59';

            if ($schedule->sesion_all_day) {
                $startTime = '00:00';
                $endTime = '23:59';
            }

            $repeatType = strtoupper($schedule->repeat_period);
            $start = Carbon::parse($startTime);
            $end = Carbon::parse($endTime);
            $minimumNoticePeriod = match (strtolower($schedule->minimum_notice_unit)) {
                'minute', 'minutes' => $schedule->minimum_notice_num,
                'hour', 'hours' => $schedule->minimum_notice_num * 60,
                default => 0
            };


            if ($durationMinutes <= 0 || $start->gte($end)) {
                continue;
            }
            // dd(3534);
            $valid = false;
            $flag = false;
            $start_time = '';
            $sesion_instruction = '';
            // dd($repeatType );
            if ($repeatType === 'NONE') {
                $valid = $carbonDate->isSameDay(Carbon::parse($schedule->session_start_date));
                if ($valid) {
                    $start = Carbon::parse($carbonDate->toDateString() . ' ' . $schedule->session_start_time);
                    $end = Carbon::parse($carbonDate->toDateString() . ' ' . $schedule->session_start_time);
                    
                    $slots = array_merge($slots, $this->generateSlots($start, $end, $durationMinutes, $minimumNoticePeriod));
                }
            } elseif ($repeatType === 'DAILY') {

                $daysSinceStart = floor(Carbon::parse($schedule->session_start_date)->diffInDays($carbonDate));
                $repeatInterval = $schedule->repeat_period_unit ?? 1; // 1 means every day

                if ($daysSinceStart % $repeatInterval !== 0) {
                    $slots = [];

                } else{
                    $start = Carbon::parse($carbonDate->toDateString() . ' ' . $schedule->session_start_time);
                    $end = Carbon::parse($carbonDate->toDateString() . ' ' . $schedule->session_start_time);
                    // $end = $end->copy()->addMinutes($durationMinutes);
                    $slots = array_merge($slots, $this->generateSlots($start, $end, $durationMinutes, $minimumNoticePeriod));

                    // $slots = array_slice($slots, 0, 1);
                }
                
            } elseif ($repeatType === 'WEEKLY') {

                $repeats = TourScheduleRepeats::where('tour_schedule_id', $schedule->id)
                    ->where('day', $dayName)
                    ->get();
               
                foreach ($repeats as $repeat) {


                    $weeksSinceStart = floor(Carbon::parse($schedule->session_start_date)->diffInWeeks($carbonDate));
                    $repeatInterval = $schedule->repeat_period_unit ?? 1; // 1 means every week
                    
                    // Skip if not matching the interval
                    if ($weeksSinceStart % $repeatInterval !== 0) {
                        
                        continue;
                    }


                    $selectedDate = Carbon::parse($request->date)->format('Y-m-d'); // or $carbonDate
                    // dd($repeat->session_start_time);
                    $slotStart = Carbon::parse($selectedDate . ' ' . ($schedule->session_start_time));
                    // dd($slotStart);
                    $slotEnd   = Carbon::parse($selectedDate . ' ' . ($schedule->session_start_time));
                    
                    $slots = array_merge($slots, $this->generateSlots($slotStart, $slotEnd, $durationMinutes, $minimumNoticePeriod));

                }                
                // $slots = array_slice($slots, 0, 1);
            } elseif ($repeatType === 'MONTHLY') {

                $monthsSinceStart = floor(Carbon::parse($schedule->session_start_date)->diffInMonths($carbonDate));
                $repeatInterval = $schedule->repeat_period_unit ?? 1; // 1 means every month

                if ($monthsSinceStart % $repeatInterval !== 0) {
                    $slots = [];
                } else{
                    $startDate = Carbon::parse($schedule->session_start_date);

                    // Match same day of the month

                    if ((int)$carbonDate->format('d') === (int)$startDate->format('d')) {
                        $start = Carbon::parse($carbonDate->toDateString() . ' ' . $schedule->session_start_time);
                        $end = Carbon::parse($carbonDate->toDateString() . ' ' . $schedule->session_start_time);

                        $slots = array_merge(
                            $slots,
                            $this->generateSlots($start, $end, $durationMinutes, $minimumNoticePeriod)
                        );
                    }
                    // $slots = array_slice($slots, 0, 1);
                }

                
            } elseif ($repeatType === 'YEARLY') {

                $yearsSinceStart = floor(Carbon::parse($schedule->session_start_date)->diffInYears($carbonDate));
                $repeatInterval = $schedule->repeat_period_unit ?? 1; // 1 means every year

                if ($yearsSinceStart % $repeatInterval !== 0) {
                    $slots = [];
                } else{
                    $startDate = Carbon::parse($schedule->session_start_date);
                    // dd($minimumNoticePeriod);
                    // Match same day and same month
                    if (
                        (int)$carbonDate->format('d') === (int)$startDate->format('d') &&
                        (int)$carbonDate->format('m') === (int)$startDate->format('m')
                    ) {
                        $start = Carbon::parse($carbonDate->toDateString() . ' ' . $schedule->session_start_time);
                        $end = Carbon::parse($carbonDate->toDateString() . ' ' . $schedule->session_start_time);
            
                        $slots = array_merge(
                            $slots,


                            $this->generateSlots($start, $end, 24*60, $minimumNoticePeriod)
                        );
                        
                        // $slots = array_slice($slots, 0, 1);
                    }
                }
            } elseif ($repeatType === 'MINUTELY') {
                // dd(324);

                $interval = $schedule->repeat_period_unit ?? 1; // e.g., every 15 minutes
                $scheduleStartDate = Carbon::parse($schedule->session_start_date);
                $scheduleEndDate = Carbon::parse($schedule->until_date);

                // Check if the selected date is between session_start_date and until_date
                if ($carbonDate->between($scheduleStartDate, $scheduleEndDate)) {
                    $dayName = $carbonDate->format('l'); // e.g., 'Monday'
                    // dd($dayName);
                    $repeatEntries = TourScheduleRepeats::where('tour_schedule_id', $schedule->id)
                        ->where('day', $dayName)
                        ->get();
                        // dd($repeatEntries);
                    foreach ($repeatEntries as $repeat) {
                        $start = Carbon::parse($carbonDate->toDateString() . ' ' . $repeat->start_time);
                        $end = Carbon::parse($carbonDate->toDateString() . ' ' . $repeat->end_time);
                        $durationMinutes = $schedule->repeat_period_unit;
                        // dd($start, $end, $durationMinutes, $minimumNoticePeriod);
                        $slots = array_merge(
                            $slots,
                            $this->generateSlots($start, $end, $durationMinutes, $minimumNoticePeriod)
                        );

                    }
                }
            }
            elseif ($repeatType === 'HOURLY') {

                $interval = $schedule->repeat_period_unit ?? 1; // e.g., every 2 hours
                $scheduleStartDate = Carbon::parse($schedule->session_start_date);
                $scheduleEndDate = Carbon::parse($schedule->until_date);

                if ($carbonDate->between($scheduleStartDate, $scheduleEndDate)) {
                    $dayName = $carbonDate->format('l'); // e.g., 'Tuesday'

                    $repeatEntries = TourScheduleRepeats::where('tour_schedule_id', $schedule->id)
                        ->where('day', $dayName)
                        ->get();
                        // dd($repeatEntries);
                    foreach ($repeatEntries as $repeat) {
                        $slotStart = Carbon::parse($carbonDate->toDateString() . ' ' . $repeat->start_time);
                        $slotEnd   = Carbon::parse($carbonDate->toDateString() . ' ' . $repeat->end_time);

                        // Check if start time matches the "every X hours" rule
                        $hoursSinceStart = floor($scheduleStartDate->diffInHours($slotStart));

                        $durationMinutes = $schedule->repeat_period_unit * 60;
  
                        $slots = array_merge(
                            $slots,
                            $this->generateSlots($slotStart, $slotEnd, $durationMinutes, $minimumNoticePeriod)
                        );

                    }
                }

                if ($schedule->sesion_time_between) {
                    $flag = true;
                    $start_time = date('h:i A', strtotime($slotStart));
                    $sesion_instruction = $schedule->sesion_instruction;
                }

            }
        }

        $fetchDeletedSlot = null;
        if (!empty($slots)) {
            $fetchDeletedSlot = $this->fetchDeletedSlot($request->tour_id);

            $date = $request->date;
            $response = [
                'data' => [
                    $date => array_unique($slots)
                ]
            ];

            $validSlots = $this->applySlotDeletions($response, $fetchDeletedSlot);
            $slots = reset($validSlots['data']); 
        }
       
        if (empty($slots)) {

            // dd(23423, $schedules->isEmpty());
            if ($schedules->isEmpty()) {

                // find the next available schedule instead of looping
                $nextDate = $carbonDate->copy();
                // dd($nextDate);
                $found = false;


                $maxUntil = TourSchedule::where('tour_id', $request->tour_id)->max('until_date');
               
                while (!$found && $maxUntil && Carbon::parse($maxUntil)->gte($nextDate)) {
                    $nextDate->addDay(); // move to next day

                    $schedules = TourSchedule::where('tour_id', $request->tour_id)
                        ->where(function ($query) use ($nextDate) {
                            $query->orWhere(function ($q) use ($nextDate) {
                                $q->whereDate('session_start_date', '<=', $nextDate)
                                  ->whereDate('until_date', '>=', $nextDate);
                            });
                        })
                        ->get();

                    if (!$schedules->isEmpty()) {
                        $found = true;
                    }
                }
                $carbonDate = $nextDate;
            }

            $nextAvailable = [];
            $next_available = [];

            foreach ($schedules as $schedule) {
                $durationMinutes = match (strtolower($schedule->estimated_duration_unit)) {
                    'minute', 'minutes' => $schedule->estimated_duration_num,
                    'hour', 'hours' => $schedule->estimated_duration_num * 60,
                    'day', 'days' => $schedule->estimated_duration_num * 60 * 24,
                    'daily', 'daily' => $schedule->estimated_duration_num * 60 * 24,
                    'weekly', 'weekly' => $schedule->estimated_duration_num * 60,
                    'monthly', 'monthly' => $schedule->estimated_duration_num * 60 * 24 * 30,
                    'yearly', 'yearly' => $schedule->estimated_duration_num * 60,
                    default => 0
                };

                $minimumNoticePeriod = match (strtolower($schedule->minimum_notice_unit)) {
                    'minute', 'minutes' => $schedule->minimum_notice_num,
                    'hour', 'hours' => $schedule->minimum_notice_num * 60,
                    default => 0
                };

                // dd($durationMinutes);
                // dd($request->get('limit'));
                // dd($schedule, $carbonDate, $request->get('limit', 1),$durationMinutes, $minimumNoticePeriod);
                if(!$fetchDeletedSlot){
                    $fetchDeletedSlot = $this->fetchDeletedSlot($request->tour_id);
                }
                
                $nextAvailable = $this->getNextAvailableSlots($schedule, $carbonDate, $request->get('limit', 1),$durationMinutes, $minimumNoticePeriod, $fetchDeletedSlot);
                // dd($nextAvailable);
                if (!empty($nextAvailable)) {
                    $next_available = array_merge($next_available, $nextAvailable);
                }
            }

            // dd($nextAvailable);
            return response()->json([
                'status' => 'warning',
                'message' => 'No sessions available on this date.',
                'schedule_set' => false,
                'next_available' => $nextAvailable,
            ]);
        }
        // $request->tour_id
        
        $req = new Request([
            'tour_date' => $request->date,
            'tour_time' => $slots[0] ?? null
        ]);
        $tour = Tour::findOrFail($request->tour_id);
        $lastMinuts = $this->getLastMinuteCharge($req, $tour);
        $fetchDepositRule = $this->fetchDepositRule($request->tour_id);

        return response()->json([
            'status' => 'success',
            'data' => $start_time && $flag ? array_unique([$start_time . ' - ' . end($slots)]) : array_unique($slots),
            'last_minute' => $lastMinuts,
            'deposit_rule' => $fetchDepositRule,
            'schedule_set' => true,
            'sesion_instruction' => $sesion_instruction
        ]);
    }

    /**
     * Calculate last minute charge based on tour date and time.
     */
    public function getLastMinuteCharge(Request $request, Tour $tour)
    {
        Log::info('add_to_cart');
        $request->validate([
            'tour_date' => 'required|date',
            'tour_time' => 'required'
        ]);
 
        $tourDateTime = Carbon::parse($request->tour_date . ' ' . $request->tour_time);
        $now = Carbon::now();
        $hoursDiff = $now->diffInHours($tourDateTime, false);
 
        if ($hoursDiff < 0) {
            return [
                'apply' => false,
                'message' => 'Tour time already passed'
            ];
        }
 
        $rules = $tour->lastMinuteBookings()
            ->whereDate('from_date', '<=', $request->tour_date)
            ->whereDate('to_date', '>=', $request->tour_date)
            ->get();
 
        foreach ($rules as $rule) {
            if ($hoursDiff <= $rule->last_minute_hours) {
                return [
                    'apply' => true,
                    'type' => $rule->amount_type,
                    'amount' => $rule->amount,
                    'hours_remaining' => $hoursDiff
                ];
            }
        }
 
        return [
            'apply' => false,
            'rules' => $rules,
            'message' => 'No last minute charge applicable ' . $request->tour_date . ' ' . $request->tour_time . ' ' .$hoursDiff
        ];

    }

    /**
     * Fetch a deposit rule by tour id.
     */
    public function fetchDepositRule($id)
    {
        Log::info('add_to_cart');
        $cacheKey = 'depositRule_' . $id;
        $discount = [];
        $depositRule = Cache::remember($cacheKey, 86400, function () use ($id) {
            return TourSpecialDeposit::where('tour_id', $id)->first();
        });

        if($depositRule && $depositRule->is_discount){
            $discount = [
                'discount_type'     =>  $depositRule->discount_type,
                'discount_value'     =>  $depositRule->discount_value,
                'is_discount'     =>  $depositRule->is_discount,
            ];
        }

        // If no rule found for specific tour, check global rule
        if (!$depositRule || ($depositRule && $depositRule->use_deposit == 0)) {
            $depositRule = Cache::remember('depositRule_global', 86400, function () {
                return TourSpecialDeposit::where('type', 'global')->first();
            });
        }

        if($depositRule && $depositRule->price_booking_fee){
            $bookingFees = [
                'price_booking_fee'     => $depositRule->price_booking_fee,
                'tour_booking_fee'      => $depositRule->tour_booking_fee,
                'tour_booking_fee_type' => $depositRule->tour_booking_fee_type,
            ];
        } else{
            $bookingFees = [
                'price_booking_fee'     => get_setting('price_booking_fee'),
                'tour_booking_fee'      => get_setting('tour_booking_fee'),
                'tour_booking_fee_type' => get_setting('tour_booking_fee_type'),
            ];
        }

        if (!$depositRule) {
            return [
                'status' => false,
                'message' => 'Tour deposit rule not found (including global rule)',
                'deposit_rule' => null,
                'booking_fees' => $bookingFees,
                'discount'     => $discount
            ];
        }

        return [
            'status' => true,
            'deposit_rule' => $depositRule,
            'booking_fees' => $bookingFees,
            'discount'     => $discount
        ];
    }

    /**
     * Fetch a deleted slot by tour id.
     */
    public function fetchDeletedSlot($id)
    {
        return ScheduleDeleteSlot::where('tour_id', $id)->get();
        return response()->json(['success' => true, 'message' => 'Slot saved successfully']);
    }

    /**
     * Normalize time string to 24h "HH:MM" for comparison
     */
    public function normalizeTime(string $time): string
    {
        return date("H:i", strtotime($time));
    }

    /**
     * Sort slots chronologically (keeps AM/PM format)
     */
    public function sortSlots(array $slots): array
    {
        usort($slots, function ($a, $b) {
            return strtotime($a) <=> strtotime($b);
        });
        return $slots;
    }

    /**
     * Apply slot deletions to the response data
     *
     * @param array $response
     * @param \Illuminate\Support\Collection|array $storeDeleteSlot
     * @return array
     */
    public function applySlotDeletions(array $response, $storeDeleteSlot): array
    {
        $clearAll = false;
        // dd($response, 32432);
        foreach ($storeDeleteSlot as $deleteSlot) {
            $date      = $deleteSlot->slot_date;
            $startTime = $this->normalizeTime($deleteSlot->slot_start_time);
            $endTime   = $deleteSlot->slot_end_time ? $this->normalizeTime($deleteSlot->slot_end_time) : null;
            $type      = $deleteSlot->delete_type;

            if ($type === 'all') {
                // dd(34);
                $clearAll = true;
                break;
            }



            if ($type === 'single') {

                if (!isset($response['data'][$date])) {
                   
                    continue;
                }
                // remove only this slot range
                $response['data'][$date] = array_filter(
                    $response['data'][$date],
                    function ($slot) use ($startTime, $endTime) {
                        $slot24 = $this->normalizeTime($slot);
                        return !($slot24 >= $startTime && $endTime && $slot24 < $endTime);
                    }
                );

            } elseif ($type === 'after') {
                // }
                // remove slots for this date and all future dates
                foreach ($response['data'] as $d => $slots) {

                    $dDate = Carbon::parse($d);   // convert key to Carbon
                    $cmp   = Carbon::parse($date);
                    // dd($dDate->lt($cmp), $dDate, $cmp);
                    if ($dDate->lt($cmp)) {
                        // dd(234);
                        continue; // only affect this date & after
                    }

                    $response['data'][$d] = array_filter($slots, function($slot) use ($startTime, $dDate, $cmp) {
                        if ($dDate->equalTo($cmp)) {
                            // for current date -> remove >= startTime
                            return Carbon::parse($slot)->lt(Carbon::parse($startTime));
                        }
                        // for future dates -> remove all
                        return false;
                    });

                }
            }
        }

        // if 'all' deletion was found → clear everything
        if ($clearAll) {
            foreach ($response['data'] as $d => $slots) {
                $response['data'][$d] = [];
            }
        }
        // dd($response['data']);
        // ensure sorted order for each date
        foreach ($response['data'] as $d => $slots) {
            $response['data'][$d] = $this->sortSlots($slots);
        }

        return $response;
    }

    /**
     * Generate slots
     */
    private function generateSlots($start, $end, $durationMinutes, $minimumNoticePeriod)
    {
        $slots = [];
        
        $earliestAllowed = now()->addMinutes($minimumNoticePeriod);
        // dd($earliestAllowed);
        while ($start->lte($end)) {

            if ($start->gte($earliestAllowed)) {
                $slots[] = $start->format('g:i A');
            }

            $start = $start->copy()->addMinutes($durationMinutes);
        }

        return $slots;
    }
 
    private function getSlotsForDate($schedule, $date, $durationMinutes = 30, $minimumNoticePeriod = 0)
    {
        $slots = [];
        // dd($schedule, $date, $durationMinutes = 30, $minimumNoticePeriod = 0);
        // Parse start and end times for the given date
        $startTime = Carbon::parse($date->format('Y-m-d') . ' ' . $schedule->session_start_time);
        $endTime   = Carbon::parse($date->format('Y-m-d') . ' ' . $schedule->session_end_time);
        
        if($startTime->gte($endTime)){
            if($schedule->estimated_duration_unit == "HOURS"){
                $estimateDurationMinutes = $schedule->estimated_duration_num * 60;
            }elseif($schedule->estimated_duration_unit == "DAYS"){
                $estimateDurationMinutes = $schedule->estimated_duration_num * 60 * 24;
            } else{
                $estimateDurationMinutes = $schedule->estimated_duration_num;
            }
            // dd($estimateDurationMinutes, $schedule->estimated_duration_unit, $schedule->estimated_duration_unit == "Hours");
            $endTime  = Carbon::parse($date->format('Y-m-d') . ' ' . $schedule->session_start_time)->copy()->addMinutes($estimateDurationMinutes);
        }
         // dd($startTime, $endTime);
        // dd($durationMinutes);

        // dd($schedule->session_start_time, $schedule->session_end_time);
        // dd( $startTime,  $endTime ,$earliestAllowed = now()->addMinutes($minimumNoticePeriod));
        // Calculate the earliest slot allowed
        $earliestAllowed = now()->addMinutes($minimumNoticePeriod);
        // dd($durationMinutes);
        // Generate slots at given intervals
        // dd($startTime->lte($endTime), $startTime, $endTime);
        // dd($startTime->lte($endTime));
        while ($startTime->lte($endTime)) {
            if ($startTime->gte($earliestAllowed)) {
                $slots[] = $startTime->format('g:i A'); // Keep same format as generateSlots()
            }
            $startTime->addMinutes($durationMinutes);
        }

        return $slots;
    }

    private function getNextAvailableSlots($schedule, Carbon $carbonDate, $limit, $durationMinutes, $minimumNoticePeriod, $storeDeleteSlot)
    {
        $durationMinutes = $schedule->repeat_period_unit;
        $repeatType      = $schedule->repeat_period;
        $interval        = $schedule->repeat_period_unit ?? 1;

        $scheduleStartDate = Carbon::parse($schedule->session_start_date);
        $scheduleEndDate   = Carbon::parse($schedule->until_date);

        $nextDates = [];
        // var_dump($repeatType);
        // --- NONE (one-time) ---
        if ($repeatType === 'NONE') {
            $start = Carbon::parse($schedule->session_start_date.' '.$schedule->session_start_time);
            $end   = Carbon::parse($schedule->session_start_date.' '.$schedule->session_start_time);

            if ($carbonDate->gt($start)) {
                return $nextDates;
            }

            $allSlots = $this->generateSlots($start, $end, $durationMinutes, $minimumNoticePeriod);

            // Apply deletions
           

            $temp = [
                'data' => [
                    $schedule->session_start_date => array_unique($allSlots)
                ]
            ];
            $temp    = $this->applySlotDeletions($temp, $storeDeleteSlot);
            $filtered = $temp['data'][$schedule->session_start_date] ?? [];

            if (!empty($filtered)) {
                $nextDates[] = [
                    'date'  => $schedule->session_start_date,
                    'slots' => $filtered,
                ];
            }

            return $nextDates;
        }

        // --- MINUTELY ---
        if ($repeatType === 'MINUTELY') {
            $checkDate = $carbonDate->copy();

            while ($checkDate <= $scheduleEndDate) {
                $dayName = $checkDate->format('l');

                $repeatEntries = TourScheduleRepeats::where('tour_schedule_id', $schedule->id)
                    ->where('day', $dayName)
                    ->get();

                if ($repeatEntries->isEmpty()) {
                    $checkDate->addDay();
                    continue;
                }

                foreach ($repeatEntries as $repeat) {
                    $start = Carbon::parse($checkDate->toDateString().' '.$repeat->start_time);
                    $end   = Carbon::parse($checkDate->toDateString().' '.$repeat->end_time);

                    $allSlots = $this->generateSlots($start, $end, $durationMinutes, $minimumNoticePeriod);

                    
                     $temp = [
                            'data' => [
                                $checkDate->toDateString() => array_unique($allSlots)
                            ]
                        ];
                    $temp    = $this->applySlotDeletions($temp, $storeDeleteSlot);
                    $filtered = $temp['data'][$checkDate->toDateString()] ?? [];

                    if (!empty($filtered)) {
                        $nextDates[] = [
                            'date'  => $checkDate->toDateString(),
                            'slots' => $filtered,
                        ];
                    }
                }

                break;
            }

            return $nextDates;
        }

        // --- HOURLY ---
        if ($repeatType === 'HOURLY') {
            $checkDate = $carbonDate->copy();

            while ($checkDate <= $scheduleEndDate) {
                $dayName = $checkDate->format('l');

                $repeatEntries = TourScheduleRepeats::where('tour_schedule_id', $schedule->id)
                    ->where('day', $dayName)
                    ->get();

                if ($repeatEntries->isEmpty()) {
                    $checkDate->addDay();
                    continue;
                }

                foreach ($repeatEntries as $repeat) {
                    $slotStart = Carbon::parse($checkDate->toDateString().' '.$repeat->start_time);
                    $slotEnd   = Carbon::parse($checkDate->toDateString().' '.$repeat->end_time);

                    $hoursSinceStart = floor($scheduleStartDate->diffInHours($slotStart));
                    if ($hoursSinceStart % $interval !== 0) {
                        continue;
                    }

                    $durationMinutes = $durationMinutes * 60;
                    $allSlots = $this->generateSlots($slotStart, $slotEnd, $durationMinutes, $minimumNoticePeriod);

                    $filteredSlots = [];
                    foreach ($allSlots as $index => $slot) {
                        if ($index % $interval === 0) {
                            $filteredSlots[] = $slot;
                        }
                    }

                    // Apply deletions
                    // dd($filteredSlots, $checkDate->toDateString());

                    $temp = [
                            'data' => [
                                $checkDate->toDateString() => array_unique($filteredSlots)
                            ]
                        ];

                        // dd($temp);
                    $temp    = $this->applySlotDeletions($temp, $storeDeleteSlot);
                    $filtered = $temp['data'][$checkDate->toDateString()] ?? [];
                    // dd($temp, $filtered);
                    if (!empty($filtered)) {
                        $nextDates[] = [
                            'date'  => $checkDate->toDateString(),
                            'slots' => $filtered,
                        ];
                    }
                }

                break;
            }

            return $nextDates;
        }

        // --- Default (DAILY, WEEKLY, MONTHLY, YEARLY) ---
        $next = $scheduleStartDate->copy();

        // while ($next <= $carbonDate) {
        //     switch ($repeatType) {
        //         case 'DAILY':   $next->addDays($interval); break;
        //         case 'WEEKLY':  $next->addDays(1); break;
        //         case 'MONTHLY': $next->addMonths($interval); break;
        //         case 'YEARLY':  $next->addYears($interval); break;
        //         default: return [];
        //     }
        // }

        while ($next <= $scheduleEndDate && count($nextDates) < $limit) {
            if ($repeatType === 'WEEKLY') {
                $dayName = $next->format('l');

                $allowed = TourScheduleRepeats::where('tour_schedule_id', $schedule->id)
                    ->where('day', $dayName)
                    ->exists();

                if (!$allowed) {
                    $next->addDay();
                    continue;
                }

                $weekDiff = $scheduleStartDate->diffInWeeks($next);
                if ($weekDiff % $interval != 0) {
                    $next->addDay();
                    continue;
                }
            }

            $slots = $this->getSlotsForDate($schedule, $next, 24*60, $minimumNoticePeriod);

            if (!empty($slots)) {
                $slots = array_slice($slots, 0, 1);

                // Apply deletions
                // $temp    = [$next->toDateString() => $slots];
                $temp = [
                            'data' => [
                                $next->toDateString() => array_unique($slots)
                            ]
                        ];
                $temp    = $this->applySlotDeletions($temp, $storeDeleteSlot);
                $filtered = $temp['data'][$next->toDateString()] ?? [];

                if (!empty($filtered)) {
                    $nextDates[] = [
                        'date'  => $next->toDateString(),
                        'slots' => $filtered,
                    ];
                }
            }

            switch ($repeatType) {
                case 'DAILY':   $next->addDays($interval); break;
                case 'WEEKLY':  $next->addDays(1); break;
                case 'MONTHLY': $next->addMonths($interval); break;
                case 'YEARLY':  $next->addYears($interval); break;
            }
        }

        return $nextDates;
    }
    
}
