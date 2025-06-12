<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderTour;
use App\Models\Tour;
use Illuminate\Http\Request;
use App\models\EmailTemplate;
use App\Mail\EmailManager;
use Mail;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $orders = Order::all();
        return view('admin.order.index', compact(['orders']));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $order = Order::findOrFail( decrypt($id) );
        $tours = Tour::orderBy('title', 'ASC')->get();
         $email_templates = EmailTemplate::all();
        return view('admin.order.edit', compact(['order', 'tours','email_templates']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validator = $request->validate([
            'order_status'   => 'required|max:255',
        ],
        [
            'order_status.required'   => 'Please select order status',
        ]);

        //echo '<pre>'; print_r( $_POST ); exit;
        
        $order = Order::findOrFail( $id );
        $order->order_status    = $request->order_status;
        $order->additional_info = $request->additional_info;

        $tourIds = $request->tour_id; // [19, 21, 90, 11]
        $orderId = $id;
        $total   = 0;
        if($orderId && is_array($request->tour_id)) {

            foreach ($tourIds as $index => $tourId) {
                $startDate = $request->tour_startdate[$index];
                $startTime = $request->tour_starttime[$index];

                //TOUR PRICING
                $pricingIds = $request->input("tour_pricing_id_{$tourId}", []);
                $pricingQtys = $request->input("tour_pricing_qty_{$tourId}", []);
                $pricingPrice = $request->input("tour_pricing_price_{$tourId}", []);

                $pricingDetails = [];
                $total_amount = 0;
                $nog = 0; 
                foreach ($pricingIds as $key => $pricingId) {
                    $qty    = isset($pricingQtys[$key]) ? (int)$pricingQtys[$key] : 0;
                    $price  = isset($pricingPrice[$key]) ? (float)$pricingPrice[$key] : 0;

                    $total_amount += (intval($qty) * floatval($price));
                    $nog += $qty;

                    // Skip all zero-quantity if needed
                    if ($qty <= 0) continue;

                    $pricingDetails[] = [
                        'tour_id'           => $tourId,
                        'tour_pricing_id'   => $pricingId,
                        'quantity'          => $qty,
                        'price'             => $price,
                    ];
                }
                $total += $total_amount;

                //TOUR EXTRA
                $extraIds = $request->input("tour_extra_id_{$tourId}", []);
                $extraQtys = $request->input("tour_extra_qty_{$tourId}", []);
                $extraPrice = $request->input("tour_extra_price_{$tourId}", []);

                $extraDetails = [];
                $total_amount = 0;
                foreach ($extraIds as $key => $extraId) {
                    $qty    = isset($extraQtys[$key]) ? (int)$extraQtys[$key] : 0;
                    $price  = isset($extraPrice[$key]) ? (float)$extraPrice[$key] : 0;

                    $total_amount += (intval($qty) * floatval($price));
                    $nog += $qty;

                    // Skip all zero-quantity if needed
                    if ($qty <= 0) continue;

                    $extraDetails[] = [
                        'tour_id'       => $tourId,
                        'tour_extra_id' => $extraId,
                        'quantity'      => $qty,
                        'price'         => $price,
                    ];
                }
                $total += $total_amount;

                // Update or create based on order_id + tour_id
                $orderTour = OrderTour::where('order_id', $orderId)
                                        ->where('tour_id', $tourId)
                                        ->first();

                if ($orderTour) {
                    $orderTour->update([
                        'tour_date'         => $startDate,
                        'tour_time'         => $startTime,
                        'tour_pricing'      => json_encode($pricingDetails),
                        'tour_extra'        => json_encode($extraDetails),
                        'total_amount'      => $total_amount,
                        'number_of_guests'  => $nog

                    ]);
                } else {
                    $order_tours = new OrderTour();
                    $order_tours->order_id          = $orderId;
                    $order_tours->tour_id           = $tourId;
                    $order_tours->tour_date         = $startDate;
                    $order_tours->tour_time         = $startTime;
                    $order_tours->tour_pricing      = json_encode($pricingDetails);
                    $order_tours->tour_extra        = json_encode($extraDetails);
                    $order_tours->number_of_guests  = $nog;
                    $order_tours->total_amount      = $total_amount;
                    $order_tours->save();
                }

                $tour = Tour::findOrFail( $tourId );
                if($tour) {
                    $taxesfees = $tour->taxes_fees;
                    $subtotal = 0;
                    if( $taxesfees ) {
                        foreach ($taxesfees as $key => $item) { 
                            $price      = get_tax($subtotal, $item->fee_type, $item->tax_fee_value);
                            $tax        = $price ?? 0;
                            $subtotal   = $subtotal + $tax; 
                        }
                        $total += $subtotal;
                    }
                }
            }
        }
        $order->total_amount = $total;
        
        if( !$order->save() )
        return redirect()->back()->withErrors($validator)->withInput()->with('error', 'Something went wrong!');

        return redirect()->back()->withErrors($validator)->withInput()->with('success', 'Order has beend updated!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        //
    }
     public function ordertemplatedetails(Request $request)
    {
    $template=0;
    $orderid = $request->orderid;
    $orderdetail = $request->orderdetail;
    $order = Order::find($orderid);
   $email_template = EmailTemplate::where('identifier', $orderdetail)->first();
   $template = $email_template->body;
    $replacements = [
        '[CUSTOMER_NAME]'     => $order->user->name ?? '',
        '[COMPANY_NAME]'      => config('app.name'), // Or from DB
        '[ORDER_NUMBER]'      => $order->order_number ?? '',
        '[ORDER_STATUS]'      => ucfirst($order->status) ?? '',
        '[ORDER_STATUS_HELP]' => $order->status_help ?? '', // Custom field if available
    ];
    $finalMessage = strtr($template, $replacements);

        if ($order) {
            // Example of returning some data (customize as needed)
            return response()->json([
                'success' => true,
                'email' => $order->user->email,
                'email_template' => $email_template,
                'body'=>$finalMessage,
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Order not found.'
            ], 404);
        }

    }
    public function orderMailSend(Request $request)
    {
        $email = $request->input('email');
        $subject = $request->input('subject');
        $header = $request->input('header');
        $body = $request->input('body');
        if (env('MAIL_USERNAME') != null) {
            $array['view'] = 'emails.newsletter';
            $array['subject'] = $subject;
            $array['header'] = $header;
            $array['from'] = env('MAIL_USERNAME');
            $array['content'] = $body;

            try {
                if(Mail::to($request->email)->queue(new EmailManager($array))){
                    return response()->json(['status' => 'success']);
                }else{
                     return response()->json(['status' => 'Failed']);
                }
                  
            } catch (\Exception $e) {
                dd($e);
            }
        }
        
    }
}
