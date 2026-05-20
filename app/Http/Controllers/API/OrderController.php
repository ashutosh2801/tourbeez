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
use App\Services\PricingService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Stripe\Customer;
use Stripe\Stripe;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     * 
     * 
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
     * View indivisual Order
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
                        ->where('payment_type', '<>', 'PROMO_CODE')
                        ->sum('amount');
                
            $promoCode  = $booking->payments()
                        ->where('status', 'succeeded')
                        ->where('payment_type', 'PROMO_CODE')
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

    public function getOrderDetailByOrderID( Request $request, $orderID )
    {
        $order = Order::find(decrypt($orderID));

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
            ];
        }

        $extra_pricing = $order->order_tour->tour_extra ? json_decode($order->order_tour->tour_extra) : [];
        $cartAdons=[]; $total = 0;
        foreach($extra_pricing as $ep) {
            $extraAddon = Addon::find($ep->tour_extra_id);
            $cartAdons[] = [
                "id"        => $ep->tour_extra_id,
                "price"     => currencyConvert($ep->price, $order->currency, 'CAD'),
                "label"     => $extraAddon->name,
                "quantity"  => $ep->quantity,
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
        $paidAmount = $order->payments()
                    ->where('status', 'succeeded')
                    ->where('payment_type', '<>', 'PROMO_CODE')
                    ->sum('amount');
                
        $promoCode  = $order->payments()
                    ->where('status', 'succeeded')
                    ->where('payment_type', 'PROMO_CODE')
                    ->sum('amount');    

        $totalAmount    = $order->total_amount ?? 0;
        $totalPaid      = $paidAmount + $promoCode;
        $balanceAmount  = max($totalAmount - $totalPaid, 0);        

        $data = [
            "order_number"  => $order->order_number,
            "source"        => $order->source,
            "currency"      => $order->currency,
            'payment_status'=> $totalPaid > 0 ? 'paid' : 'unpaid',
            "total_amount"  => $totalPaid > 0 ? $balanceAmount : $totalAmount,
            "balance_amount"=> $balanceAmount,
            "promo_code"    => currencyConvert( $promoCode, $order->currency, 'CAD'),
            "paid_amount"   => currencyConvert( $paidAmount, $order->currency, 'CAD'),
            "total_paid"    => currencyConvert( $totalPaid, $order->currency, 'CAD'),
            'payment_by'    => 'customer',
            "orderId"       => $order->id,
            "tourId"        => $order->tour_id,
            "tourTitle"     => $order->tour?->title,
            "tourSlug"      => $order->tour?->slug,
            "tourImage"     => $image,
            "selectedDate"  => $order->order_tour->tour_date,
            "selectedTime"  => $order->order_tour->tour_time,
            "tourPrice"     => currencyConvert( $order->total_amount, $order->currency, 'CAD'),
            "sessionId"     => $order->session_id ?? strtotime('now'),
            "userId"        => $order->user_id ?? 0,
            "minQty"        => $order->tour->detail->quantity_min,
            "maxQty"        => $order->tour->detail->quantity_max,
            "tourFees"      => $tourFees,
            "tourPickups"   => $tourPickups,
            "customer"      => $customer,
            "cartItems"     => $cartItems,
            "cartAdons"     => $cartAdons,
            "deposite_rule" => $order->tour->specialDeposit,
            "action_name"   => $order->action_name,
            "free_cancellation"   => $order->tour->detail->free_cancellation,
            "exceptional_deal"    => $order->tour->detail->exceptional_deal,
            "lowest_price"        => $order->tour->detail->lowest_price,
            "kids_discount"       => $order->tour->detail->kids_discount,
            "full_refund"         => $order->tour->detail->full_refund,



        ];



        return response()->json([
            'status' => true,
            'data' => $data,
        ], 200);
    }
    

    /**
     * Adding cart
     */
    public function add_to_cart(Request $request) 
    {
        //dd($request->all());
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

        $tour = Tour::with(['pricings'])->where('id', $request->tourId)->first();
        if(!$tour) {
            return response()->json([
            'status' => false,
            'message' => 'Tour not found.'
            ], 404);
        }

        $order = Order::updateOrCreate(
        [
            'id' => $request->orderId ?? null, // condition: check if orderId exists
        ],[
            'tour_id'       => $request->tourId,
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
                        'tour_id'           => $request->tourId,
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

            if( isset($request->cartAdons) && !empty($request->cartAdons) ) {
                foreach ($request->cartAdons as $addon) {
                    if(isset($addon['id']) && isset($addon['quantity'])) {

                        $price  = floatval($addon['price']);
                        $qty    = intval($addon['quantity']);

                        $extra_price  = $price * $qty;
                        $item_total  += $extra_price;

                        $extra[] = [
                            'tour_id'           => $request->tourId,
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
                            'tour_id'           => $request->tourId,
                            'tour_taxes_id'     => $fee['id'],
                            'label'             => $fee['label'],
                            'type'              => $type,
                            'value'             => $value,
                            'price'             => round($tax_fee, 2),
                        ];
                    }
                }
            }

            OrderTour::updateOrCreate(
                [
                    'order_id' => $orderId
                ],
                [
                    'order_id'          => $orderId,
                    'tour_id'           => $request->tourId, // mandatory
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

            return response()->json([
                'status'        => true,
                'message'       => 'Item added in cart',
                'orderId'       => encrypt($orderId),
                'data'          => $order,
                'data_detail'   => $order->orderTours
            ], 200);
        }

        return response()->json([
                'status'    => false,
                'message'   => 'Item not added in cart',
            ], 401);
    }

    /**
     * Update cart
     */
    public function update_cart(Request $request, $id)
    {
        $validated = $request->validate([
            // 'orderId' => 'required|integer|exists:orders,id',
            'tourId' => 'required|integer|exists:tours,id',
            'selectedDate' => 'required|date_format:Y-m-d',
            'selectedTime' => 'nullable',
            'cartItems' => 'required|array|min:1',
            'cartItems.*.id' => 'required|integer',
            'cartItems.*.actual_price' => 'nullable|numeric',
            'cartItems.*.price' => 'required|numeric',
            'cartItems.*.discount' => 'nullable|numeric',
            'cartItems.*.quantity' => 'required|integer|min:1',
            'cartItems.*.total_price'=> 'required|numeric',
            'cartItems.*.label' => 'required|string',
            'cartItems.*.price_type' => 'required|string',

            'formData.first_name' => 'required|string|max:255',
            'formData.last_name'  => 'required|string|max:255',
            'formData.email'      => 'required|email|max:255',
            'formData.phone'      => 'required|string|max:20',
            'formData.instructions' => 'nullable|string|max:500',
            'formData.pickup_id' => 'nullable|numeric',
            'formData.pickup_name' => 'nullable|string|max:255',
            'formData.adv_deposite' => 'nullable|string|max:255',
            'formData.is_discount' => 'nullable|string|max:255',
            'formData.booking_fee' => 'nullable|numeric|max:255',

        ]);

        $order = Order::find($id);
        if (!$order) {
            return response()->json([
                'status' => false,
                'message' => 'Order not found.'
            ], 404);
        }

        $tour = Tour::with(['pricings'])->find($request->tourId);
        if (!$tour) {
            return response()->json([
                'status' => false,
                'message' => 'Tour not found.'
            ], 404);
        }

        if ($request->filled('promo_code')) {
            $promo = Promo::where('code', $request->promo_code)
                ->where('status', 'ISSUED')
                ->where(function ($q) {
                    $q->whereNull('expiry_date')
                      ->orWhere('expiry_date', '>=', now()->toDateString());
                })
                ->first();
            $promo->used_by = $promo->used_by + 1;
            $promo->save();
        }
        
        try {

            $data = $request->input('formData');

            // Save or update customer
            $customer = OrderCustomer::where('order_id', $id)->first() ?? new OrderCustomer();
            $adv_deposite = $data['adv_deposite'];

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

            Stripe::setApiKey(env('STRIPE_SECRET'));

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

            // Initialize
            $quantity   = 0;
            $pricing    = [];
            $extra      = [];
            $fees       = [];
            $discount   = [];
            $item_total = 0;

            // Cart Items
            foreach ($validated['cartItems'] as $item) {
                $qty            = $item['quantity'] ?? 1;
                $actual_price   = $item['actual_price'] ?? 0;
                $price          = $item['price'] ?? 0;
                $discount_price = $item['discount'] ?? 0;
                $total          = $item['total_price'] ?? 0;
                $item_total     += $total;
                $quantity       += $qty;

                $pricing[] = [
                    'tour_id'           => $request->tourId,
                    'tour_pricing_id'   => $item['id'],
                    'label'             => $item['label'],
                    'price_type'        => $item['price_type'],
                    'quantity'          => $qty,
                    'actual_price'      => $actual_price,
                    'price'             => $price,
                    'discount'          => $discount_price,
                    'total_price'       => $total,
                ];
                
                if($request->action_name === "book" 
                    && $adv_deposite === "deposit" 
                    && (str_contains($item['label'], 'Adult') || str_contains($item['label'], 'Participant') || str_contains($item['label'], 'Group')) 
                    && $discount_price>0) 
                {
                    $depositRule = TourSpecialDeposit::where('use_deposit', 1)
                                    ->where('tour_id', $tour->id)
                                    ->first();
                    if(!$depositRule){
                        $depositRule = TourSpecialDeposit::where('type', 'global')->first();
                    }

                    if ($depositRule->is_discount && $depositRule->charge === 'NONE') {

                        $discount[] = [
                            'tour_id'  => $request->tourId,
                            'label'    => 'Discount',
                            'type'     => $depositRule->discount_type,
                            'quantity' => $qty,
                            'discount' => $depositRule->discount_value ?? 0,
                            'price'    => $item['price_type'] === 'FIXED' ? round($discount_price, 2) : round($discount_price * $qty, 2),
                        ];
                    }
                }
            }

            // Add-ons
            if (!empty($request->cartAdons)) {
                foreach ($request->cartAdons as $addon) {
                    if (isset($addon['id'], $addon['quantity'], $addon['price'], $addon['total_price'], $addon['label']) && $addon['quantity'] != 0) {
                        $extra[] = [
                            'tour_id'           => $request->tourId,
                            'tour_extra_id'     => $addon['id'],
                            'quantity'          => $addon['quantity'],
                            'label'             => $addon['label'],
                            'price'             => $addon['price'],
                            'total_price'       => $addon['total_price'],
                        ];
                        $item_total += floatval($addon['total_price']);
                    }
                }
            }

            // Cart Fees
            if (!empty($request->cartFees)) {
                foreach ($request->cartFees as $fee) {
                    if (isset($fee['id'], $fee['value'], $fee['label'])) {
                        $fees[] = [
                            'tour_id'           => $request->tourId,
                            'tour_taxes_id'     => $fee['id'],
                            'label'             => $fee['label'],
                            'type'              => $fee['type'],
                            'value'             => $fee['value'],
                            'price'             => $fee['price'],
                        ];
                        $item_total += floatval($fee['price']);
                    }
                }
            }

            $order_tour_data = [
                'tour_id'           => $request->tourId,
                'order_id'          => $order->id,
                'tour_date'         => $validated['selectedDate'],
                'tour_pricing'      => json_encode($pricing ?? []),
                'tour_extra'        => json_encode($extra ?? []),
                'tour_fees'         => json_encode($fees ?? []),
                'discount'          => json_encode($discount ?? []),
                'number_of_guests'  => $quantity,
                'total_amount'      => $item_total,
            ];

            if ($order->payment_status === 1) {
                return response()->json([
                    'status'            => true,
                    'message'           => 'Cart already updated successfully',
                    'data'              => $order,
                    'data_detail'       => $order->orderTours,
                    'stripe_customer_id'=> $order->stripe_customer_id,
                    'payment_intent_id' => $order->payment_intent_id,
                    'payment_intent_client_secret' => $order->payment_intent_client_secret,
                ], 200);
            }

            OrderTour::updateOrCreate(
                ['order_id' => $order->id],
                $order_tour_data
            );

            // Save Order Metas
            if (!empty($request->cartFees)) {
                foreach ($request->cartFees as $fee) {
                    if (isset($fee['name'], $fee['value'])) {
                        OrderMeta::create([
                            'order_id' => $order->id,
                            'name'     => $fee['name'],
                            'value'    => $fee['value'],
                        ]);
                    }
                }
            }

            /* If already partially paid or added discount/promo etc in backend */
            $paidAmount = $order->payments()
                        ->where('status', 'succeeded')
                        ->where('payment_type', '<>', 'PROMO_CODE')
                        ->sum('amount');
                
            $promoCode  = $order->payments()
                        ->where('status', 'succeeded')
                        ->where('payment_type', 'PROMO_CODE')
                        ->sum('amount');

            $totalPaid  = $paidAmount + $promoCode;

            if($totalPaid > 0)
            $item_total = max(($item_total - $totalPaid), 0);  

            // Final update to main order
            $previousOrderTotalAmount = $order->total_amount;

            $order_actions_notes       = NULL;
            $order->sub_tour_id        = $request->sub_tour_id;
            $order->action_name        = $request->action_name;
            $order->number_of_guests   = $quantity;
            $order->total_amount       = $item_total ?? 0;
            $order->balance_amount     = ($adv_deposite == 'deposit') ? $item_total : 0;
            $order->adv_deposite       = $adv_deposite;
            $order->currency           = $request->currency;
            $order->source             = $request->source ? ucwords($request->source) : 'Tourbeez';
            $order->updated_at         = now();
            $order->save();
            // dd(242);
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

            //dd($order_tour_data);
            if ($adv_deposite === "deposit") {
                
                $chargeAmount = 0;              

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
                    // Deposit not enabled → fallback to full
                    $chargeAmount = $order->total_amount;
                }

                if ($request->filled('promo_code')) {

                    switch ($promo->value_type) {

                        /* ================= FIXED ================= */

                        case 'VALUE':
                            $discountAmount = min($promo->voucher_value, $item_total);
                            break;

                        case 'VALUE_LIMITPRODUCT':
                            foreach ($pricing as $item) {
                                if ($item['tour_pricing_id'] == $promo->product_id) {
                                    $discountAmount = min(
                                        $promo->voucher_value,
                                        $item['total_price']
                                    );
                                    break;
                                }
                            }
                            break;

                        case 'VALUE_LIMITCATEGORY':
                            foreach ($pricing as $item) {
                                $product = TourPricing::find($item['tour_pricing_id']);
                                if ($product && $product->category_id == $promo->category_id) {
                                    $discountAmount = min(
                                        $promo->voucher_value,
                                        $item['total_price']
                                    );
                                }
                            }
                            break;

                        /* ================= PERCENT ================= */

                        case 'PERCENT':
                            $discountAmount = ($item_total * $promo->value_percent) / 100;
                            break;

                        case 'PERCENT_LIMITPRODUCT':
                            foreach ($pricing as $item) {
                                if ($item['tour_pricing_id'] == $promo->product_id) {
                                    $discountAmount = ($item['total_price'] * $promo->value_percent) / 100;
                                    break;
                                }
                            }
                            break;

                        case 'PERCENT_LIMITCATEGORY':
                            foreach ($pricing as $item) {
                                $product = TourPricing::find($item['tour_pricing_id']);
                                if ($product && $product->category_id == $promo->category_id) {
                                    $discountAmount += ($item['total_price'] * $promo->value_percent) / 100;
                                }
                            }
                            break;
                    }

                    // Safety
                    $discountAmount = round(min($discountAmount, $item_total), 2);

                    $chargeAmount = $chargeAmount - $discountAmount;
                }

                // ✅ Update amounts in order
                if($request->action_name === "reserve"){
                    $chargeAmount = 0;
                }
                $order->booked_amount  = $chargeAmount;        // what’s being charged now
                $order->balance_amount = $order->total_amount - $order->booked_amount;

                //print_r($order); exit;
                if ($chargeAmount > 0) {
                    
                    $pi = isset($order->payment_intent_id) ? \Stripe\PaymentIntent::retrieve($order->payment_intent_id) : null;
                    if(!$pi || $pi->status !== "requires_capture" || $pi->status !== 'succeeded') {
                        $pi = \Stripe\PaymentIntent::create([
                            'customer'  => $stripeCustomer->id,
                            'amount' => intval(round($chargeAmount * 100)),
                            'currency' => $order->currency,
                            // 'receipt_email' => $data['email'],
                            'description' => '#' . $order->order_number . ' - ' . $tour->title,
                            'statement_descriptor_suffix' =>  $order->order_number,
                            'metadata'  => $metaData,
                            'capture_method' => 'manual',
                            'automatic_payment_methods' => ['enabled' => true],
                            'setup_future_usage'=> 'off_session',
                        ]);

                        $order->payment_intent_client_secret = $pi->client_secret;
                        $order->payment_intent_id = $pi->id;
                        $order->save(); 

                        $order_payment = OrderPayment::create([
                            'order_id'          => $order->id,
                            'amount'            => ($adv_deposite == 'deposit') ? $chargeAmount : $order->total_amount,
                            'currency'          => $order->currency,
                            'status'            => 'pending', // manual capture pending
                            'action'            => $adv_deposite,
                            'payment_intent_id' => $pi->id,
                            'transaction_id'    => null, // no charge yet until capture
                            'response_payload'  => json_encode($pi),
                        ]);
                    }
                    
                    // Retrieve card details from payment method if available
                    try {
                        $retrievedIntent = \Stripe\PaymentIntent::retrieve($pi->id);
                        if (!empty($retrievedIntent->payment_method)) {
                            $paymentMethod = \Stripe\PaymentMethod::retrieve($retrievedIntent->payment_method);
                            // return $paymentMethod;
                            if (isset($paymentMethod->card) && $paymentMethod->type === 'card') {
                                $cardDetails = [
                                    'type'      => $paymentMethod->type ?? null,
                                    'brand'     => $paymentMethod->card->brand ?? null,
                                    'last4'     => $paymentMethod->card->last4 ?? null,
                                    'exp_month' => $paymentMethod->card->exp_month ?? null,
                                    'exp_year'  => $paymentMethod->card->exp_year ?? null,
                                ];

                                // Optional: store in Order table (if fields exist)
                                // Store full card details as JSON if you have a field for it
                                $order->card_info = json_encode($cardDetails); 
                            }

                            \Log::warning('PaymentIntent uncaptured - ' . $order->order_number . ' - ' . $pi->id);
                            
                            OrderPayment::updateOrCreate(['id' => $order_payment->id], 
                            [
                                'status'            => 'uncaptured',
                                'payment_method'    => $cardDetails['type'] ?? null,
                                'card_brand'        => $cardDetails['brand'] ?? null,
                                'card_last4'        => $cardDetails['last4'] ?? null,
                                'card_exp_month'    => $cardDetails['exp_month'] ?? null,
                                'card_exp_year'     => $cardDetails['exp_year'] ?? null,
                            ]);
                        }
                    } catch (\Exception $cardError) {
                        \Log::warning('Unable to retrieve card details: ' . $cardError->getMessage());
                    }

                } else {
                    // No charge needed
                    $si = \Stripe\SetupIntent::create([
                        'customer'  => $stripeCustomer->id,
                        'automatic_payment_methods' => [
                            'enabled' => true,
                        ],                        // 'usage'     => 'off_session',
                        'metadata'  => $metaData
                    ]);               
                    $order->payment_intent_client_secret = $si->client_secret;
                    $order->payment_intent_id = $si->id;

                    \Log::warning('SetupIntent uncaptured - ' . $order->order_number . ' - ' . $si->id);
                    OrderPayment::create([
                        'order_id'          => $order->id,
                        'payment_intent_id' => $si->id,
                        'transaction_id'    => null, // no charge yet until capture
                        'payment_method'    => 'card',
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
                
                \Log::warning('full - ' . $order->order_number . ' - Stripe Customer: ' . $stripeCustomer->id);
                $order->booked_amount  = $order->total_amount;
                $order->balance_amount = 0;

                // ✅ Fetch and store card details (if available)
                try {
                    $pi = isset($order->payment_intent_id) ? \Stripe\PaymentIntent::retrieve($order->payment_intent_id) : null;
                    if(!$pi || $pi->status !== "requires_capture" || $pi->status !== 'succeeded') {
                        $pi = \Stripe\PaymentIntent::create([
                            'customer'  => $stripeCustomer->id,
                            'amount' => intval(round($order->total_amount * 100)),
                            'currency' => $order->currency,
                            // 'receipt_email' => $data['email'],
                            'description' => '#' . $order->order_number . ' - ' . $tour->title,
                            'statement_descriptor_suffix' =>  $order->order_number,
                            'metadata'  => $metaData,
                            'automatic_payment_methods' => ['enabled' => true],
                            'capture_method' => 'manual',
                            'setup_future_usage'=> 'off_session',
                        ]);

                        $order->payment_intent_client_secret = $pi->client_secret;
                        $order->payment_intent_id = $pi->id;
                        $order->save();

                        $order_payment = OrderPayment::create([
                            'order_id'          => $order->id,
                            'amount'            => $order->total_amount,
                            'currency'          => $order->currency,
                            'status'            => 'pending', // manual capture pending
                            'action'            => $adv_deposite,
                            'payment_intent_id' => $pi->id,
                            'transaction_id'    => null, // no charge yet until capture
                            'response_payload'  => json_encode($pi),
                        ]);
                    }
                    
                    $retrievedIntent = \Stripe\PaymentIntent::retrieve($pi->id);
                    $paymentMethod = \Stripe\PaymentMethod::retrieve($retrievedIntent->payment_method);
                    if ($paymentMethod->type === 'card') {

                        $cardDetails = [
                            'type'      => $paymentMethod->type ?? null,
                            'brand'     => $paymentMethod->card->brand ?? null,
                            'last4'     => $paymentMethod->card->last4 ?? null,
                            'exp_month' => $paymentMethod->card->exp_month ?? null,
                            'exp_year'  => $paymentMethod->card->exp_year ?? null,
                        ];

                        // Optional: store in Order table (if fields exist)
                        // Store full card details as JSON if you have a field for it
                        $order->card_info = json_encode($cardDetails);

                        OrderPayment::updateOrCreate(
                            ['id' => $order_payment->id], 
                            [
                                'status'            => 'uncaptured',
                                'payment_method'    => $cardDetails['type'] ?? null,
                                'card_brand'        => $cardDetails['brand'] ?? null,
                                'card_last4'        => $cardDetails['last4'] ?? null,
                                'card_exp_month'    => $cardDetails['exp_month'] ?? null,
                                'card_exp_year'     => $cardDetails['exp_year'] ?? null,
                            ]);
                    }
                    
                } catch (\Exception $cardError) {
                    \Log::warning('Unable to retrieve card details: ' . $cardError->getMessage());
                }
            } else if ($adv_deposite === "partial") {

                \Log::warning('partial - ' . $order->order_number . ' - Stripe Customer: ' . $stripeCustomer->id);
                $paidAmount = $order->payments()
                    ->where('status', 'succeeded')
                    ->sum('amount');

                $order->total_amount = $previousOrderTotalAmount;
                $totalAmount  = $previousOrderTotalAmount ?? 0;
                $chargeAmount = max(($totalAmount - $paidAmount), 0);
                \Log::warning("Charge Amount - $chargeAmount");
                if ($chargeAmount <= 0) {
                    throw new \Exception('No remaining amount to charge.');
                }
                
                $pi = \Stripe\PaymentIntent::create([
                    'customer' => $stripeCustomer->id,
                    'amount'   => intval(round($chargeAmount * 100)),
                    'currency' => $order->currency,
                    // 'receipt_email' => $data['email'],
                    'description' => '#' . $order->order_number . ' - ' . $tour->title . ' (Remaining Balance)',
                    'statement_descriptor_suffix' => $order->order_number,
                    'metadata' => $metaData,
                    'automatic_payment_methods' => ['enabled' => true],
                    'capture_method' => 'manual',
                    'setup_future_usage'=> 'off_session',
                ]);

                // 3️⃣ Save PI details on order
                $order->payment_intent_client_secret = $pi->client_secret;
                $order->payment_intent_id = $pi->id;

                // 4️⃣ Retrieve payment method details
                $retrievedIntent = \Stripe\PaymentIntent::retrieve($pi->id);
                        
                $paymentMethod = \Stripe\PaymentMethod::retrieve($retrievedIntent->payment_method);
                if ($paymentMethod->type === 'card') {

                    $cardDetails = [
                        'type'      => $paymentMethod->type ?? null,
                        'brand'     => $paymentMethod->card->brand ?? null,
                        'last4'     => $paymentMethod->card->last4 ?? null,
                        'exp_month' => $paymentMethod->card->exp_month ?? null,
                        'exp_year'  => $paymentMethod->card->exp_year ?? null,
                    ];

                    // Optional: store in Order table (if fields exist)
                    // Store full card details as JSON if you have a field for it
                    $order->card_info = json_encode($cardDetails);
                }                

                // 5️⃣ Store payment record
                OrderPayment::create([
                    'order_id'          => $order->id,
                    'payment_intent_id' => $pi->id,
                    'transaction_id'    => null, // no charge yet until capture
                    'payment_method'    => $cardDetails['type'] ?? 'card',
                    'card_brand'        => $cardDetails['brand'] ?? null,
                    'card_last4'        => $cardDetails['last4'] ?? null,
                    'card_exp_month'    => $cardDetails['exp_month'] ?? null,
                    'card_exp_year'     => $cardDetails['exp_year'] ?? null,
                    'amount'            => $order->total_amount,
                    'currency'          => $order->currency,
                    'status'            => 'pending', // manual capture pending
                    'action'            => $adv_deposite,
                    'response_payload'  => json_encode($pi),
                ]);

                $order_actions_notes = $customer->name." paid the remaining amount {$chargeAmount}";
            }

            $booking_fee = $data['booking_fee'];
            if($booking_fee > 0 && get_setting('price_booking_fee')){
                $bookingFeeType = get_setting('tour_booking_fee_type'); 

                if($bookingFeeType === 'FIXED'){
                    $booking_fee = get_setting('tour_booking_fee');
                } elseif($bookingFeeType === 'PERCENT') {
                    $booking_fee = $order->total_amount * get_setting('tour_booking_fee')/100;
                }
            }

            $order->booking_fee = $booking_fee;
            $order->stripe_customer_id = $stripeCustomer->id;
            $order->save();

            $order_actions = [
                'order_id'         => $order->id,
                'performed_by'     => $customer->id,
                'notes'            => $order_actions_notes ?? $customer->name." placed a new order {$order->order_number}",
                'created_at'       => now(),
                'updated_at'       => now()
            ];
            OrderActions::insert($order_actions);           

            return response()->json([
                'status'            => true,
                'message'           => 'Cart updated successfully',
                'data'              => $order,
                'data_detail'       => $order->orderTours,
                'stripe_customer_id'=> $order->stripe_customer_id,
                'payment_intent_id' => $order->payment_intent_id,
                'payment_intent_client_secret' => $order->payment_intent_client_secret,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Cart Update Error: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Cart Update Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Summary of update_error
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update_error(Request $request) {
        $validated = $request->validate([
            'order_id' => 'required|integer|exists:orders,id',
            'payment_intent_id' => 'required'
        ]);

        $order = Order::find($request->order_id);
        if (!$order) {
            return response()->json([
                'status' => false,
                'message' => 'Order not found.'
            ], 404);
        }

        $order->balance_amount     = $order->total_amount;
        $order->booked_amount       = 0;
        $order->updated_at         = now();
        $order->save();

        $order_tour = $order->order_tour;
        $pricing = [];
        $discounts = [];
        $item_total = 0;
        $quantity = 0;
        // Cart Items
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
            
            if($i === 0) {
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
                        'tour_id'  => $request->tourId,
                        'discount' => $depositRule->discount_value ?? 0,
                        'label'    => 'Discount',
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
        $order_tour->save();

        return response()->json([
            'status'   => true,
            'message'  => 'Cart balance updated successfully',
            'data'     => $order,
        ], 200);
    }


    /**
     * Get session time 
     */
    public function getSessionTimes(Request $request)
    {
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
            }elseif ($repeatType === 'MINUTELY') {
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

            // $nextAvailable = $this->getNextAvailableSessions($schedules, $carbonDate, 1); // default only 1
            // return response()->json([
            //     'slots' => [],
            //     'next_available' => $nextAvailable
            // ]);


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
            'data' => array_unique($slots),
            'last_minute' => $lastMinuts,
            'deposit_rule' => $fetchDepositRule,
            'schedule_set' => true
        ]);
    }

    /**
     * Calculate last minute charge based on tour date and time.
     */
    public function getLastMinuteCharge(Request $request, Tour $tour)
    {
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

    public function fetchDeletedSlot($id)
    {
        return ScheduleDeleteSlot::where('tour_id', $id)->get();
        return response()->json(['success' => true, 'message' => 'Slot saved successfully']);
    }

    /**
     * Normalize time string to 24h "HH:MM" for comparison
     */
    function normalizeTime(string $time): string
    {
        return date("H:i", strtotime($time));
    }

    /**
     * Sort slots chronologically (keeps AM/PM format)
     */
    function sortSlots(array $slots): array
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
    function applySlotDeletions(array $response, $storeDeleteSlot): array
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

    private function getNextAvailableSlots($schedule, Carbon $carbonDate, $limit = 1, $durationMinutes = 30, $minimumNoticePeriod = 0, $storeDeleteSlot)
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