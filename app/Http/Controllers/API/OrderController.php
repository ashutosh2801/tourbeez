<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\City;
use App\Models\Order;
use App\Models\OrderCustomer;
use App\Models\OrderTour;
use App\Models\Tour;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, $id = 0)
    {
        if( $id == 0 ) {
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
    

    /**
     * Adding cart
     */
    public function add_to_cart(Request $request) {

        //dd($request->all());
        $validated = $request->validate([
            'tourId' => 'required|integer|exists:tours,id',
            'selectedDate' => 'required|date_format:Y-m-d',
            'cartItems' => 'required|array|min:1',
            'cartItems.*.id' => 'required|integer',
            'cartItems.*.price' => 'required',
            'cartItems.*.quantity' => 'required|integer|min:1',
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
            'order_status'  => 0,
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

                    $price  = floatval($item['price']);
                    $qty    = intval($item['quantity']);

                    $item_price  = $price * $qty;
                    $item_total += $item_price;
                    $quantity   += $qty;

                    $pricing[] = [
                        'tour_id'           => $request->tourId,
                        'tour_pricing_id'   => $item['id'],
                        'quantity'          => $qty,
                        'price'             => $item_price
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
                            'tour_extra_id'     => $item['id'],
                            'quantity'          => $qty,
                            'price'             => $extra_price
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
                'number_of_guests'  => $quantity,
                'total_amount'      => $item_total,
            ]);

            $order->number_of_guests = $quantity;
            $order->total_amount = $item_total;
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
        return response()->json($request->all());

        $validated = $request->validate([
            'orderId' => 'required|integer|exists:orders,id',
            'tourId' => 'required|integer|exists:tours,id',
            'selectedDate' => 'required|date_format:Y-m-d',
            'cartItems' => 'required|array|min:1',
            'cartItems.*.id' => 'required|integer',
            'cartItems.*.price' => 'required',
            'cartItems.*.quantity' => 'required|integer|min:1',
            'first_name'  => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'  => 'required|string|max:255',
            'phone'  => 'required',
        ]);

        $order = Order::find($request->orderId);
        if (!$order) {
            return response()->json([
                'status' => false,
                'message' => 'Order not found.'
            ], 404);
        }

        $customer = Tour::where('order_id', $request->orderId)->first();
        if(!$customer) {
            $customer = new OrderCustomer();
        }
        $data = $request->input('formData');

        $customer->order_id     = $order->id;
        $customer->user_id      = $request->userId;
        $customer->first_name   = $data->first_name;
        $customer->last_name    = $data->last_name;
        $customer->email        = $data->email;
        $customer->phone        = $data->phone;
        $customer->instructions = $data->instructions;
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

        if (isset($request->cartAdons) && !empty($request->cartAdons)) {
            foreach ($request->cartAdons as $addon) {
                if (isset($addon['id']) && isset($addon['quantity'])) {
                    $price = floatval($addon['price']);
                    $qty = intval($addon['quantity']);
                    $extra_price = $price * $qty;
                    $item_total += $extra_price;

                    $extra[] = [
                        'tour_id' => $request->tourId,
                        'tour_extra_id' => $addon['id'],
                        'quantity' => $qty,
                        'price' => $extra_price
                    ];
                }
            }
        }

        $orderTour = OrderTour::where('order_id', $order->id)->first();
        if ($orderTour) {
            $orderTour->tour_id = $request->tourId;
            $orderTour->tour_date = $validated['selectedDate'];
            $orderTour->tour_time = $validated['selectedTime'] ?? null;
            $orderTour->tour_pricing = json_encode($pricing);
            $orderTour->tour_extra = json_encode($extra);
            $orderTour->number_of_guests = $quantity;
            $orderTour->total_amount = $item_total;
            $orderTour->save();
        } else {
            OrderTour::create([
                'order_id' => $order->id,
                'tour_id' => $request->tourId,
                'tour_date' => $validated['selectedDate'],
                'tour_time' => $validated['selectedTime'] ?? null,
                'tour_pricing' => json_encode($pricing),
                'tour_extra' => json_encode($extra),
                'number_of_guests' => $quantity,
                'total_amount' => $item_total,
            ]);
        }

        $order->tour_id = $request->tourId;
        $order->number_of_guests = $quantity;
        $order->total_amount = $item_total;
        $order->updated_at = date('Y-m-d H:i:s');
        $order->save();

        return response()->json([
            'status' => true,
            'message' => 'Cart updated successfully',
            'data' => $order,
            'data_detail' => $order->orderTours
        ], 200);
    }
    
}
