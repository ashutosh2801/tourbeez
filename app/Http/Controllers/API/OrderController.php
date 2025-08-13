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

            $image = uploaded_asset($booking->tour->main_image->id ?? 0, 'medium');

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

        // } catch (\Exception $e) {
        //     return response()->json([
        //         'status'  => 'failed',
        //         'message' => $e->getMessage(),
        //     ], 500);
        // }
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
            'order_number'  => unique_order(),
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
            $fees = [];
            $addon_price  = 8;
            $extra_price  = 0;
            $item_total=0;

            foreach ($validated['cartItems'] as $item) {

                if(isset($item['id']) && isset($item['quantity'])) {

                    $price  = floatval($item['price']);
                    $qty    = intval($item['quantity']);

                    $item_price  = $tour->price_type == 'PER_PERSON' ? $price * $qty : $price;
                    $item_total += $item_price;
                    $quantity   += $qty;

                    $pricing[] = [
                        'tour_id'           => $request->tourId,
                        'tour_pricing_id'   => $item['id'],
                        'quantity'          => $item['quantity'],
                        'label'             => $item['label'],
                        'price'             => round($item['price'], 2),
                        'price_type'        => $tour->price_type,
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
                        $value = intval($fee['value']);

                        $tax_fee    = $type === "PERCENT" ? ($item_total * $value)/100 : $value;
                        $item_total+= $tax_fee;

                        $fees[] = [
                            'tour_id'           => $request->tourId,
                            'tour_taxes_id'     => $fee['id'],
                            'label'             => $fee['label'],
                            'price'             => round($tax_fee, 2),
                        ];
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
                'tour_fees'         => json_encode($fees),
                'number_of_guests'  => $quantity,
                'total_amount'      => round($item_total, 2),
            ]);

            $order->number_of_guests = $quantity;
            $order->total_amount = round($item_total, 2);
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
            'selectedTime' => 'nullable',
            'cartItems' => 'required|array|min:1',
            'cartItems.*.id' => 'required|integer',
            'cartItems.*.price' => 'required|numeric',
            'cartItems.*.quantity' => 'required|integer|min:1',
            'cartItems.*.total_price'=> 'required|numeric',
            'cartItems.*.label' => 'required|string',

            'formData.first_name' => 'required|string|max:255',
            'formData.last_name'  => 'required|string|max:255',
            'formData.email'      => 'required|email|max:255',
            'formData.phone'      => 'required|string|max:20',
            'formData.instructions' => 'nullable|string|max:255',
        ]);

        $order = Order::find($request->orderId);
        if (!$order) {
            return response()->json([
                'status' => false,
                'message' => 'Order not found.'
            ], 404);
        }

        try {
            // Save or update customer
            $customer = OrderCustomer::where('order_id', $request->orderId)->first() ?? new OrderCustomer();
            $data = $request->input('formData');

            $customer->order_id     = $order->id;
            $customer->user_id      = $request->userId ?? 0;
            $customer->first_name   = $data['first_name'];
            $customer->last_name    = $data['last_name'];
            $customer->email        = $data['email'];
            $customer->phone        = $data['phone'];
            $customer->instructions = $data['instructions'] ?? '';
            $customer->save();

            $tour = Tour::with(['pricings'])->find($request->tourId);
            if (!$tour) {
                return response()->json([
                    'status' => false,
                    'message' => 'Tour not found.'
                ], 404);
            }

            // Initialize
            $quantity = 0;
            $pricing = [];
            $extra = [];
            $fees = [];
            $item_total = 0;

            // Cart Items
            foreach ($validated['cartItems'] as $item) {
                $qty        = ($item['quantity']);
                $price      = ($item['price']);
                $total      = ($item['total_price']);
                $item_total += $total;
                $quantity += $qty;

                $pricing[] = [
                    'tour_id'           => $request->tourId,
                    'tour_pricing_id'   => $item['id'],
                    'label'             => $item['label'],
                    //'price_type'        => $item['price_type'],
                    'quantity'          => $qty,
                    'price'             => $price,
                    'total_price'       => $total,
                ];
                
            }

            // Add-ons
            if (!empty($request->cartAdons)) {
                foreach ($request->cartAdons as $addon) {
                    if (isset($addon['id'], $addon['quantity'], $addon['price'], $addon['total_price'], $addon['label'])) {
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

            // Update or create OrderTour
            $orderTour = OrderTour::where('order_id', $order->id)->first();
            $tourData = [
                'tour_id'           => $request->tourId,
                'tour_date'         => $validated['selectedDate'],
                // 'tour_time'         => $validated['selectedTime'] ?? null,
                'tour_pricing'      => json_encode($pricing ?? []),
                'tour_extra'        => json_encode($extra ?? []),
                'tour_fees'         => json_encode($fees ?? []),
                'number_of_guests'  => $quantity,
                'total_amount'      => $item_total,
            ];

            if ($orderTour) {
                $orderTour->update($tourData);
            } else {
                $tourData['order_id'] = $order->id;
                OrderTour::create($tourData);
            }

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

            // Final update to main order
            $order->tour_id            = $request->tourId;
            $order->number_of_guests   = $quantity;
            $order->total_amount       = $item_total;
            $order->updated_at         = now();
            $order->save();

            return response()->json([
                'status' => true,
                'message' => 'Cart updated successfully',
                'data' => $order,
                'data_detail' => $order->orderTours
            ], 200);
        } catch (\Exception $e) {
            Log::error('Cart Update Error: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong: ' . $e->getMessage(),
            ], 500);
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
                $query->orWhere(function ($q) use ($carbonDate) {
                          $q->whereDate('session_start_date', '<=', $carbonDate)
                            ->whereDate('until_date', '>=', $carbonDate);
                      });
            })
            ->get();
        // dd($schedules);
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

            // dd($durationMinutes, $schedule->estimated_duration_num, $schedule->estimated_duration_unit);
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
                var_dump($durationMinutes);
                continue;
            }
            // dd(3534);
            $valid = false;
            // dd($repeatType);
            if ($repeatType === 'NONE') {
                $valid = $carbonDate->isSameDay(Carbon::parse($schedule->session_start_date));
                if ($valid) {

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
                    $end = $end->copy()->addMinutes($durationMinutes);
                    $slots = array_merge($slots, $this->generateSlots($start, $end, $durationMinutes, $minimumNoticePeriod));
                    $slots = array_slice($slots, 0, 1);
                }
                
            } elseif ($repeatType === 'WEEKLY') {

                $repeats = TourScheduleRepeats::where('tour_schedule_id', $schedule->id)
                    ->where('day', $dayName)
                    ->get();
                // dd($repeats);
                foreach ($repeats as $repeat) {


                    $weeksSinceStart = floor(Carbon::parse($schedule->session_start_date)->diffInWeeks($carbonDate));
                    $repeatInterval = $schedule->repeat_period_unit ?? 1; // 1 means every week

                    // Skip if not matching the interval
                    if ($weeksSinceStart % $repeatInterval !== 0) {
                        continue;
                    }


                    $selectedDate = Carbon::parse($request->date)->format('Y-m-d'); // or $carbonDate

                    $slotStart = Carbon::parse($selectedDate . ' ' . ($repeat->start_time ?? $schedule->session_start_time));
                    $slotEnd   = Carbon::parse($selectedDate . ' ' . ($repeat->end_time ?? $endTime));

                    $slots = array_merge($slots, $this->generateSlots($slotStart, $slotEnd, $durationMinutes, $minimumNoticePeriod));
                }
                $slots = array_slice($slots, 0, 1);
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
                        $end = Carbon::parse($carbonDate->toDateString() . ' ' . $schedule->session_end_time);

                        $slots = array_merge(
                            $slots,
                            $this->generateSlots($start, $end, $durationMinutes, $minimumNoticePeriod)
                        );
                    }
                    $slots = array_slice($slots, 0, 1);
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
                    $end = $end->copy()->addMinutes($durationMinutes);
                    // dd($start, $end, $durationMinutes, $minimumNoticePeriod);
                    // dd(now(), $minimumNoticePeriod);
                    $slots = array_merge(
                        $slots,


                        $this->generateSlots($start, $end, 60, $minimumNoticePeriod)
                    );
                    $slots = array_slice($slots, 0, 1);
                }
    
                


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

                    foreach ($repeatEntries as $repeat) {
                        $slotStart = Carbon::parse($carbonDate->toDateString() . ' ' . $repeat->start_time);
                        $slotEnd   = Carbon::parse($carbonDate->toDateString() . ' ' . $repeat->end_time);

                        // Check if start time matches the "every X hours" rule
                        $hoursSinceStart = floor($scheduleStartDate->diffInHours($slotStart));
                        if ($hoursSinceStart % $interval !== 0) {
                            continue; // Skip this slot if not matching the interval
                        }

                        $slots = array_merge(
                            $slots,
                            $this->generateSlots($slotStart, $slotEnd, $durationMinutes, $minimumNoticePeriod)
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

    private function generateSlots($start, $end, $durationMinutes, $minimumNoticePeriod)
    {
        $slots = [];

        // Calculate the earliest slot time allowed
        $earliestAllowed = now()->addMinutes($minimumNoticePeriod);
        // dd($earliestAllowed, $start, $start->gte($earliestAllowed), now());
        while ($start->lte($end)) {

            if ($start->gte($earliestAllowed)) {
                $slots[] = $start->format('g:i A');
            }

            $start = $start->copy()->addMinutes($durationMinutes);
        }

        return $slots;
    }
    
}
