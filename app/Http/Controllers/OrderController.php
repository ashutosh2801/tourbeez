<?php

namespace App\Http\Controllers;

use App\Exports\ManifestExport;
use App\Mail\EmailManager;
use App\Models\Addon;
use App\Models\EmailTemplate;
use App\Models\Order;
use App\Models\OrderCustomer;
use App\Models\OrderEmailHistory;
use App\Models\OrderPayment;
use App\Models\OrderTour;
use App\Models\SmsTemplate;
use App\Models\Tour;
use App\Models\TourPricing;
use App\Models\TourSpecialDeposit;
use App\Models\User;
use App\Notifications\NewOrderNotification;
use App\Services\TwilioService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Stripe\PaymentIntent;
use Stripe\Refund;
use Stripe\Stripe;



class OrderController extends Controller
{

//     use App\Models\Order;
// use App\Models\Tour;
// use Illuminate\Http\Request;

    public function index(Request $request)
    {
        $query = Order::with(['customer', 'orderTours.tour'])
            ->whereHas('customer', function ($q) {
                $q->whereNotNull('first_name')
                  ->where('first_name', '!=', ''); // exclude empty strings
            })
            ->orderBy('created_at', 'DESC');

        // Search by order number or customer name
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($q2) use ($search) {
                      $q2->where('first_name', 'like', "%{$search}%")
                         ->orWhere('last_name', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by tour product
        if ($product = $request->input('product')) {

            $query->whereHas('orderTours', function ($q) use ($product) {
                $q->where('tour_id', $product);
            });
        }

        // Filter by payment status
        if ($paymentStatus = $request->input('payment_status')) {
            $query->where('payment_status', $paymentStatus);
        }

        // Filter by order status


        if ($orderStatus = $request->input('order_status')) {
            $query->where('order_status', $orderStatus);
        }

        // Filter by tour start date range
        if ($start = $request->input('tour_start_date')) {
            // dd($request->input('tour_start_date'));
            $query->whereHas('orderTours', function ($q) use ($start) {

                $q->whereDate('tour_date', '=', $start);
            });
        }

        
        if ($filter = $request->input('date_filter')) {
            $today = Carbon::today();

            switch ($filter) {
                case 'last_7':
                    $from = $today->copy()->subDays(6); // today + last 6
                    break;
                case 'last_15':
                    $from = $today->copy()->subDays(14);
                    break;
                case 'this_month':
                    $from = $today->copy()->startOfMonth();
                    break;
                case 'last_90':
                    $from = $today->copy()->subDays(89);
                    break;
                case 'last_6_months':
                    $from = $today->copy()->subMonths(5)->startOfMonth(); // inclusive of current
                    break;
                case 'this_year':
                    $from = $today->copy()->startOfYear();
                    break;
                default:
                    $from = null;
            }

            if (isset($from)) {
                $query->whereDate('created_at', '>=', $from);
            }
        }

        if ($tourFilter = $request->input('tour_date_filter')) {
            $today = Carbon::today();

            switch ($tourFilter) {
                case 'last_7':
                    $from = $today->copy()->subDays(6);
                    break;
                case 'last_15':
                    $from = $today->copy()->subDays(14);
                    break;
                case 'this_month':
                    $from = $today->copy()->startOfMonth();
                    break;
                case 'last_90':
                    $from = $today->copy()->subDays(89);
                    break;
                case 'last_6_months':
                    $from = $today->copy()->subMonths(5)->startOfMonth();
                    break;
                case 'this_year':
                    $from = $today->copy()->startOfYear();
                    break;
                default:
                    $from = null;
            }

            if (isset($from)) {
                $query->whereHas('orderTours', function ($q) use ($from) {
                    $q->whereDate('tour_date', '>=', $from);
                });
            }
        }



        $orders = $query->paginate(10)->appends($request->all()); // preserve filters in pagination

        $products = Tour::select('id', 'title')->get(); // for filter dropdown

        return view('admin.order.index', compact('orders', 'products'));
    }


    public function showPdfFiles()
    {
        
        $pdfFiles = Storage::disk('s3')->files('rezdy-manifest'); // or 'your-folder/' if needed

        // Filter only .pdf files
        $pdfFiles = array_filter($pdfFiles, function ($file) {
            return str_ends_with($file, '.pdf');
        });

        // Get URLs
        $pdfUrls = array_map(function ($file) {
            return [
                'name' => basename($file),
                'path' => $file,
                'url' => Storage::disk('s3')->url($file)
            ];
        }, $pdfFiles);

        return view('admin.order.pdfs', compact('pdfUrls'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // $products = Tour::select('id', 'title', 'slug')->get();
        // $tours = $products;

        $tours = Tour::with('pricings', 'addons', 'taxes_fees', 'pickups', 'location.country', 'location.state', 'location.city')->get();
        $customers = User::all();

        return view('admin.order.internal-order', compact('tours', 'customers'));
    }

    /**
     * Store a newly created resource in storage.
     */
public function store(Request $request)
{



    // dd($request->customer_id);

    $request->merge([
    'customer_id' => $request->customer_id ?: null
]);
    // dd($request->all());
    $validated = $request->validate([

        // Existing Customer
        'customer_id'          => 'nullable',

            // New Customer Fields (conditionally required)
        'customer_first_name'  => 'exclude_unless:customer_id,null|required|string|max:100',
        'customer_last_name'   => 'exclude_unless:customer_id,null|required|string|max:100',
        'customer_email'       => 'exclude_unless:customer_id,null|required|email|max:150',
        'customer_phone'       => 'exclude_unless:customer_id,null|required|string|max:50',

        // Tours
        'tour_id'              => 'required|array|min:1',
        'tour_id.*'            => 'integer|exists:tours,id',

        'tour_startdate'       => 'required|array|min:1',
        'tour_startdate.*'     => 'date',

        'tour_starttime'       => 'required|array|min:1',
        'tour_starttime.*'     => 'string',

        'additional_info'      => 'nullable|string|max:500',

    ], [

        // Customer Validation Messages
        'customer_id.required'             => 'Please select an existing customer.',
        'customer_first_name.required_without' => 'First name is required when no existing customer is selected.',
        'customer_last_name.required_without'  => 'Last name is required when no existing customer is selected.',
        'customer_email.required_without'      => 'Email is required when no existing customer is selected.',
        'customer_phone.required_without'      => 'Phone is required when no existing customer is selected.',

        // Tours
        'tour_id.required'          => 'At least one tour must be selected.',
        'tour_startdate.required'   => 'Please provide a start date.',
        'tour_starttime.required'   => 'Please provide a start time.',
    ]);

    // if ($validated->fails()) {
    //     return redirect()->back()->withErrors($validated)->withInput();
    // }


    $data = $request->all();

    // Make sure at least one tour is selected
    $tourIds = array_unique($data['tour_id']) ?? [];
    if (empty($tourIds)) {
        return response()->json([
            'status' => false,
            'message' => 'No tour selected.',
        ], 422);
    }

    $firstTourId = $tourIds[0]; // Keep this for orders.tour_id foreign key

    DB::beginTransaction();
    try {
        // ===== Create Order =====
        $order = Order::create([
            'tour_id'       => $firstTourId,
            'user_id'       => auth()->id() ?? 0,
            'order_number'  => unique_order(),
            'currency'      => $request->currency ?? 'CAD',
            'order_status'  => $request->order_status,
            'payment_status'=> 5,
            'total_amount'  => 0,
            'balance_amount'=> 0,
            'created_by'    => auth()->user()->id,
        ]);

        // ===== Customer =====
        $customerId = $request->customer_id ?? null;

        if ($customerId) {
            // Fetch from users table
            $user = User::find($customerId);

            $fullName = $user->name ?? 'N/A';
            $nameParts = explode(' ', $fullName, 2);

            $firstName = $nameParts[0] ?? 'N/A';
            $lastName  = $nameParts[1] ?? ''; // empty if only one name provided

            $customer = OrderCustomer::create([
                'order_id'     => $order->id,
                'user_id'      => $user->id,
                'first_name'   => $firstName,
                'last_name'    => $lastName,
                'email'        => $user->email ?? 'N/A',
                'phone'        => $user->phone ?? 'N/A',
                'instructions' => $request->additional_info ?? '',
                'pickup_id'    => $request->pickup_id ?? '',
                'pickup_name'  => $request->pickup_name ?? '',
            ]);
        } else {
            // Fallback to request inputs

            $user = User::where('email', $request->customer_email)->first();

            $user_id = $user?->id ?? 0;

            $customer = OrderCustomer::create([
                'order_id'     => $order->id,
                'user_id'      => $user_id,
                'first_name'   => $request->customer_first_name ?? 'N/A',
                'last_name'    => $request->customer_last_name ?? 'N/A',
                'email'        => $request->customer_email ?? 'N/A',
                'phone'        => $request->full_phone ?? 'N/A',
                'instructions' => $request->additional_info ?? '',
                'pickup_id'    => $request->pickup_id ?? '',
                'pickup_name'  => $request->pickup_name ?? '',
            ]);
        }

        // ===== Loop Through Tours =====
        $totalOrderAmount = 0;
        foreach ($tourIds as $index => $tourId) {
            $tour = Tour::with(['pricings', 'addons', 'taxes_fees'])->findOrFail($tourId);

            $tour_pricing_ids  = $data["tour_pricing_id_$tourId"] ?? [];
            $tour_pricing_qtys = $data["tour_pricing_qty_$tourId"] ?? [];
            $tour_pricing_prices = $data["tour_pricing_price_$tourId"] ?? [];

            $tour_extra_ids    = $data["tour_extra_id_$tourId"] ?? [];
            $tour_extra_qtys   = $data["tour_extra_qty_$tourId"] ?? [];
            $tour_extra_prices = $data["tour_extra_price_$tourId"] ?? [];

            $pricing = [];
            $extras  = [];
            $subtotal = 0;
            $quantity = 0;

            // ===== Pricing =====
            foreach ($tour_pricing_ids as $i => $pricingId) {
                $qty   = $tour_pricing_qtys[$i] ?? 0;
                $price = $tour_pricing_prices[$i] ?? 0;

                $tourPricing = TourPricing::find($pricingId);
                $label = str_ireplace('Group', 'Participants', $tourPricing->label);
                $pricing[] = [
                    'tour_id'         => $tour->id,
                    'label'           => $label,
                    'tour_pricing_id' => $pricingId,
                    'quantity'        => $qty,
                    'price'           => $price,
                    'total_price'     => $tour->price_type == 'FIXED'? $price :  $qty * $price,
                ];
                $subtotal += $tour->price_type == 'FIXED'? $price :  $qty * $price;// $qty * $price;
                $quantity += $qty;
            }


            

            // ===== Extras =====
            foreach ($tour_extra_ids as $i => $extraId) {
                $qty   = $tour_extra_qtys[$i] ?? 0;
                $price = $tour_extra_prices[$i] ?? 0;

                $extraAddon = Addon::find($extraId);
                $extras[] = [
                    'tour_id'       => $tour->id,
                    'label'         => $extraAddon->name,
                    'tour_extra_id' => $extraId,
                    'quantity'      => $qty,
                    'price'         => $price,
                    'total_price'   => $qty * $price,
                ];
                $subtotal += $qty * $price;
            }
            
            // ===== Taxes & Fees =====
            $fees = [];
            if ($tour->taxes_fees) {
                foreach ($tour->taxes_fees as $fee) {
                    $feePrice = get_tax($subtotal, $fee->fee_type, $fee->tax_fee_value);
                    $fees[] = [
                        'tour_taxes_id' => $fee->id,
                        'label'         => $fee->label,
                        'type'          => $fee->fee_type,
                        'value'         => $fee->tax_fee_value,
                        'price'         => $feePrice,
                    ];
                    $subtotal += $feePrice;
                }
            }

            $totalOrderAmount += $subtotal;

            $tourStartDates = $request->input('tour_startdate', []);
            $tourStartTimes = $request->input('tour_starttime', []);

            // For the current tour row (index 0 if only one tour)
            $selectedDate = $tourStartDates[0] ?? null;
            $selectedTime = $tourStartTimes[0] ?? null;

            // ===== Save OrderTour =====
            OrderTour::create([
                'order_id'         => $order->id,
                'tour_id'          => $tourId,
                'tour_date'         => $selectedDate,
                'tour_time'         => $selectedTime,
                'tour_pricing'     => json_encode($pricing),
                'tour_extra'       => json_encode($extras),
                'tour_fees'        => json_encode($fees),
                'number_of_guests' => $quantity,
                'total_amount'     => $subtotal,

                'tour_date'         => $selectedDate,
                'tour_time'         => $selectedTime,
            ]);
        }

        // ===== Update Order totals =====
        $order->total_amount = $totalOrderAmount;
        $order->balance_amount = $totalOrderAmount;
        
        // $order->payment_type = $request->payment_type;
        $order->save();
        // dd($request->payment_type);
        if($request->payment_type){
            


          if($request->payment_type == 'transaction'){
                    // $order->payment_type = $request->payment_type;
                    $order->booked_amount = $totalOrderAmount;
                    $order->balance_amount = 0;
                    $order->transaction_id = $request->transaction_id;
                    $order->payment_method = $request->payment_method;
                    $order->save();
                } else {
            // ===== Stripe Payment Handling =====
            try {
                \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

                // Check if customer already linked
                if (!$order->stripe_customer_id) {
                    $name = $customer->first_name.' '.$customer->last_name;
                    $stripeCustomer = \Stripe\Customer::create([
                        'name'  => $name,
                        'email' => $customer->email,
                        'phone' => $customer->phone,
                    ]);
                } else {
                    $stripeCustomer = \Stripe\Customer::retrieve($order->stripe_customer_id);
                }

                $adv_deposite = $request->adv_deposite ?? 'full'; // or deposit/full

                $metaData = [
                    'bookedDate'    => $order->created_at,
                    'orderId'       => $order->id,
                    'orderNumber'   => $order->order_number,
                    'tourName'      => $tour->title ?? 'Multiple Tours',
                    'customerId'    => $customer->id,
                    'customerEmail' => $customer->email,
                    'customerName'  => $customer->first_name.' '.$customer->last_name,
                    'planName'      => "TourBeez Plan",
                    'status'        => 'Pending supplier',
                    'totalAmount'   => $order->total_amount,
                ];

                $chargeAmount = 0;

                // if ($adv_deposite == "deposit") {
                    $depositRule = \App\Models\TourSpecialDeposit::where('tour_id', $firstTourId)->first();
                    if(!$depositRule){
                        $depositRule = \App\Models\TourSpecialDeposit::where('type', 'global')->first();
                    }
                    if ($depositRule && $depositRule->use_deposit) {
                        switch ($depositRule->charge) {
                            case 'FULL':
                                $chargeAmount = $order->total_amount;
                                break;
                            case 'DEPOSIT_PERCENT':
                                $chargeAmount = $order->total_amount * ($depositRule->deposit_amount / 100);
                                break;
                            case 'DEPOSIT_FIXED':
                            case 'DEPOSIT_FIXED_PER_ORDER':
                                $chargeAmount = $depositRule->deposit_amount;
                                break;
                            case 'NONE':
                                $chargeAmount = 0;
                                break;
                        }
                    } else {
                        $chargeAmount = $order->total_amount; // fallback full
                    }
                // } 
                // else {
                //     $chargeAmount = $order->total_amount; // full payment
                // }

                if ($chargeAmount > 0) {
                    $pi = \Stripe\PaymentIntent::create([
                        'customer'  => $stripeCustomer->id,
                        'amount' => intval(round($chargeAmount * 100)),
                        'currency' => $order->currency,
                        'automatic_payment_methods' => ['enabled' => true],
                        'receipt_email' => $customer->email,
                        'capture_method' => 'manual',
                        'description' => 'TourBeez Booking',
                        'statement_descriptor_suffix' => $order->order_number,
                        'metadata'  => $metaData,
                        'setup_future_usage'=> 'off_session',
                    ]);

                    // Save intent details
                    $order->payment_intent_client_secret = $pi->client_secret;
                    $order->payment_intent_id = $pi->id;
                    $order->booked_amount = $chargeAmount;
                    $order->balance_amount = max($order->total_amount - ($chargeAmount ?? 0), 0); //$order->total_amount - $chargeAmount;

                    // ✅ If frontend sent payment_method_id, confirm & capture immediately
                    if ($request->filled('payment_intent_id')) {
                        $pi = \Stripe\PaymentIntent::retrieve($pi->id);
                        $pi->confirm(['payment_method' => $request->payment_intent_id]);
                        $pi->capture();

                        $order->payment_status = 'paid';
                        $order->balance_amount = 0;
                    }
                } else {
                    $si = \Stripe\SetupIntent::create([
                        'customer'  => $stripeCustomer->id,
                        'automatic_payment_methods' => ['enabled' => true],
                        'usage'     => 'off_session',
                        'metadata'  => $metaData
                    ]);
                    $order->payment_intent_client_secret = $si->client_secret;
                    $order->payment_intent_id = $si->id;
                }

                // Booking fee
                $booking_fee = $request->booking_fee ?? 0;
                if($booking_fee > 0 && get_setting('price_booking_fee')){
                    $bookingFeeType = get_setting('tour_booking_fee_type');
                    if($bookingFeeType == 'FIXED'){
                        $booking_fee = get_setting('tour_booking_fee');
                    } elseif($bookingFeeType == 'PERCENT') {
                        $booking_fee = $order->total_amount * get_setting('tour_booking_fee')/100;
                    }
                }

                $order->booking_fee = $booking_fee;
                $order->stripe_customer_id = $stripeCustomer->id;
                $order->save();

            } catch (\Exception $e) {
                DB::rollBack();
                \Log::error('Stripe Payment Error: '.$e->getMessage());
                return response()->json([
                    'status' => false,
                    'message' => 'Payment setup failed: '.$e->getMessage()
                ], 500);
            }
        }


        }




        DB::commit();

        return redirect()->route('admin.orders.edit', [encrypt($order->id)]);

        if ($validator->fails()) {
            return redirect()
                ->route('admin.orders.edit', [encrypt($order->id)])
                ->withErrors($validator)
                ->withInput();
        }

        return response()->json([
            'status' => true,
            'message' => 'Order created successfully',
            'order_id' => $order->id
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Order Store Error: '.$e->getMessage());
        return response()->json([
            'status' => false,
            'message' => 'Something went wrong: '.$e->getMessage()
        ], 500);
    }
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
        // $email_templates = EmailTemplate::get();

        $email_templates = EmailTemplate::whereIn('identifier', [
            'order_detail',
            'order_cancelled',
            'order_confirmed',
            'trip_completed',
            'payment_receipt',
            'order_pending',
            'payment_request'
        ])->get();
        $sms_templates = SmsTemplate::get();
        return view('admin.order.edit', compact(['order', 'tours', 'email_templates', 'sms_templates']));
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

                    $tourPricing = TourPricing::find($pricingId);
                    $label = str_ireplace('Group', 'Participants', $tourPricing->label);

                    $pricingDetails[] = [
                        'tour_id'           => $tourId,
                        'label'             => $label,
                        'tour_pricing_id'   => $pricingId,
                        'quantity'          => $qty,
                        'price'             => $price,
                        'total_price'     => $qty * $price,
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
                    $extraAddon = Addon::find($extraId);

                    $extraDetails[] = [
                        'tour_id'       => $tourId,
                        'label'         => $extraAddon->name,
                        'tour_extra_id' => $extraId,
                        'quantity'      => $qty,
                        'price'         => $price,
                        'total_price'   => $qty * $price,
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
                            $price      = get_tax($total, $item->fee_type, $item->tax_fee_value);
                            $tax        = $price ?? 0;

                            

                            \Log::info($tax);
                            $subtotal   = $subtotal + $tax; 
                            
                            \Log::info($subtotal);
                        }
                        $total += $subtotal;
                    }
                }
            }
        }
        $order->total_amount = $total;
        $order->balance_amount =max($order->total_amount - ($order->booked_amount ?? 0), 0);// $total - $order->total_amount;
        
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

    public function bulkDelete(Request $request)
    {
        $ids = $request->ids;

        if (!$ids || count($ids) === 0) {
            return redirect()->back()->with('error', 'No orders selected.');
        }

        Order::whereIn('id', $ids)->delete();

        return redirect()->back()->with('success', 'Selected orders deleted successfully.');
    }

    public function order_mail_send(Request $request)
{

    $cc_mail   = $request->input('cc_mail');
    $bcc_mail   = $request->input('bcc_mail');
    $email   = $request->input('email');
    $subject = $request->input('subject');
    $header  = $request->input('header');
    $body    = $request->input('body');
    $footer  = $request->input('footer');
    $event   = $request->input('event');



    if (env('MAIL_FROM_ADDRESS')) {
        $array = [
            'view'    => 'emails.newsletter',
            'subject' => $subject,
            'header'  => $header,
            'from'    => env('MAIL_FROM_ADDRESS'),
            'content' => $header . $body . $footer,
            'event'   => json_decode($event, true),
        ];

        try {

            // Explicitly use Mailgun mailer
            $mailer = Mail::mailer('mailgun');
            // $mailer = Mail::mailer();

            // Send email and capture message inf

            $sentMessage = $mailer->to($email);
            
            if( $cc_mail ) {
                
                $sentMessage->cc(explode(',', $cc_mail));
            }
            if( $bcc_mail ) {
                $sentMessage->bcc(explode(',', $bcc_mail));
            }
            
            $sentMessage = $sentMessage->send(new EmailManager($array));
           
            $messageId = null;
            if ($sentMessage instanceof \Illuminate\Mail\SentMessage) {
                $symfonySent = $sentMessage->getSymfonySentMessage();
                if ($symfonySent && method_exists($symfonySent, 'getMessageId')) {
                    $messageId = $symfonySent->getMessageId();
                    $messageId = trim($messageId, '<>');
                }
            }



            // Get order_id from request
            $order_id = $request->input('order_id') ?? optional($request->order)->id;

            // Save to email history table
            OrderEmailHistory::create([
                'order_id'   => $order_id,
                'to_email'   => $email,
                'from_email' => env('MAIL_FROM_ADDRESS'),
                'subject'    => $subject,
                'body'       => $header . $body . $footer,
                'status'     => 'sent',
                'message_id' => $messageId, // ✅ store for webhook tracking
            ]);

            return response()->json(['status' => 'success', 'message_id' => $messageId]);
        } catch (\Exception $e) {
            \Log::error('Mail send failed: ' . $e->getMessage());
            return response()->json(['status' => 'failed', 'error' => $e->getMessage()]);
        }
    }
}


    public function order_template_details(Request $request)
    {

        try{
            $order_id = $request->order_id;
            $order_template_id = $request->order_template_id;
            $order = Order::findorFail($order_id);
            
            $email_template = EmailTemplate::findorFail($order_template_id);

            $template = $email_template->body;

            $template_footer = $email_template->footer;

            $template_subject = $email_template->subject;

            $system_logo = get_setting('system_logo');
            $logo = uploaded_asset($system_logo);

            $customer   = $order->customer;
            if(!$customer){
                $customer = $order->user;
            }

            if(!$customer){
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found.'
                ], 404);
            }

            $orderTour  = $order->orderTours()->first();
            $tour       = $orderTour->tour;
            //echo '<pre>'; print_r($orderTour->tour); exit;


            $pickup_address = '';
            if( $order->customer->pickup_name ) {
                $pickup_address = $order->customer->pickup_name;
            }
            else if($order->customer->pickup_id) {
                $pickup_address = $order->customer?->pickup?->location . ' ( '.$order->customer?->pickup?->address.' )';
            }
            if($pickup_address) {
                $pickup_address = '
                  <small style="font-size:10px; font-weight:400; text-transform: uppercase; color:#fff;">Pick up</small>
                  <h3 style="color: #fff; margin-top: 5px; font-size: 15px; margin-bottom: 5px;">
                    <strong>' . $pickup_address . '</strong>
                  </h3>';
                            }

            $TOUR_PAYMENT_HISTORY = '
                    <table width="640" bgcolor="#ffffff" cellpadding="0" cellspacing="0" border="0" align="center" class="header_table" style="width:640px;">
                        <tbody>
                            <tr>
                                <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; text-align: left; padding: 30px 30px 15px; width:640px;">
                                    <h3 style="font-size:19px"><strong>Payment History</strong></h3>
                                </td>
                            </tr>
                        </tbody>
                    </table>
            
                    <table width="640" bgcolor="#ffffff" cellpadding="0" cellspacing="0" border="0" align="center" class="table" style="border-width:0 30px 30px; border-color: #fff; border-style: solid; background-color:#fff">
                        <tbody>
                            <tr>
                                <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; width: 50%; border-bottom:2pt solid #000; text-align: left;padding: 5px 0px;">
                                    <small style="font-size:10px; font-weight:400; text-transform: uppercase; color:#000">Payment Type</small>
                                </td>

                                <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; width: 30%; border-bottom:2pt solid #000; text-align: left;padding: 5px 0px;">
                                    <small style="font-size:10px; font-weight:400; text-transform: uppercase; color:#000">Date</small>
                                </td>
                                <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; width: 20%; border-bottom:2pt solid #000; text-align: right;padding: 5px 0px;">
                                    <small style="font-size:10px; font-weight:400; text-transform: uppercase; color:#000">Amount</small>
                                </td>
                            </tr>

                            <tr>
                                <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; border-top:1pt solid #000; text-align: left;padding: 5px 0px;" valign="top">Credit card</td>
                                <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; border-top:1pt solid #000; text-align: left;padding: 5px 0px;" valign="top">' . date('M d, Y', strtotime($order->created_at)) . '</td>
                                <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; border-top:1pt solid #000; text-align: right;padding: 5px 0px;" valign="top"><strong>' . price_format_with_currency($order->total_amount, $order->currency) . '</strong></td>
                            </tr>

                            <tr>
                                <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; border-top:2pt solid #000; border-bottom:2pt solid #000;">
                                &nbsp;
                                </td>
                                <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; border-top:2pt solid #000;  border-bottom:2pt solid #000; text-align: left;padding: 5px 0px;">
                                    <small style="font-size:10px; font-weight:400; text-transform: uppercase; color:#000;">Total</small>
                                </td>
                                <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; border-top:2pt solid #000; border-bottom:2pt solid #000; text-align: right;padding: 5px 0px;">
                                    <h3 style="color: #000;font-size:19px"><strong>' . price_format_with_currency($order->total_amount, $order->currency) . '</strong></h3>
                                </td>
                            </tr>
                        </tbody>
                    </table>';


            $TOUR_ITEM_SUMMARY = '';
 
            foreach ($order->orderTours as $order_tour) {
                $subtotal = 0;
                $_tourId = $order_tour->tour_id;
                $tour_pricing = !empty($order_tour->tour_pricing) ? json_decode($order_tour->tour_pricing, true) : [];
                $tour_extra = !empty($order_tour->tour_extra) ? json_decode($order_tour->tour_extra, true) : [];
                $TOUR_ITEM_SUMMARY .= '
                    <table width="768" bgcolor="#ffffff" cellpadding="0" cellspacing="0" border="0" align="center" class="header_table" style="width:768px;">
                    <tbody>
                    <tr>
                    <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; text-align: left; padding: 0 0 10px; width:768px;">
                    <h3 style="font-size:19px"><strong>' . $order_tour->tour->title . ' - Item Summary</strong></h3>
                    </td>
                    </tr>
                    </tbody>
                    </table>
                     
                    <table width="768" bgcolor="#ffffff" cellpadding="0" cellspacing="0" border="0" align="center" class="table" style="border-width:0 30px 30px; border-color: #fff; border-style: solid; background-color:#fff">
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
                        //$total = $qty * $price;
                        $total = $result['total_price'] ?? 0;
                        if ($qty > 0) {
                            $subtotal += $total;
                            $TOUR_ITEM_SUMMARY .= '
                    <tr>
                    <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; border-top:1pt solid #ddd; text-align: left;padding: 5px 0px;">' . $qty . '</td>
                    <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; border-top:1pt solid #ddd; text-align: left;padding: 5px 0px;">' . ucwords($result['label']??"") . '</td>
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
                                        // $total = $qty * $price;
                                        $total = $extra['total_price'] ?? 0;
                                        if ($qty > 0) {
                                            $subtotal += $total;
                                            $TOUR_ITEM_SUMMARY .= '
                    <tr>
                    <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; border-top:1pt solid #ddd; text-align: left;padding: 5px 0px;">' . $qty . '</td>
                    <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; border-top:1pt solid #ddd; text-align: left;padding: 5px 0px;">' . $extra['label']??"" . ' (Extra)</td>
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
            
            $pickup_address = '';
            if( $order->customer->pickup_name ) {
                $pickup_address = $order->customer->pickup_name;
            }
            else if($order->customer->pickup_id) {
                $pickup_address = $order->customer?->pickup?->location . ' ( '.$order->customer?->pickup?->address.' )';
            }
            // if($pickup_address) {
            //     $pickup_address = '
            //       <small style="font-size:10px; font-weight:400; text-transform: uppercase; color:#fff;">Pick up</small>
            //       <h3 style="color: #fff; margin-top: 5px; font-size: 15px; margin-bottom: 5px;">
            //         <strong>' . $pickup_address . '</strong>
            //       </h3>';
            //                 }

            $to_address = $tour->location->destination ?? '';
            $to_address.= $tour->location->address ? ' ('.$tour->location->address.')' : '';
            $order_paid = $order->total_amount - $order->balance_amount;

            $token = encrypt($order->id);

            $timestamp = round(microtime(true) * 1000);
            $checkoutUrl = "https://tourbeez.com/checkout/{$timestamp}?token={$token}";

            $replacements = [
                "[[CUSTOMER_NAME]]"         => $customer->name ?? '',
                "[[CUSTOMER_EMAIL]]"        => $customer->email ?? '',
                "[[CUSTOMER_PHONE]]"        => $customer->phone ?? '',



                "[[TOUR_TITLE]]"            => $tour->title ?? '',
                "[[TOUR_SKU]]"              => $tour->unique_code ?? '',
                // "[[TOUR_MAP]]"              => $tour->location->address ?? '',
                "[[TOUR_MAP]]"              => $pickup_address,
                "[[TOUR_ADDRESS]]"          => $tour->location->address ?? '',
                "[[TOUR_PAYMENT_HISTORY]]"  => $TOUR_PAYMENT_HISTORY,
                "[[TOUR_ITEM_SUMMARY]]"     => $TOUR_ITEM_SUMMARY,
                "[[TOUR_TERMS_CONDITIONS]]"  => $tour->terms_and_conditions,
                "[[PICKUP_ADDRESS]]"        => $pickup_address,

                "[[APP_LOGO]]"              => $logo,
                "[[APP_NAME]]"              => get_setting('site_name'),
                "[[COMPANY_NAME]]"          => get_setting('site_name'),
                "[[APP_URL]]"               => get_setting('app_url'),
                "[[APP_EMAIL]]"             => get_setting('app_email'),
                "[[APP_PHONE]]"             => get_setting('app_phone'),
                "[[APP_ADDRESS]]"           => get_setting('app_address'),
                "[[YEAR]]"                  => date('Y'),

                "[[ORDER_NUMBER]]"          => $order->order_number ?? '',
                "[[ORDER_STATUS]]"          => $order->status,
                "[[ORDER_TOUR_DATE]]"       => date('l, F j, Y', strtotime($orderTour->tour_date)),
                "[[ORDER_TOUR_TIME]]"       => $orderTour->tour_time,
                "[[ORDER_TOTAL]]"           => price_format_with_currency($order->total_amount, $order->currency) ?? '',
                "[[ORDER_BALANCE]]"         => price_format_with_currency($order->balance_amount, $order->currency) ?? '',
                "[[ORDER_BOOKING_FEE]]"     => price_format_with_currency($order->booking_fee, $order->currency) ?? '',
                "[[ORDER_CREATED_DATE]]"    => date('M d, Y', strtotime($order->created_at)) ?? '',
                "[[YEAR]]"                 => date('Y'),
                "[[ORDER_LINK]]"           => $checkoutUrl,
            ];
 
            $finalMessage = strtr($template, $replacements);
            $finalfooter = strtr($template_footer, $replacements);
            $finalsubject = strtr($template_subject, $replacements);
            
            if ($order) {
                $email_template->subject = $finalsubject;

                return response()->json([
                    'success' => true,
                    'email' => $customer->email,
                    'bcc_mail' => $email_template->identifier == "trip_completed" ? 'tourbeez.com+9768a17f10@invite.trustpilot.com' : '',
                    'email' => $customer->email,
                    'email_template' => $email_template,
                    'body'=>$finalMessage,
                    'footer'=>$finalfooter,
                    'event' => $email_template->identifier == "trip_completed" ? []: [
                        'uid' => "TB" . $order->order_number,
                        'start' => $orderTour->tour_date . ' ' . $orderTour->tour_time, // "2025-10-02 6:00 PM"
                        'end' => $orderTour->tour_date . ' ' . date(
                            'g:i A',
                            strtotime('+2 hours', strtotime($orderTour->tour_time))
                        ),
                        'title' => $tour->title,
                        'description' => $finalsubject,
                        'location' => $tour->location->address,
                    ],
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found.'
                ], 404);
            }
        }
        catch(\Exception $e){
            return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 404);
        }
 
    }
    
    public function order_sms_send(Request $request, TwilioService $twilio)
    {
        $mobile_number = $request->mobile_number;
        $message       = strip_tags($request->message);
 
        try {
            $lookup = $twilio->lookupNumber($mobile_number);
            
            if($lookup->phoneNumber)
            $twilio->sendSms($mobile_number, $message);

            return back()->with('success', translate("SMS has been sent."));
        } catch (\Exception $e) {
            return $e->getMessage();
            return back()->with('error', $e->getMessage());
        }
    }

    public function order_confirmation_message(Request $request) {
        try {
            $order_id = $request->order_id;
            $order_confirmation_id = $request->order_confirmation_id;
            $order = Order::findorFail($order_id);
            $confirmation_template = SmsTemplate::findorFail($order_confirmation_id);

            $customer = $order->user;

            $customer   = $order->user;
            if(!$customer){
                $customer = $order->orderUser;
            }
            // dd($customer, $order->user);
            $template = $confirmation_template->message;
            $orderTour  = $order->orderTours()->first();
            $tour       = $orderTour->tour;

            $system_logo = get_setting('system_logo');
            $logo = uploaded_asset($system_logo);


            $replacements = [
                "[[CUSTOMER_NAME]]"         => $customer->name ?? '',
                "[[CUSTOMER_EMAIL]]"        => $customer->email ?? '',
                "[[CUSTOMER_PHONE]]"        => $customer->name ?? '',
                "[[CUSTOMER_FIRST_NAME]]"   => $customer->first_name ?? '',
                "[[CUSTOMER_LAST_NAME]]"   => $customer->last_name ?? '',

                "[[TOUR_TITLE]]"            => $tour->title ?? '',
                "[[TOUR_SKU]]"              => $tour->unique_code ?? '',
                "[[TOUR_MAP]]"              => $tour->location->address ?? '',
                "[[TOUR_ADDRESS]]"          => $tour->location->address ?? '',
                // "[[TOUR_PAYMENT_HISTORY]]"  => $TOUR_PAYMENT_HISTORY,
                // "[[TOUR_ITEM_SUMMARY]]"     => $TOUR_ITEM_SUMMARY,
                "[[TOUR_TERMS_CONDITIONS]]"  => $tour->terms_and_conditions,

                "[[APP_LOGO]]"              => $logo,
                "[[APP_NAME]]"              => get_setting('site_name'),
                "[[COMPANY_NAME]]"          => get_setting('site_name'),
                "[[APP_URL]]"               => get_setting('app_url'),
                "[[APP_EMAIL]]"             => get_setting('app_email'),
                "[[APP_PHONE]]"             => get_setting('app_phone'),
                "[[APP_ADDRESS]]"           => get_setting('app_address'),
                "[[YEAR]]"                  => date('Y'),

                "[[ORDER_NUMBER]]"          => $order->order_number ?? '',
                "[[ORDER_STATUS]]"          => $order->status,
                "[[ORDER_STATUS_HELP]]"     => $order->help ?? '',
                "[[ORDER_TOUR_DATE]]"       => date('l, F j, Y', strtotime($order->created_at)),
                "[[ORDER_TOUR_TIME]]"       => date('H:i A', strtotime($order->created_at)),
                "[[ORDER_TOTAL]]"           => price_format_with_currency($order->total_amount, $order->currency) ?? '',
                "[[ORDER_BALANCE]]"         => price_format_with_currency($order->balance_amount, $order->currency) ?? '',
                "[[ORDER_BOOKING_FEE]]"     => price_format_with_currency($order->booking_fee, $order->currency) ?? '',
                "[[ORDER_CREATED_DATE]]"    => date('M d, Y', strtotime($order->created_at)) ?? '',
            ];
            // $replacements = [
            //     "[[CUSTOMER_NAME]]"         => $customer->name ?? '',
            //     "[[CUSTOMER_FIRST_NAME]]"   => $customer->first_name ?? '',
            //     "[[CUSTOMER_LAST_NAME]]"   => $customer->first_name ?? '',
            //     "[[COMPANY_NAME]]"          => config('app.name'),
            //     "[[ORDER_NUMBER]]"          => $order->order_number ?? '',
            //     "[[ORDER_STATUS]]"          => ucfirst($order->status) ?? '',

            //     "[[TOUR_TITLE]]"            => $order->user->name ?? '',
            //     "[[TOUR_SKU]]"              => $tour->unique_code ?? '',
            //     "[[TOUR_DATE]]"             => $order->user->name ?? '',
            //     "[[TOUR_TIME]]"             => $order->user->name ?? '',
            //     "[[TOUR_MAP]]"              => $order->user->name ?? '',
            //     "[[TOUR_ADDRESS]]"          => $order->user->name ?? '',
            //     "[[TOUR_PAYMENT_HISTORY]]"  => $order->user->name ?? '',
            //     "[[TOUR_ITEM_SUMMARY]]"     => $order->user->name ?? '',

            //     "[[CUSTOMER_NAME]]"         => $order->user->name ?? '',
            //     "[[CUSTOMER_EMAIL]]"        => $order->user->name ?? '',
            //     "[[CUSTOMER_PHONE]]"        => $order->user->name ?? '',

            //     "[[APP_LOGO]]"              => $order->user->name ?? '',
            //     "[[APP_NAME]]"              => $order->user->name ?? '',
            //     "[[APP_URL]]"               => $order->user->name ?? '',
            //     "[[APP_EMAIL]]"             => $order->user->name ?? '',
            //     "[[APP_PHONE]]"             => $order->user->name ?? '',
            //     "[[APP_ADDRESS]]"           => $order->user->name ?? '',

            //     "[[ORDER_NUMBER]]"          => $order->user->name ?? '',
            //     "[[ORDER_STATUS_HELP]]"     => $order->user->name ?? '',
            //     "[[ORDER_STATUS]]"     => $order->user->name ?? '',
            //     "[[ORDER_TOTAL]]"           => $order->user->name ?? '',
            //     "[[ORDER_BALANCE]]"         => $order->user->name ?? '',
            //     "[[ORDER_BOOKING_FEE]]"     => $order->user->name ?? '',
            //     "[[ORDER_CREATED_DATE]]"    => $order->user->name ?? '',
            // ];


            $finalMessage = strtr($template, $replacements);
 
            if ($order) {
                return response()->json([
                    'success' => true,
                    'mobile' => $customer->phone,
                    'confirmation_template' => $confirmation_template,
                    'message'=>$finalMessage,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found.'
                ], 404);
            }
        }
        catch(\Exception $e){
            return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 404);
        }
 
    }


    public function updateStatus(Request $request, $id)
    {
        // dd($id);
        $order = Order::findOrFail($id);
        // dd($order);
        // $request->validate([
        //     'status' => 'required|string|max:50',
        // ]);
        // dd($request->status);
        $order->order_status = $request->status;
        
        if($request->status == 'Confirmed' ){

            // dd($order->order_status, $request->status, $order->payment_status);
            if($order->payment_status == 3){

                $confirmPayment = self::confirmPayment($order->id, $order->adv_deposite, $order->booked_amount);

                $confirmPayment = $confirmPayment->getData();
                
                if($confirmPayment->status === 'succeeded'){
                    
                    $order->payment_status == 1;
                    $order->save();
                    return response()->json(['success' => true, 'message' => $confirmPayment->message]);

                } else{
                    return response()->json(['success' => false, 'message' => $confirmPayment->message]);
                }
            } elseif($order->payment_status == 5) {
                
                return response()->json(['success' => true, 'message' => 'Please enter payment details before confirming the order']);
            }
            elseif($order->payment_status == 7) {
                
                return response()->json(['success' => true, 'message' => 'Payment is already cancelled']);
            }else {

                $order->save();
                return response()->json(['success' => true, 'message' => 'Order is already charged']);
            }

            return response()->json(['success' => false, 'message' => 'Order is not confirmed Yet']);
            
            
        }

        if ($request->status == 'Cancelled') {

            // Only cancel if payment not captured yet
            if ($order->payment_status == 3) { // 3 = authorized only

                // ❌ If already captured, do not cancel
                if ($order->payment_intent_id) {
                    \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
                    $intent = \Stripe\PaymentIntent::retrieve($order->payment_intent_id);

                    if ($intent->status === 'succeeded' || $intent->status === 'partially_captured') {
                        return response()->json([
                            'success' => false,
                            'message' => 'Order already captured, you can proceed with refund.'
                        ]);
                    }
                }

                // Call Stripe cancellation method you created earlier
                $cancel = self::cancelUncapturedAmount($order->id);

                $response = $cancel->getData();

                if ($response->success) {

                    // Update to payment_status = 0 (payment cancelled)
                    $order->payment_status = 7;
                    $order->booked_amount = 0;
                    $order->save();

                    return response()->json([
                        'success' => true,
                        'message' => 'Payment authorization cancelled successfully.'
                    ]);

                } else {
                    return response()->json([
                        'success' => false,
                        'message' => $response->message
                    ]);
                }
            }
            $order->save();
            // If not in payment_status=3:
            return response()->json([
                'success' => false,
                'message' => 'Nothing to cancel. Payment already processed or no authorization present.'
            ]);
        }
        

        $order->save();

        // $this->sendOrderStatusEmail($order);

        return response()->json(['success' => true]);
    }




    public function manifest(Request $request)
    {
        $date = $request->input('date') ?? Carbon::today()->toDateString();

        // Preload pricing labels indexed by ID
        $pricingLabels = TourPricing::pluck('label', 'id')->toArray();

        // Create 48 half-hour slots
        $timeSlots = collect();
        $start = Carbon::createFromTime(0, 0);
        for ($i = 0; $i < 48; $i++) {
            $slotStart = $start->copy()->addMinutes($i * 30);
            $slotEnd = $slotStart->copy()->addMinutes(30);
            $timeSlots->push([
                'label' => $slotStart->format('g:i A') . ' - ' . $slotEnd->format('g:i A'),
                'start' => $slotStart->format('H:i:s'),
                'end' => $slotEnd->format('H:i:s'),
            ]);
        }

        $sessions = collect();

        foreach ($timeSlots as $slot) {
            $orders = Order::with(['customer', 'orderTours'])
                ->whereDate('created_at', $date)
                ->whereTime('created_at', '>=', $slot['start'])
                ->whereTime('created_at', '<', $slot['end'])
                ->get()
                ->map(function ($order) use ($pricingLabels) {
                    $guests = collect();
                    $extras = collect();

                    foreach ($order->orderTours as $ot) {
                        // Parse guest pricing
                        $pricingItems = json_decode($ot->tour_pricing, true);
                        if (is_array($pricingItems)) {
                            foreach ($pricingItems as $p) {
                                $qty = $p['quantity'] ?? 0;
                                $pricingId = $p['tour_pricing_id'] ?? null;
                                $label = $pricingLabels[$pricingId] ?? ($p['label'] ?? null);

                                if ($qty && $label) {
                                    $guests->push("{$qty} {$label}");
                                }
                            }
                        }

                        // Parse extras
                        $extraItems = json_decode($ot->tour_extra, true);
                        if (is_array($extraItems)) {
                            foreach ($extraItems as $e) {
                                $qty = $e['quantity'] ?? 0;
                                $label = $e['label'] ?? null;
                                if ($qty && $label) {
                                    $extras->push("{$qty} {$label}");
                                }
                            }
                        }
                    }

                    $order->guest_summary = $guests->isNotEmpty() ? $guests->implode(', ') : '-';
                    $order->extras_summary = $extras->isNotEmpty() ? $extras->implode(', ') : '-';
                    $order->paid_amount = $order->total_amount - ($order->balance_amount ?? 0);

                    return $order;
                });

            if ($orders->isNotEmpty()) {
                $sessions->push([
                    'slot_time' => $slot['label'],
                    'orders' => $orders,
                ]);
            }
        }

        return view('admin.order.manifest', compact('sessions', 'date'));
    }


    public function downloadManifest(Request $request)
    {
        $date = $request->input('date') ?? Carbon::today()->toDateString();

        $pricingLabels = TourPricing::pluck('label', 'id')->toArray();
        $timeSlots = collect();
        $start = Carbon::createFromTime(0, 0);
        for ($i = 0; $i < 48; $i++) {
            $slotStart = $start->copy()->addMinutes($i * 30);
            $slotEnd = $slotStart->copy()->addMinutes(30);
            $timeSlots->push([
                'label' => $slotStart->format('g:i A') . ' - ' . $slotEnd->format('g:i A'),
                'start' => $slotStart->format('H:i:s'),
                'end' => $slotEnd->format('H:i:s'),
            ]);
        }

        $sessions = collect();

        foreach ($timeSlots as $slot) {
            $orders = Order::with(['customer', 'orderTours'])
                ->whereDate('created_at', $date)
                ->whereTime('created_at', '>=', $slot['start'])
                ->whereTime('created_at', '<', $slot['end'])
                ->get()
                ->map(function ($order) use ($pricingLabels) {
                    $guests = collect();
                    $extras = collect();
                    $guestCount = 0;

                    foreach ($order->orderTours as $ot) {
                        $pricingItems = json_decode($ot->tour_pricing, true);
                        if (is_array($pricingItems)) {
                            foreach ($pricingItems as $p) {
                                $qty = $p['quantity'] ?? 0;
                                $pricingId = $p['tour_pricing_id'] ?? null;
                                $label = $pricingLabels[$pricingId] ?? ($p['label'] ?? null);

                                if ($qty && $label) {
                                    $guests->push("{$qty} {$label}");
                                    $guestCount += $qty;
                                }
                            }
                        }

                        $extraItems = json_decode($ot->tour_extra, true);
                        if (is_array($extraItems)) {
                            foreach ($extraItems as $e) {
                                $qty = $e['quantity'] ?? 0;
                                $label = $e['label'] ?? null;
                                if ($qty && $label) {
                                    $extras->push("{$qty} {$label}");
                                }
                            }
                        }
                    }

                    $order->guest_summary = $guests->isNotEmpty() ? $guests->implode(', ') : '-';
                    $order->extras_summary = $extras->isNotEmpty() ? $extras->implode(', ') : '-';
                    $order->paid_amount = $order->total_amount - ($order->balance_amount ?? 0);
                    $order->guest_count = $guestCount;

                    return $order;
                });

            if ($orders->isNotEmpty()) {
                $sessions->push([
                    'slot_time' => $slot['label'],
                    'orders' => $orders,
                ]);
            }
        }

        return Excel::download(new ManifestExport($sessions, $date), "Manifest_{$date}.xlsx");
    }


    protected function sendOrderStatusEmail($order)
    {
        $statusToTemplate = [
            'New'               => 'order_detail',
            'On Hold'           => 'order_pending',
            'Pending supplier'  => 'order_pending',
            'Pending customer'  => 'order_pending',
            'Confirmed'         => 'order_confirmed',
            'Cancelled'         => 'order_cancelled',
            'Abandoned cart'    => 'payment_required',
        ];

        $status = $order->status;

        if (!isset($statusToTemplate[$status])) {
            \Log::info("No email template mapped for status: " . $status);
            return;
        }

        $templateIdentifier = $statusToTemplate[$status];

        // dd($templateIdentifier);
        $emailTemplate = EmailTemplate::where('identifier', $templateIdentifier)->first();

        if (!$emailTemplate) {
            \Log::warning("Email template not found for identifier: " . $templateIdentifier);
            return;
        }

        // Now reuse your function
        $request = new Request([
            'order_id' => $order->id,
            'order_template_id' => $emailTemplate->id
        ]);

        // This will return the JSON with compiled template
        $response = $this->order_template_details($request);

        // Convert response to array
        $data = $response->getData(true);

       if (!isset($data['success']) || !$data['success']) {
            \Log::error("Failed to build email template for order " . $order->id);
            return false;
        }

        $request = new Request([
            'order' => $order,
            'email' => $data['email'],
            'subject' => $data['email_template']['subject'],
            'header' => $data['email_template']['header'] ?? '',
            'body' => $data['email_template']['body'] ?? '',
            'footer' => $data['footer'],
            'event' => $data['event'] ? json_encode($data['event']) : null
        ]);


        // Call your static mail function
        return self::order_mail_send($request
        );
    }






public function capturePayment(Request $request, $orderId)
{
    DB::beginTransaction();

    try {
        $order = Order::findOrFail($orderId);
        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

        $customerId = $order->stripe_customer_id;
        $intentId   = $order->payment_intent_id;

        if (!$customerId || !$intentId) {
            throw new \Exception("Stripe customer or PaymentIntent not found.");
        }

        // Retrieve previous PaymentIntent
        $paymentIntent = \Stripe\PaymentIntent::retrieve($intentId);
        $paymentMethodId = $paymentIntent->payment_method;

        if (!$paymentMethodId) {
            throw new \Exception("No payment method found on previous PaymentIntent.");
        }

        // Retrieve payment method
        $paymentMethod = \Stripe\PaymentMethod::retrieve($paymentMethodId);

        // Attach to customer if not already attached
        if ($paymentMethod->customer !== $customerId) {
            $paymentMethod->attach(['customer' => $customerId]);
        }

        // Charge amount
        $chargeAmount = (float) $request->input('amount');
        if ($chargeAmount <= 0) {
            throw new \Exception("Invalid charge amount.");
        }

        // Create a new PaymentIntent for off-session charge
        $newIntent = \Stripe\PaymentIntent::create([
            'customer'             => $customerId,
            'amount'               => intval($chargeAmount * 100),
            'currency'             => $order->currency ?? 'eur',
            'payment_method'       => $paymentMethodId,
            // 'payment_method_types' => ['card', 'link'], // card and link allowed
            'automatic_payment_methods' => ['enabled' => true],
            'off_session'          => true,
            'confirm'              => true,
            'description'          => "Manual charge for Order #{$order->order_number}",
            'metadata'             => [
                'order_id' => $order->id,
                'action'   => 'manual_charge'
            ]
        ]);

        // Save card info
        $card = $paymentMethod->card;
        $last4 = $card->last4 ?? null;
        $brand = $card->brand ?? null;


        $order->booked_amount += $chargeAmount;
        $order->balance_amount = max($order->total_amount - $order->booked_amount, 0);
        $order->save();

        // Save payment record
        OrderPayment::create([
            'order_id'          => $order->id,
            'payment_intent_id' => $newIntent->id,
            'transaction_id'    => $newIntent->charges->data[0]->id ?? $newIntent->id,
            'payment_method'    => 'card',
            'card_brand'        => $brand,
            'card_last4'        => $last4,
            'amount'            => $chargeAmount,
            'currency'          => $order->currency,
            'status'            => 'succeeded',
            'action'            => 'manual_charge',
            'response_payload'  => json_encode($newIntent),
        ]);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => "Customer charged successfully.",
            'data' => [
                'intent' => $newIntent,
                'card' => [
                    'brand' => $brand,
                    'last4' => $last4
                ]
            ]
        ]);

    } catch (\Stripe\Exception\CardException $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => $e->getError()->message,
        ], 400);

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error("Manual charge failed", [
            'message' => $e->getMessage(),
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
        ]);

        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
        ], 500);
    }
}










public function refundPayment(Request $request, Order $order)
{
    $request->validate([
        'payment_id' => 'required|integer',
        'amount' => 'required|numeric|min:0.5',
        'reason' => 'nullable|string|max:255'
    ]);

    $payment = $order->payments()->findOrFail($request->payment_id);

    // Ensure we don’t over-refund
    $alreadyRefunded = $payment->refund_amount ?? 0;
    $remainingRefundable = $payment->amount - $alreadyRefunded;

    if ($remainingRefundable <= 0) {
        return response()->json(['success' => false, 'message' => 'This payment has already been fully refunded.']);
    }

    if ($request->amount > $remainingRefundable) {
        return response()->json(['success' => false, 'message' => 'Refund amount exceeds remaining refundable balance.']);
    }

    try {
        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

        $refund = \Stripe\Refund::create([
            'payment_intent' => $payment->payment_intent_id,
            'amount' => (int) ($request->amount * 100), // cents
            'reason' => $request->reason ?: null,
        ]);

        // Calculate new total refunded amount
        $newRefundTotal = $alreadyRefunded + $request->amount;

        // Determine new status
        $newStatus = $newRefundTotal >= $payment->amount ? 'refunded' : 'partial_refunded';

        // Update payment
        $payment->update([
            'status' => $newStatus,
            'refund_id' => $refund->id ?? null,
            'refunded_at' => now(),
            'refund_amount' => $newRefundTotal, // cumulative refund
            'refund_reason' => $request->reason,
        ]);

        // Update order amounts
        $order->booked_amount -= $request->amount;
        $order->balance_amount = max($order->total_amount - $order->booked_amount, 0);
        $order->save();

        return response()->json([
            'success' => true,
            'status' => $newStatus,
            'refunded_amount' => $newRefundTotal,
            'remaining' => $payment->amount - $newRefundTotal,
        ]);

    } catch (\Stripe\Exception\ApiErrorException $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()]);
    }
}





    public function tourManifest(Request $request)
    {
        $date = $request->input('date') ?? Carbon::today()->toDateString();

        // Preload pricing labels indexed by ID
        $pricingLabels = TourPricing::pluck('label', 'id')->toArray();

        // Create 48 half-hour slots
        $timeSlots = collect();
        $start = Carbon::createFromTime(0, 0);
        for ($i = 0; $i < 48; $i++) {
            $slotStart = $start->copy()->addMinutes($i * 30);
            $slotEnd = $slotStart->copy()->addMinutes(30);
            $timeSlots->push([
                'label' => $slotStart->format('g:i A') . ' - ' . $slotEnd->format('g:i A'),
                'start' => $slotStart->format('H:i:s'),
                'end' => $slotEnd->format('H:i:s'),
            ]);
        }

        $sessions = [];

        foreach ($timeSlots as $slot) {
            $orders = Order::with(['customer', 'orderTours.tour']) // ensure tour is loaded
                ->whereDate('created_at', $date)
                ->whereTime('created_at', '>=', $slot['start'])
                ->whereTime('created_at', '<', $slot['end'])
                ->get();

            foreach ($orders as $order) {
                foreach ($order->orderTours as $ot) {
                    $tourTitle = $ot->tour->title ?? 'Unknown Tour';
                    $key = "{$slot['label']} || {$tourTitle} ";

                    // Parse guest pricing
                    $guests = collect();
                    $extras = collect();
                    $pricingItems = json_decode($ot->tour_pricing, true);
                    if (is_array($pricingItems)) {
                        foreach ($pricingItems as $p) {
                            $qty = $p['quantity'] ?? 0;
                            $pricingId = $p['tour_pricing_id'] ?? null;
                            $label = $pricingLabels[$pricingId] ?? ($p['label'] ?? null);

                            if ($qty && $label) {
                                $guests->push("{$qty} {$label}");
                            }
                        }
                    }

                    // Parse extras
                    $extraItems = json_decode($ot->tour_extra, true);
                    if (is_array($extraItems)) {
                        foreach ($extraItems as $e) {
                            $qty = $e['quantity'] ?? 0;
                            $label = $e['label'] ?? null;
                            if ($qty && $label) {
                                $extras->push("{$qty} {$label}");
                            }
                        }
                    }

                    $order->guest_summary = $guests->isNotEmpty() ? $guests->implode(', ') : '-';
                    $order->extras_summary = $extras->isNotEmpty() ? $extras->implode(', ') : '-';
                    $order->paid_amount = $order->total_amount - ($order->balance_amount ?? 0);
                    // $sessions[$key]['orders'] = 'wq';
                    $sessions[$key]['slot_time'] = $key;
                    $sessions[$key]['orders'][] = $order;
                }
            }
        }

        // dd($sessions); // for debugging

        return view('admin.order.tour-manifest', compact('sessions', 'date'));
    }




   public function downloadTourManifest(Request $request)
    {
        $date = $request->input('date') ?? Carbon::today()->toDateString();

        // Preload pricing labels
        $pricingLabels = TourPricing::pluck('label', 'id')->toArray();

        // Load all orders for the date with their tours
        $orders = Order::with(['customer', 'orderTours.tour'])
            ->whereDate('created_at', $date)
            ->get();

        // Build as a plain array (NOT a Collection)
        $sessions = [];

        foreach ($orders as $order) {
            // ---- enrich order once (guest/extras/paid) ----
            $guests = collect();
            $extras = collect();
            $guestCount = 0;

            foreach ($order->orderTours as $ot) {
                // pricing
                $pricingItems = json_decode($ot->tour_pricing, true);
                if (is_array($pricingItems)) {
                    foreach ($pricingItems as $p) {
                        $qty = (int) ($p['quantity'] ?? 0);
                        $pricingId = $p['tour_pricing_id'] ?? null;
                        $label = $pricingLabels[$pricingId] ?? ($p['label'] ?? null);
                        if ($qty && $label) {
                            $guests->push("{$qty} {$label}");
                            $guestCount += $qty;
                        }
                    }
                }
                // extras
                $extraItems = json_decode($ot->tour_extra, true);
                if (is_array($extraItems)) {
                    foreach ($extraItems as $e) {
                        $qty = (int) ($e['quantity'] ?? 0);
                        $label = $e['label'] ?? null;
                        if ($qty && $label) {
                            $extras->push("{$qty} {$label}");
                        }
                    }
                }
            }

            $order->guest_summary = $guests->isNotEmpty() ? $guests->implode(', ') : '-';
            $order->extras_summary = $extras->isNotEmpty() ? $extras->implode(', ') : '-';
            $order->paid_amount    = $order->total_amount - ($order->balance_amount ?? 0);
            $order->guest_count    = $guestCount;

            // ---- group per tour + half-hour slot ----
            foreach ($order->orderTours as $ot) {
                $tourTitle = optional($ot->tour)->title ?? 'Unknown Tour';

                // slot from created_at (change to your scheduled time if you have one)
                $createdAt = $order->created_at instanceof \Carbon\Carbon
                    ? $order->created_at
                    : \Carbon\Carbon::parse($order->created_at);

                $slotStart = $createdAt->copy()->second(0);
                $slotStart->minute($slotStart->minute >= 30 ? 30 : 0);
                $slotEnd   = $slotStart->copy()->addMinutes(30);

                $slotLabel = $slotStart->format('g:i A') . ' - ' . $slotEnd->format('g:i A');
                $key       = "{$slotLabel} {$tourTitle}";

                if (!isset($sessions[$key])) {
                    $sessions[$key] = [
                        'slot_time' => $key,   // "Tour A 08:00 - 08:30"
                        'orders'    => [],     // we’ll index by order id to avoid duplicates
                    ];
                }

                // Avoid duplicate same order under same tour+slot

                $sessions[$key]['slot_time'] = $slotLabel;
                $sessions[$key]['title'] = $tourTitle;

                $sessions[$key]['orders'][$order->id] = $order;
            }
        }
        // dd($sessions);
        // Reindex inner orders numerically for the view/export
        foreach ($sessions as &$bucket) {
            $bucket['orders'] = array_values($bucket['orders']);
        }
        unset($bucket);

        // If your ManifestExport expects a collection, wrap with collect($sessions)
        return Excel::download(new ManifestExport($sessions, $date, 'tour'), "Manifest_{$date}.xlsx");
    }


    public function getPaymentDetails($orderId)
    {
        $order = Order::findOrFail($orderId);

        if (!$order->payment_intent_id) {
            return response()->json(['status' => false, 'message' => 'No Stripe Intent found'], 404);
        }

        Stripe::setApiKey(env('STRIPE_SECRET'));

        $paymentIntent = PaymentIntent::retrieve([
            'id' => $order->payment_intent_id,
            'expand' => ['payment_method'],
        ]);

        $card = $paymentIntent->payment_method->card ?? null;

        return response()->json([
            'status' => true,
            'data' => [
                'customer_name' => $customer = $order->user ?ucwords($order->user->name) : ucwords($order->customer->name),
                'amount'      => $order->balance_amount, // Stripe stores in cents
                'currency'    => strtoupper($paymentIntent->currency),
                'brand'       => $card?->brand,
                'last4'       => $card?->last4,
                'exp_month'   => $card?->exp_month,
                'exp_year'    => $card?->exp_year,
            ]
        ]);
    }


    public function addStripePayment(Request $request, Order $order)
    {
        // dd(2332, $request, $order, $order->payments());
        try {
            \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => (int) ($request->amount * 100),
                'currency' => strtoupper($order->currency ?? 'cad'),
                'customer' => $order->stripe_customer_id,
                'payment_method' => $request->payment_method_id,
                'off_session' => true,
                'confirm' => true,
            ]);

            // Store in order_payments table
            $order->payments()->create([
                'order_id'        => $order->id,
                'payment_intent_id' => $paymentIntent->id,
                'transaction_id'  => NULL,
                'payment_method'  => 'stripe',
                'amount'          => (int) ($request->amount),
                'currency'        => strtoupper($order->currency ?? 'cad'),
                'status'          => $paymentIntent->status,
                'action'          => 'deposit',
                'response_payload'=> json_encode($paymentIntent),
                'card_last4'      => $request->card_last4,
                'card_brand'      => $request->card_brand,
                'card_exp_month'  => $request->card_exp_month,
                'card_exp_year'   => $request->card_exp_year,
            ]);



            // Update main order summary
            $order->booked_amount += $request->amount;
            $order->balance_amount = max($order->total_amount - $order->booked_amount, 0);
            $order->save();

            return response()->json([
                'success' => true,
                'message' => 'Payment successful',
                'data' => $paymentIntent
            ]);
        } catch (\Exception $e) {
            \Log::error('Stripe Add Payment Failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    public function confirmPayment343543543($orderId, $action_name = 'full', $amount)
    {
        DB::beginTransaction();

        try {
            $order = Order::findOrFail($orderId);
            \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

            $capturedIntent = null;
            $action_name = $order->adv_deposite ?: $action_name;
            $customerId = $order->stripe_customer_id;
            $intentId = $order->payment_intent_id;
            $paymentMethodId = null;

            // Retrieve PaymentMethod from existing intents
            if ($intentId && Str::startsWith($intentId, 'seti_')) {
                $setupIntent = \Stripe\SetupIntent::retrieve($intentId);
                $paymentMethodId = $setupIntent->payment_method;
            } elseif ($intentId && Str::startsWith($intentId, 'pi_')) {
                $paymentIntent = \Stripe\PaymentIntent::retrieve($intentId);
                $paymentMethodId = $paymentIntent->payment_method;
            }

            if (!$paymentMethodId || !$customerId) {
                throw new \Exception("No valid payment method or customer found for this order.");
            }

            // Ensure PaymentMethod is attached to customer
            $paymentMethod = \Stripe\PaymentMethod::retrieve($paymentMethodId);
            if ($paymentMethod->customer !== $customerId) {
                $paymentMethod->attach(['customer' => $customerId]);
            }

            // Get card details
            $cardDetails = $paymentMethod->card ?? null;
            $cardLast4 = $cardDetails->last4 ?? null;
            $cardBrand = $cardDetails->brand ?? null;

            // Determine amount to charge
            $chargeAmount = $action_name === 'deposit'
                ? (float) $amount
                : (float) $order->balance_amount;

            if ($chargeAmount <= 0) {
                throw new \Exception("Invalid charge amount.");
            }

            // ✅ Create new PaymentIntent with only `card` as method type
            // "link" caused your previous error — we’ll use just 'card' for safe capture
            $paymentIntent = \Stripe\PaymentIntent::create([
                'customer'             => $customerId,
                'amount'               => (int) ($chargeAmount * 100),
                'currency'             => $order->currency ?? 'usd',
                'payment_method'      => $paymentMethodId,
                'payment_method_types'=> ['card', 'link'],
                'setup_future_usage' => 'off_session',
                
                'confirm'              => true,
                'description'          => 'Charge for Order #' . $order->order_number,
                'metadata'             => [
                    'order_id' => $order->id,
                    'action'   => $action_name,
                ],
            ]);

            $capturedIntent = $paymentIntent;

            // Update order status and amounts
            // $order->booked_amount += $chargeAmount;
            // $order->balance_amount = max(0, $order->total_amount - $order->booked_amount);

            if ($order->balance_amount <= 0) {
                $order->payment_status = 1; // Fully paid
            }

            $order->payment_intent_id = $paymentIntent->id;
            $order->transaction_id = $paymentIntent->charges->data[0]->id ?? $paymentIntent->id;
            $order->payment_status = 1;
            $order->save();

            // ✅ Save payment record
            OrderPayment::create([
                'order_id'          => $order->id,
                'payment_intent_id' => $paymentIntent->id,
                'transaction_id'    => $paymentIntent->charges->data[0]->id ?? $paymentIntent->id,
                'payment_method'    => 'card',
                'card_brand'        => $cardBrand,
                'card_last4'        => $cardLast4,
                'amount'            => $chargeAmount,
                'currency'          => $order->currency,
                'status'            => 'succeeded',
                'action'            => $action_name,
                'response_payload'  => json_encode($paymentIntent),
            ]);

            DB::commit();

            return response()->json([
                'success' => ($capturedIntent->status === 'succeeded'),
                'status' => $capturedIntent->status,
                'message' => $order->balance_amount > 0
                    ? 'Partial payment captured successfully.'
                    : 'Full payment captured successfully.',
                'data' => [
                    'payment_intent' => $capturedIntent,
                    'card' => [
                        'brand' => $cardBrand,
                        'last4' => $cardLast4,
                    ],
                ],
            ]);

        } catch (\Stripe\Exception\CardException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'status' => 'fail',
                'message' => $e->getError()->message ?? 'Card was declined.',
            ], 400);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Payment capture failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'status' => 'fail',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function confirmPayment($orderId, $action_name = 'full', $amount)
{
    DB::beginTransaction();

    try {
        $order = Order::findOrFail($orderId);

        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

        $intentId = $order->payment_intent_id;
        $customerId = $order->stripe_customer_id;
        $paymentMethodId = null;

        if (!$intentId) {
            throw new \Exception("No payment intent found for this order.");
        }

        // Retrieve existing payment intent
        $paymentIntent = \Stripe\PaymentIntent::retrieve($intentId);

        // ----------------------------------------------------
        // CASE 1: Already captured
        // ----------------------------------------------------
        if ($paymentIntent->status === 'succeeded') {
            return response()->json([
                'success' => false,
                'message' => "This payment has already been captured."
            ], 400);
        }

        // ----------------------------------------------------
        // CASE 2: Intent exists but not captured (requires_capture)
        // ----------------------------------------------------
        if ($paymentIntent->status === 'requires_capture') {

            // stripe expects integer cents
            $captureAmount = (int) ($amount * 100);

            // call capture on the retrieved instance (NOT statically)
            $captured = $paymentIntent->capture([
                'amount_to_capture' => $captureAmount
            ]);

            // Save record
            OrderPayment::create([
                'order_id' => $order->id,
                'payment_intent_id' => $captured->id,
                'transaction_id' => $captured->charges->data[0]->id ?? null,
                'amount' => $amount,
                'currency' => $captured->currency ?? $order->currency,
                'status' => 'succeeded',
                'action' => $action_name,
                'response_payload' => json_encode($captured)
            ]);

            // Update order values
            $order->booked_amount += $amount;
            $order->balance_amount = max(0, $order->total_amount - $order->booked_amount);
            $order->payment_status = $order->balance_amount <= 0 ? 1 : 0;
            $order->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'status' => 'succeeded',
                'message' => "Payment captured successfully.",
                'data' => $captured
            ]);
        }

        // ----------------------------------------------------
        // CASE 3: Intent is setup-intent (seti_xxx) or invalid → create new PI
        // ----------------------------------------------------
        if (Str::startsWith($intentId, 'seti_')) {
            $setupIntent = \Stripe\SetupIntent::retrieve($intentId);
            $paymentMethodId = $setupIntent->payment_method;
        } else {
            $paymentMethodId = $paymentIntent->payment_method;
        }

        if (!$paymentMethodId) {
            throw new \Exception("No payment method found for this customer.");
        }

        // Create NEW payment intent because no valid uncaptured PI exists
        $newIntent = \Stripe\PaymentIntent::create([
            'amount'               => (int) ($amount * 100),
            'currency'             => $order->currency,
            'customer'             => $customerId,
            'payment_method'       => $paymentMethodId,
            'off_session'          => true,
            'confirm'              => true,
            'description'          => "Charge for Order #{$order->id}",
        ]);

        $order->payment_intent_id = $newIntent->id;
        $order->save();

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => "Payment captured.",
            'data'    => $newIntent
        ]);

    } catch (\Exception $e) {
        DB::rollBack();

        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 400);
    }
}



    public function confirmInitialPayment($orderId)
{
    DB::beginTransaction();

    try {
        $order = Order::findOrFail($orderId);

        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

        // Retrieve existing intent (created in step 1)
        $intentId = $order->payment_intent_id;
        if (!$intentId) {
            throw new \Exception("No initial PaymentIntent found.");
        }

        // Retrieve PaymentIntent
        $paymentIntent = \Stripe\PaymentIntent::retrieve($intentId);

        // Extract the payment method
        $paymentMethodId = $paymentIntent->payment_method;
        if (!$paymentMethodId) {
            throw new \Exception("No PaymentMethod saved on initial PaymentIntent.");
        }

        // Confirm the payment — this will charge the user
        $confirmed = \Stripe\PaymentIntent::confirm($intentId, [
            'off_session' => true,    // charge without user present
        ]);

        // Save transaction info
        $order->transaction_id = $confirmed->charges->data[0]->id ?? $confirmed->id;
        $order->payment_status = 1; // paid
        $order->save();

        OrderPayment::create([
            'order_id'          => $order->id,
            'payment_intent_id' => $confirmed->id,
            'transaction_id'    => $confirmed->charges->data[0]->id ?? $confirmed->id,
            'payment_method'    => 'card',
            'amount'            => ($confirmed->amount / 100),
            'currency'          => $confirmed->currency,
            'status'            => $confirmed->status,
            'action'            => 'initial_charge',
            'response_payload'  => json_encode($confirmed),
        ]);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => "Initial payment captured successfully.",
            'data'    => $confirmed
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
        ], 500);
    }
}

public function cancelUncapturedAmount234($orderId)
{
    DB::beginTransaction();

    try {
        $order = Order::findOrFail($orderId);

        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

        $intentId = $order->payment_intent_id;

        if (!$intentId) {
            throw new \Exception("No payment intent found for this order.");
        }

        // Retrieve payment intent
        $paymentIntent = \Stripe\PaymentIntent::retrieve($intentId);

        // Stripe can only cancel if still in requires_capture state
        if ($paymentIntent->status !== 'requires_capture') {
            throw new \Exception("This payment intent cannot be canceled because it is already captured or canceled.");
        }

        // Cancel / void the uncaptured amount
        $canceledIntent = \Stripe\PaymentIntent::cancel($intentId);

        // Update order status
        $order->payment_status = 0; // or any status meaning "capture canceled"
        $order->save();

        // Log the cancellation
        OrderPayment::create([
            'order_id'          => $order->id,
            'payment_intent_id' => $intentId,
            'transaction_id'    => $intentId,
            'payment_method'    => 'card',
            'status'            => 'canceled',
            'amount'            => 0,
            'currency'          => $order->currency,
            'action'            => 'cancel_uncaptured',
            'response_payload'  => json_encode($canceledIntent),
        ]);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Uncaptured amount canceled successfully.',
            'status' => $canceledIntent->status,
            'data' => $canceledIntent
        ]);

    } catch (\Exception $e) {
        DB::rollBack();

        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
        ], 500);
    }
}


public function cancelUncapturedAmount($orderId)
{
    DB::beginTransaction();

    try {
        $order = Order::findOrFail($orderId);

        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

        $intentId = $order->payment_intent_id;

        if (!$intentId) {
            throw new \Exception("No payment intent found for this order.");
        }

        // Retrieve payment intent
        $paymentIntent = \Stripe\PaymentIntent::retrieve($intentId);

        // Only cancel if still authorized (requires_capture)
        if ($paymentIntent->status !== 'requires_capture') {
            throw new \Exception("This payment intent cannot be canceled because it is already captured or canceled.");
        }

        // Correct method → you MUST call cancel() on the object, not statically
        $canceledIntent = $paymentIntent->cancel();

        // Update order
        $order->payment_status = 0; // mark payment cancelled

        $order->balance_amount = $order->total_amount;
        $order->save();

        // Log the cancellation
        OrderPayment::create([
            'order_id'          => $order->id,
            'payment_intent_id' => $intentId,
            'transaction_id'    => $intentId,
            'payment_method'    => 'card',
            'status'            => 'canceled',
            'amount'            => 0,
            'currency'          => $order->currency,
            'action'            => 'cancel_uncaptured',
            'response_payload'  => json_encode($canceledIntent),
        ]);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Uncaptured amount cancelled successfully.',
            'status'  => $canceledIntent->status,
            'data'    => $canceledIntent,
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
        ], 500);
    }
}


public function refundMultiple(Request $request, Order $order)
{
    $request->validate([
        'amount' => 'required|numeric|min:0.5'
    ]);



    $refundTarget = $request->amount;
    $remaining = $refundTarget;


    if ($refundTarget > $order->booked_amount) {
        return response()->json([
            'success' => false,
            'message' => "Refund amount cannot exceed the booked amount ({$order->booked_amount})."
        ], 400);
    }

    // ❌ Prevent negative booked amount updates
    if (($order->booked_amount - $refundTarget) < 0) {
        return response()->json([
            'success' => false,
            'message' => "Refund exceeds available refundable amount."
        ], 400);
    }


    \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

    $payments = $order->payments()->orderBy('id')->get();

    foreach ($payments as $payment) {

        if ($remaining <= 0) break;

        $alreadyRefunded = $payment->refund_amount ?? 0;
        $remainingRefundable = $payment->amount - $alreadyRefunded;

        if ($remainingRefundable <= 0) continue;

        $refundNow = min($remaining, $remainingRefundable);

        $refund = \Stripe\Refund::create([
            'payment_intent' => $payment->payment_intent_id,
            'amount' => (int)($refundNow * 100)
        ]);
        
        $payment->update([
            'refund_amount' => $alreadyRefunded + $refundNow,
            'refunded_at' => now(),
            'refund_id' => $refund->id,
            'status' => ($alreadyRefunded + $refundNow) >= $payment->amount ?
                        'refunded' : 'partial_refunded'
        ]);

        $remaining -= $refundNow;
    }
    dd($refund);
    // Update order totals
    $order->booked_amount -= $refundTarget;
    $order->balance_amount = max($order->total_amount - $order->booked_amount, 0);
    $order->save();

    return response()->json([
        'success' => true,
        'message' => "Refunded $refundTarget successfully."
    ]);
}








    

}
