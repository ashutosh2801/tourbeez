<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Addon;
use App\Models\Category;
use App\Models\City;
use App\Models\Order;
use App\Models\OrderCustomer;
use App\Models\OrderMeta;
use App\Models\OrderTour;
use App\Models\Tour;
use App\Models\TourPricing;
use App\Models\TourSchedule;
use App\Models\TourScheduleRepeats;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
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

        $orders = $query->paginate(20);

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

    public function view(Request $request, $id = 0)
    {
        if( !$id || $id == 0 ) {
            return response()->json([
                'message'    => 'Order not found!',
                'status'    => false,
                'data'  => $request->all()
            ]);
        }

        try {

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

            if ($booking && $booking->order_status !== 1) {
                $booking->order_status   = 1;
                $booking->payment_status = 1;
                $booking->payment_method = $paymentIntent->payment_method_types[0] ?? 'card';
                $booking->updated_at     = now();
                $booking->save();

                // Refresh cache after updating the order
                // Cache::put($cacheKey, $booking->fresh(['tour.location', 'tour.detail', 'tour.addons', 'tour.fees', 'tour.pickups', 'customer']), now()->addMinutes(10));
            }
            
            $tour_pricing = $booking->order_tour->tour_pricing ? json_decode($booking->order_tour->tour_pricing) : [];
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


            $image = uploaded_asset($booking->tour->main_image->id, 'medium');

            $detail = [
                'order_number'      => $booking->order_number,
                'number_of_guests'  => $booking->number_of_guests,
                'total_amount'      => $booking->total_amount,
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
                ],
            ];

            return response()->json([
                'status'  => 'succeeded',
                'booking' => $detail,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'failed',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    

    /**
     * Adding cart
     */
    public function add_to_cart(Request $request) {

        //dd($request->all());
        $validated = $request->validate([
            'tourId'                    => 'required|integer|exists:tours,id',
            'selectedDate'              => 'required|date_format:Y-m-d',
            'cartItems'                 => 'required|array|min:1',
            'cartItems.*.id'            => 'required|integer',
            'cartItems.*.label'         => 'required|string|min:1',
            'cartItems.*.quantity'      => 'required|integer|min:1',
            'cartItems.*.price'         => 'required',
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

        $order = Order::create([
            'tour_id'       => $request->tourId,
            'user_id'       => $request->userId ?? 0,
            'session_id'    => $request->sessionId, // optional if using guest carts
            'order_number'  => unique_code().rand(10,99),
            'currency'      => $request->currency,
            'total_amount'  => $request->tourPrice,
            'order_status'  => 1,
            'created_at'    => date('Y-m-d H:i:s'),
            'updated_at'    => date('Y-m-d H:i:s'),
        ]);

        if($order) {

            $orderId = $order->id;
            $quantity = 0;
            $pricing = [];
            $extra = [];
            $addon_price  = 8;
            $extra_price  = 0;
            $item_total=0;

            foreach ($validated['cartItems'] as $item) {

                if(isset($item['id']) && isset($item['quantity'])) {

                    $pricing[] = [
                        'tour_id'           => $request->tourId,
                        'tour_pricing_id'   => $item['id'],
                        'quantity'          => $item['quantity'],
                        'label'             => $item['label'],
                        'price'             => $item['price'],
                        //'total_price'       => $item['total_price']
                    ];

                    // $price  = floatval($item['price']);
                    // $qty    = intval($item['quantity']);

                    // $item_price  = $price * $qty;
                    // $item_total += $item_price;
                    // $quantity   += $qty;

                    // $pricing[] = [
                    //     'tour_id'           => $request->tourId,
                    //     'tour_pricing_id'   => $item['id'],
                    //     'quantity'          => $qty,
                    //     'price'             => $item_price
                    // ];
                }
                
            }

            if( isset($request->cartAdons) && !empty($request->cartAdons) ) {
                foreach ($request->cartAdons as $addon) {
                    if(isset($addon['id']) && isset($addon['quantity'])) {

                        $extra[] = [
                            'tour_id'           => $request->tourId,
                            'tour_extra_id'     => $addon['id'],
                            'quantity'          => $addon['quantity'],
                            'label'             => $addon['label'],
                            'price'             => $addon['price'],
                            //'total_price'       => $addon['total_price']
                        ];

                        // $price  = floatval($addon['price']);
                        // $qty    = intval($addon['quantity']);

                        // $extra_price  = $price * $qty;
                        // $item_total  += $extra_price;

                        // $extra[] = [
                        //     'tour_id'           => $request->tourId,
                        //     'tour_extra_id'     => $addon['id'],
                        //     'quantity'          => $qty,
                        //     'price'             => $extra_price
                        // ];
                    }
                }
            }

            OrderTour::create([
                'order_id'          => $orderId,
                'tour_id'           => $request->tourId, // mandatory
                'tour_date'         => $validated['selectedDate'],
                'tour_time'         => $validated['selectedTime'] ?? null,
                'tour_pricing'      => json_encode($pricing),
                'tour_extra'        => json_encode($extra),
                'number_of_guests'  => $quantity,
                'total_amount'      => $item_total,
            ]);

            $order->number_of_guests = $quantity;
            //$order->total_amount = $item_total;
            $order->save();

            return response()->json([
                'status'        => true,
                'message'       => 'Item added in cart',
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
    public function update_cart(Request $request)
    {

        $validated = $request->validate([
            'orderId' => 'required|integer|exists:orders,id',
            'tourId' => 'required|integer|exists:tours,id',
            'selectedDate' => 'required|date_format:Y-m-d',
            
            'cartItems' => 'required|array|min:1',
            'cartItems.*.id' => 'required|integer',
            'cartItems.*.price' => 'required|numeric',
            'cartItems.*.quantity' => 'required|integer|min:1',
            'cartItems.*.total_price'=> 'required',
            'formData.first_name' => 'required|string|max:255',
            'formData.last_name'  => 'required|string|max:255',
            'formData.email'      => 'required|email|max:255',
            'formData.phone'      => 'required|string|max:20',
            'formData.instructions' => 'nullable|string|max:20',
        ]);
 
        $order = Order::find($request->orderId);
        if (!$order) {
            return response()->json([
                'status' => false,
                'message' => 'Order not found.'
            ], 404);
        }


        // $customer = Tour::where('order_id', $request->orderId)->first();
        // if(!$customer) {
        //     $customer = new OrderCustomer();
        // }
        $customer = new OrderCustomer();
        $data = $request->input('formData');



        $customer->order_id     = $order->id;
        $customer->user_id      = $request->userId;
        $customer->first_name   = $data['first_name'];
        $customer->last_name    = $data['last_name'];
        $customer->email        = $data['email'];
        $customer->phone        = $data['phone'];
        $customer->instructions = $data['instructions'] ?? NULL;
        $customer->save();

        $tour = Tour::with(['pricings'])->where('id', $request->tourId)->first();
        if (!$tour) {
            return response()->json([
                'status' => false,
                'message' => 'Tour not found.'
            ], 404);
        }

        $quantity = 0;
        $pricing = [];
        $extra = [];
        $item_total = 0;

        foreach ($validated['cartItems'] as $item) {
            if (isset($item['id']) && isset($item['quantity'])) {
                $price = floatval($item['price']);
                $qty = intval($item['quantity']);
                $item_price = $price * $qty;
                $item_total += $item_price;
                $quantity += $qty;

                $pricing[] = [
                    'tour_id' => $request->tourId,
                    'tour_pricing_id' => $item['id'],
                    'quantity' => $qty,
                    'price' => $item_price
                ];
            }
        }

        try {
 
            $customer = OrderCustomer::where('order_id', $request->orderId)->first();
            if(!$customer) {
                $customer = new OrderCustomer();
            }
            //$customer = new OrderCustomer();
            $data = $request->input('formData'); 
    
            if($request->userId != 0){
                $userId = $request->userId;
            } else{
                $userId = 0;
            }
            $customer->order_id     = $order->id;
            $customer->user_id      = $userId;
            $customer->first_name   = $data['first_name'];
            $customer->last_name    = $data['last_name'];
            $customer->email        = $data['email'];
            $customer->phone        = $data['phone'];
            $customer->instructions = $data['instructions'] ?? '';
            // $customer->pickup_id    = $data['pickup_id'] ?? 0;
            // $customer->pickup_name  = $data['pickup_name'] ?? '';
            $customer->save();
    
            $tour = Tour::with(['pricings'])->where('id', $request->tourId)->first();
            if (!$tour) {
                return response()->json([
                    'status' => false,
                    'message' => 'Tour not found.'
                ], 404);
            }
    
            $quantity = 0;
            $pricing = [];
            $extra = [];
            $item_total = 0;
    
            foreach ($validated['cartItems'] as $item) {
                if (isset($item['id']) && isset($item['quantity'])) {
                    $pricing[] = [
                        'tour_id'           => $request->tourId,
                        'tour_pricing_id'   => $item['id'],
                        'quantity'          => $item['quantity'],
                        'label'             => $item['label'],
                        'price'             => $item['price'],
                        'total_price'       => $item['total_price']
                    ];

                    // $qty = $item['quantity'] ?? 0;
                    // $price = floatval($item['price']);
                    // $qty = intval($qty);
                    $qty    = intval($item['quantity']);
                    // $item_price = $price * $qty;
                    // $item_total += $item_price;
                    $quantity += $qty;
    
                    // $pricing[] = [
                    //     'tour_id' => $request->tourId,
                    //     'tour_pricing_id' => $item['id'],
                    //     'quantity' => $qty,
                    //     'price' => $item_price
                    // ];
                }
            }
    
            if (isset($request->cartAdons) && !empty($request->cartAdons)) {
                foreach ($request->cartAdons as $addon) {
                    if (isset($addon['id']) && isset($addon['quantity'])) {
                        $extra[] = [
                            'tour_id'           => $request->tourId,
                            'tour_extra_id'     => $addon['id'],
                            'quantity'          => $addon['quantity'],
                            'label'             => $addon['label'],
                            'price'             => $addon['price'],
                            'total_price'       => $addon['total_price']
                        ];
                        // $price = floatval($addon['price']);
                        // $qty = intval($addon['quantity']);
                        // $extra_price = $price * $qty;
                        // $item_total += $extra_price;
    
                        // $extra[] = [
                        //     'tour_id' => $request->tourId,
                        //     'tour_extra_id' => $addon['id'],
                        //     'quantity' => $qty,
                        //     'price' => $extra_price
                        // ];
                    }
                }
            }
    
            $orderTour = OrderTour::where('order_id', $order->id)->first();
            if ($orderTour) {
                $orderTour->tour_id         = $request->tourId;
                $orderTour->tour_date       = $validated['selectedDate'];
                $orderTour->tour_time       = $validated['selectedTime'] ?? null;
                $orderTour->tour_pricing    = json_encode($pricing);
                $orderTour->tour_extra      = json_encode($extra);
                $orderTour->number_of_guests= $quantity;
                $orderTour->total_amount    = $item_total;
                $orderTour->save();
            } else {
                OrderTour::create([
                    'order_id'          => $order->id,
                    'tour_id'           => $request->tourId,
                    'tour_date'         => $validated['selectedDate'],
                    'tour_time'         => $validated['selectedTime'] ?? null,
                    'tour_pricing'      => json_encode($pricing),
                    'tour_extra'        => json_encode($extra),
                    'number_of_guests'  => $quantity,
                    'total_amount'      => $item_total,
                ]);
            }

            if (isset($request->cartMetas) && !empty($request->cartMetas)) {
                foreach ($request->cartMetas as $fee) {
                    if (isset($fee['name']) && isset($fee['value'])) {
                        OrderMeta::create([
                            'order_id'  => $order->id,
                            'name'      => $fee['name'],
                            'value'     => $fee['value']
                        ]);
                    }
                }
            }
    
            $order->tour_id         = $request->tourId;
            $order->number_of_guests= $quantity;
            $order->total_amount    = $item_total;
            $order->updated_at      = date('Y-m-d H:i:s');
            $order->save();
    
            return response()->json([
                'status' => true,
                'message' => 'Cart updated successfully',
                'data' => $order,
                'data_detail' => $order->orderTours
            ], 200);
        }
        catch (\Exception $e) {
            Log::info($e);
            return response()->json([
                'status' => true,
                'message' => $e->getMessage(),
            ], 401);
        }
    }


    public function getSessionTimes(Request $request)
    {


        $carbonDate = Carbon::parse($request->date);
        $date = $request->date;

        $dayName = $carbonDate->format('l');
        $slots = [];

        $schedules = TourSchedule::where('tour_id', $request->tour_id)
            ->where(function ($query) use ($carbonDate) {
                $query->where('repeat_period', 'MONTHLY')
                      ->orWhere('repeat_period', 'YEARLY')
                      ->orWhere(function ($q) use ($carbonDate) {
                          $q->whereDate('session_start_date', '<=', $carbonDate)
                            ->whereDate('until_date', '>=', $carbonDate);
                      });
            })
            ->get();
        
        foreach ($schedules as $schedule) {
            $durationMinutes = match (strtolower($schedule->minimum_notice_unit)) {
                'minute', 'minutes' => $schedule->minimum_notice_num,
                'hour', 'hours' => $schedule->minimum_notice_num * 60,
                default => 0
            };

            $startTime = $schedule->session_start_time ?? '00:00';
            $endTime = $schedule->session_end_time ?? '23:59';

            if ($schedule->sesion_all_day) {
                $startTime = '00:00';
                $endTime = '23:59';
            }

            $repeatType = strtoupper($schedule->repeat_period);
            $start = Carbon::parse($startTime);
            $end = Carbon::parse($endTime);

            if ($durationMinutes <= 0 || $start->gte($end)) {
                continue;
            }

            $valid = false;
            // dd($repeatType);
            if ($repeatType === 'NONE') {
                $valid = $carbonDate->isSameDay(Carbon::parse($schedule->session_start_date));
                if ($valid) {
                    $slots = array_merge($slots, $this->generateSlots($start, $end, $durationMinutes, $schedule->id, null));
                }
            } elseif ($repeatType === 'DAILY') {
                $slots = array_merge($slots, $this->generateSlots($start, $end, $durationMinutes, $schedule->id, null));
            } elseif ($repeatType === 'WEEKLY') {
                $repeats = TourScheduleRepeats::where('tour_schedule_id', $schedule->id)
                    ->where('day', $dayName)
                    ->get();

                foreach ($repeats as $repeat) {
                    $slotStart = Carbon::parse($repeat->start_time ?? $startTime);
                    $slotEnd = Carbon::parse($repeat->end_time ?? $endTime);
                    $slots = array_merge($slots, $this->generateSlots($slotStart, $slotEnd, $durationMinutes, $schedule->id, $repeat->id));
                }
            } elseif ($repeatType === 'MONTHLY') {
                $startDate = Carbon::parse($schedule->session_start_date);

                // Match same day of the month
                if ((int)$carbonDate->format('d') === (int)$startDate->format('d')) {
                    $start = Carbon::parse($carbonDate->toDateString() . ' ' . $schedule->session_start_time);
                    $end = Carbon::parse($carbonDate->toDateString() . ' ' . $schedule->session_end_time);

                    $slots = array_merge(
                        $slots,
                        $this->generateSlots($start, $end, $durationMinutes, $schedule->id, null)
                    );
                }
            } elseif ($repeatType === 'YEARLY') {
                $startDate = Carbon::parse($schedule->session_start_date);

                // Match same day and same month
                if (
                    (int)$carbonDate->format('d') === (int)$startDate->format('d') &&
                    (int)$carbonDate->format('m') === (int)$startDate->format('m')
                ) {
                    $start = Carbon::parse($carbonDate->toDateString() . ' ' . $schedule->session_start_time);
                    $end = Carbon::parse($carbonDate->toDateString() . ' ' . $schedule->session_end_time);

                    $slots = array_merge(
                        $slots,
                        $this->generateSlots($start, $end, $durationMinutes, $schedule->id, null)
                    );
                }
            }elseif ($repeatType === 'MINUTELY') {
                $interval = $schedule->repeat_period_unit; // e.g., every 15 minutes
                $scheduleStartDate = Carbon::parse($schedule->session_start_date);
                $scheduleEndDate = Carbon::parse($schedule->until_date);

                // Check if the selected date is between session_start_date and until_date
                if ($carbonDate->between($scheduleStartDate, $scheduleEndDate)) {
                    $dayName = $carbonDate->format('l'); // e.g., 'Monday'

                    $repeatEntries = TourScheduleRepeats::where('tour_schedule_id', $schedule->id)
                        ->where('day', $dayName)
                        ->get();

                    foreach ($repeatEntries as $repeat) {
                        $start = Carbon::parse($carbonDate->toDateString() . ' ' . $repeat->start_time);
                        $end = Carbon::parse($carbonDate->toDateString() . ' ' . $repeat->end_time);

                        $slots = array_merge(
                            $slots,
                            $this->generateSlots($start, $end, $durationMinutes, $schedule->id, $repeat->id, $interval, 'minutes')
                        );
                    }
                }
            }
            elseif ($repeatType === 'HOURLY') {

                $interval = $schedule->repeat_period_unit; // e.g., every 2 hours
                $scheduleStartDate = Carbon::parse($schedule->session_start_date);
                $scheduleEndDate = Carbon::parse($schedule->until_date);

                if ($carbonDate->between($scheduleStartDate, $scheduleEndDate)) {
                    $dayName = $carbonDate->format('l'); // e.g., 'Tuesday'

                    $repeatEntries = TourScheduleRepeats::where('tour_schedule_id', $schedule->id)
                        ->where('day', $dayName)
                        ->get();

                    foreach ($repeatEntries as $repeat) {
                        $start = Carbon::parse($carbonDate->toDateString() . ' ' . $repeat->start_time);
                        $end = Carbon::parse($carbonDate->toDateString() . ' ' . $repeat->end_time);

                        $slots = array_merge(
                            $slots,
                            $this->generateSlots($start, $end, $durationMinutes, $schedule->id, $repeat->id, $interval, 'hours')
                        );
                    }
                }
            }
        }
        
        if (empty($slots)) {
            return response()->json([
                'status' => 'error',
                'message' => 'No sessions available on this date.'
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data' => array_unique($slots)
        ]);
    }

    private function generateSlots($start, $end, $durationMinutes, $scheduleId, $repeatId = null)
    {
        $slots = [];

        while ($start->lt($end)) {
            $slotEnd = $start->copy()->addMinutes($durationMinutes);

            if ($slotEnd->gt($end)) {
                break;
            }


            $slots[] = 
                $start->format('g:i A');

            $start = $slotEnd;
        }

        return $slots;
    }
    
}
