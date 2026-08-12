<?php

namespace App\Http\Controllers;

use App\Exports\ManifestExport;
use App\Imports\OrderImport;
use App\Imports\OrderMultiSheetImport;
use App\Imports\OrdersImport;
use App\Mail\EmailManager;
use App\Models\Addon;
use App\Models\EmailTemplate;
use App\Models\Order;
use App\Models\OrderActions;
use App\Models\OrderCustomer;
use App\Models\OrderEmailHistory;
use App\Models\OrderPayment;
use App\Models\OrderPaymentDetail;
use App\Models\OrderTour;
use App\Models\PickupLocation;
use App\Models\SmsTemplate;
use App\Models\StripeWebhookLog;
use App\Models\Tour;
use App\Models\TourPricing;
use App\Models\TourSpecialDeposit;
use App\Models\User;
use App\Notifications\NewOrderNotification;
use App\Services\OrderPaymentSummaryService;
use App\Services\CheckoutDiscountService;
use App\Services\CheckoutTotalService;
use App\Services\TwilioService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Facades\Excel;
use Stripe\Cancel;
use Stripe\PaymentIntent;
use Stripe\Refund;
use Stripe\Stripe;
use Validator;


class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['customer', 'orderTours.tour', 'payments', 'partner', 'latestPaymentLog'])
            ->whereHas('customer', function ($q) {
                $q->whereNotNull('first_name')
                  ->where('first_name', '!=', ''); // exclude empty strings
            })
            ->addSelect([
                'latest_payment_at' => OrderPayment::query()
                    ->selectRaw('MAX(updated_at)')
                    ->whereColumn('order_payments.order_id', 'orders.id')
                    ->where('amount', '>', 0)
                    ->whereNotIn('payment_type', ['DISCOUNT', 'PROMOCODE', 'BOOKINGFEE']),
            ])
            ->orderByRaw('COALESCE(latest_payment_at, orders.created_at) DESC')
            ->orderByDesc('orders.id');

        // Search by order number or customer name
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($q2) use ($search) {
                      $q2->where('first_name', 'like', "%{$search}%")
                         ->orWhere('last_name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

       if ($source = $request->input('source')) {
            $query->whereRaw('LOWER(source) = ?', [strtolower($source)]);
        }

        if ($excluded_source = $request->input('excluded_source')) {
            $excluded_source = array_map('strtolower', $excluded_source);

            $query->whereNotIn(DB::raw('LOWER(source)'), $excluded_source);
        }

        // Filter by tour product
        // if ($product = $request->input('product')) {
        //     $query->whereHas('orderTours', function ($q) use ($product) {
        //         $q->where('tour_id', $product);
        //     });
        // }

        if ($product = $request->input('product')) {
            $product = array_filter((array)$product);
            if (!empty($product)) {
                $query->whereHas('orderTours', function ($q) use ($product) {
                    $q->whereIn('tour_id', $product);
                });
            }
        }

        if ($excludeProducts = $request->input('exclude_product')) {

            $excludeProducts = array_filter((array)$excludeProducts);

            if (!empty($excludeProducts)) {

                $query->whereDoesntHave('orderTours', function ($q) use ($excludeProducts) {

                    $q->whereIn('tour_id', $excludeProducts);

                });

            }

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
        if ($tour_start_date = $request->input('tour_start_date')) {
            $dates = explode(' - ', $tour_start_date);
            if (count($dates) === 2) {
                $startDate = Carbon::parse($dates[0])->format('Y-m-d');
                $endDate = Carbon::parse($dates[1])->format('Y-m-d');
                $query->whereHas('orderTours', function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('tour_date', [$startDate, $endDate]);
                });
            } else {
                $query->whereHas('orderTours', function ($q) use ($tour_start_date) {
                    $q->whereDate('tour_date', '=', $tour_start_date);
                });
            }
        }

        // Filter by tour start date range
        if ($order_created_date = $request->input('order_created_date')) {
            $dates = explode(' - ', $order_created_date);
            if (count($dates) === 2) {
                $startDate = Carbon::parse($dates[0]);
                $endDate = Carbon::parse($dates[1])->addDay();
                $query->whereBetween('created_at', [$startDate, $endDate]);
            } else {
                $query->whereDate('created_at', '=', $order_created_date);
            }
        }


/*
        if ($tourFilter = $request->input('tour_date_filter')) {

                $today = Carbon::today();

                if ($tourFilter === 'today') {

                    $query->whereHas('orderTours', function ($q) use ($today) {
                        $q->whereDate('tour_date', $today->toDateString());
                    });

                } elseif ($tourFilter === 'yesterday') {

                    $yesterday = Carbon::yesterday();

                    $query->whereHas('orderTours', function ($q) use ($yesterday) {
                        $q->whereDate('tour_date', $yesterday->toDateString());
                    });

                } else {

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

                    if ($from) {
                        $startDate = $from->copy()->startOfDay();
                        $endDate = $today->copy()->endOfDay();

                        $query->whereHas('orderTours', function ($q) use ($startDate, $endDate) {
                            $q->whereBetween('tour_date', [$startDate, $endDate]);
                        });
                    }
                }
            }

        if ($filter = $request->input('date_filter')) {

            $today = Carbon::today();

            if ($filter === 'today') {

                $query->whereDate('created_at', $today->toDateString());

            }  elseif ($filter === 'yesterday') {

                    $yesterday = Carbon::yesterday();

                    $query->whereDate('created_at', $yesterday->toDateString());

                }else {

                switch ($filter) {
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

                if ($from) {
                    $startDate = $from->copy()->startOfDay();
                    $endDate = $today->copy()->endOfDay();

                    $query->whereBetween('created_at', [$startDate, $endDate]);
                }
            }
        }
*/
        $totalOrders = (clone $query)->count();
        $perPage = $request->input('per_page', 10);

                        //die (getFullSql($query));

        $orders = $query->paginate($perPage)->appends($request->all()); // preserve filters in pagination
        $paymentSummaryService = new OrderPaymentSummaryService();
        $orders->getCollection()->each(function (Order $order) use ($paymentSummaryService) {
            $summary = $paymentSummaryService->summarize($order->payments);
            $grossAmount = round((float) $order->orderTours->sum('total_amount'), 2);
            $order->canonical_balance = round(max(
                $grossAmount - $summary['total_credits'],
                0
            ), 2);
            $order->canonical_paid = $summary['paid_amount'];
            $order->canonical_authorized = $summary['authorized_amount'];
        });

        $products = Tour::select('id', 'title')->where('status', 1)->get(); 

        $selectedProducts = Tour::whereIn(
            'id',
            (array)$request->product
        )->get(['id','title']);

        $excludedProducts = Tour::whereIn(
            'id',
            (array)$request->exclude_product
        )->get(['id','title']);// for filter dropdown

        return view('admin.order.index', compact('orders', 'products', 'totalOrders', 'selectedProducts', 'excludedProducts'));
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
        $customers = User::where('user_type', 'member')
                            ->orderBy('first_name', 'asc')
                            ->orderBy('last_name', 'asc')
                            ->get();

        return view('admin.order.internal-order', compact('tours', 'customers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->merge([
            'customer_id' => $request->customer_id ?: null
        ]);    

       
        $validated = $request->validate([

            // Existing Customer
            'customer_id'          => 'nullable',

            'payment_intent_id'    => 'nullable|required_if:payment_type,card',

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
            'pickup_id.*'          => ['required', function($attribute, $value, $fail) use ($request) {
            $index = explode('.', $attribute)[1]; // get index of the tour row
            $tourId = $request->tour_id[$index] ?? null;
            $pickupValue = $value;

            // Get tour pickups
            $tour = \App\Models\Tour::find($tourId);
            if(!$tour) return;

            $pickups = $tour->pickups;

            if(!empty($pickups) && isset($pickups[0])) {
                $firstPickup = $pickups[0];
                if($firstPickup->name === 'Pickup' || $firstPickup->name !== 'No Pickup') {
                    // Pickup required
                    if($pickupValue === null || $pickupValue === '') {
                        $fail('Pickup is required for tour '.$tour->title);
                    }

                    // If "other", check pickup_name
                    if($pickupValue === 'other') {
                        $pickupName = $request->pickup_name[$index] ?? '';
                        if(trim($pickupName) === '') {
                            $fail('Please enter pickup location for tour '.$tour->title);
                        }
                    }
                }
            }
        }],

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
        // dd($data);

        // Make sure at least one tour is selected
        $tourIds = array_unique($data['tour_id']) ?? [];
        if (empty($tourIds)) {
            return response()->json([
                'status' => false,
                'message' => 'No tour selected.',
            ], 422);
        }
        $tourIds = array_values($tourIds);

        $firstTourId = $tourIds[0]; // Keep this for orders.tour_id foreign key


        $minList = $request['tour_pricing_min_'.$firstTourId]; // you can send this hidden
        $qtyList = $request['tour_pricing_qty_'.$firstTourId];

        $valid = false;

        foreach ($qtyList as $index => $qty) {
            $minRequired = $minList[$index];

            // qty must be >= min AND qty must not be zero
            if ((int)$qty > 0 && (int)$qty >= (int)$minRequired) {

                $valid = true;
                break;
            }
        }

        if (!$valid) {
            return back()->withErrors([
                'quantity_error' => 'At least one quantity must meet the minimum requirement.'
            ])->withInput();
        }

        // $pickupId   = $request->pickup_id ?? [];
        // $pickupName = $request->pickup_name ?? [];

        // $pickupId = is_array($pickupId) ? $pickupId : [];
        // $pickupName = is_array($pickupName) ? $pickupName : [];

        // --------------------------------------------
        // 1) Check once if any selected tour has "No Pickup"
        // --------------------------------------------
        $hasNoPickupTour = \App\Models\Tour::whereIn('id', $tourIds)
            ->whereHas('pickups', function($q) {
                $q->where('name', 'No Pickup');
            })
            ->exists();

        // If ANY tour has "No Pickup", no validation needed
        if (!$hasNoPickupTour) {
            // --------------------------------------------
            // 2) Require at least ONE pickupId or pickupName
            // --------------------------------------------

            // if (!is_array($pickupId)) {
            //     $pickupId = [$pickupId];
            // }

            // if (!is_array($pickupName)) {
            //     $pickupName = [$pickupName];
            // }

            // $pickupId   = $request->pickup_id ?? 0;
            // $pickupName = $request->pickup_name ?? "";

            // $anyPickupIdFilled = array_filter($pickupId);
            // $anyPickupNameFilled = array_filter(array_map('trim', $pickupName));

            // if (empty($pickupId) && empty($pickupName)) {
            //     return back()->withErrors([
            //         'pickup_error' => 'Pickup or pickup name is required.'
            //     ])->withInput();
            // }
        }

        // dd($tourIds);

        
        DB::beginTransaction();
        try {
            // ===== Create Order =====
            $order = Order::create([
                'tour_id'           => $firstTourId,
                'user_id'           => auth()->id() ?? 0,
                'order_number'      => unique_order(),
                'currency'          => $request->currency ?? 'CAD',
                'order_status'      => $request->order_status,
                'payment_status'    => 5,
                'total_amount'      => 0,
                'balance_amount'    => 0,
                'additional_info'   => $request->additional_info ?? '',
                'internal_notes'    => $request->internal_notes ?? '',
                'created_by'        => auth()->user()->id,
                'source'            => $request->source ?? "internal",
                'created_at'        => $request->order_date ? Carbon::parse($request->order_date)->format('Y-m-d') : now()
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

                if($request->has('addToCustomer')){
                    $userExists = User::where('email', $request->customer_email)->exists();
                    if(!$userExists){
                        User::create([
                            'first_name'   => $firstName,
                            'last_name'    => $lastName,
                            'name'         => $firstName . " " . $lastName,
                            'email'        => $user->email ?? 'N/A',
                            'phone'        => $user->phone ?? 'N/A',
                            'user_type'    => 'Member',
                        ]);

                    }
                        
                }
            } else {
                // Fallback to request inputs
                $user = User::where('email', $request->customer_email)->first();

                $user_id = $user?->id ?? 0;

                $customer = OrderCustomer::create([
                    'order_id'     => $order->id,
                    'user_id'      => $user_id,
                    'first_name'   => $request->customer_first_name,
                    'last_name'    => $request->customer_last_name,
                    'email'        => $request->customer_email ,
                    'phone'        => $request->full_phone ?? $request->customer_phone,
                    'instructions' => $request->additional_info ?? null,
                    'pickup_id'    => $request->pickup_id ?? null,
                    'pickup_name'  => $request->pickup_name ?? null,
                ]);
                if($request->has('addToCustomer')){
                    
                    if(!$user_id){
                        User::create([
                            'first_name'   => $request->customer_first_name,
                            'last_name'    => $request->customer_last_name,
                            'name'         => $request->customer_first_name . " " . $request->customer_last_name,
                            'email'        => $request->customer_email ?? 'N/A',
                            'phone'        => $request->full_phone ?? $request->customer_phone,
                            'user_type'    => 'Member',
                        ]);

                    }
                        
                }

            }

            //echo '<pre>'; print_r( $customer ); echo '</pre>'; exit;

            // ===== Loop Through Tours =====
            $totalOrderAmount = 0;
            foreach ($tourIds as $index => $tourId) {
                $tour = Tour::with(['pricings', 'addons', 'taxes_fees'])->findOrFail($tourId);

                $tour_pricing_ids   = $data["tour_pricing_id_$tourId"] ?? [];
                $tour_pricing_qtys  = $data["tour_pricing_qty_$tourId"] ?? [];
                $tour_pricing_prices= $data["tour_pricing_price_$tourId"] ?? [];

                $tour_extra_ids     = $data["tour_extra_id_$tourId"] ?? [];
                $tour_extra_qtys    = $data["tour_extra_qty_$tourId"] ?? [];
                $tour_extra_prices  = $data["tour_extra_price_$tourId"] ?? [];

                $pricing = [];
                $extras  = [];
                $subtotal = 0;
                $quantity = 0;

                $discounts = []  ;

                // ===== Pricing =====
                foreach ($tour_pricing_ids as $i => $pricingId) {
                    $qty   = $tour_pricing_qtys[$i] ?? 0;

                    if($qty <1){
                        continue;
                    }
                    $price = $tour_pricing_prices[$i] ?? 0;
                    $actual_price = $price;
                    $discount = 0;

                    // if($i == 0) {
                    //     $deposite_rule = $tour->specialDeposit;
                    //     if($deposite_rule->is_discount === 1 && $deposite_rule->discount_type === 'PERCENT') {
                    //         $discount = ($price * $deposite_rule->discount_value)/100;
                    //     }
                    //     else if($deposite_rule->is_discount === 1 && $deposite_rule->discount_type === 'FIXED') {
                    //         $discount = $deposite_rule->discount_value;
                    //     }

                    //     $price = $price - $discount;

                    //     if($discount>0) {
                    //         $discounts[] = [
                    //             'tour_id'  => $tour->id,
                    //             'discount' => $deposite_rule->discount_value ?? 0,
                    //             'quantity' => $qty ?? 1,
                    //             'label'    => 'Special Discount',
                    //             'type'     => $deposite_rule->discount_type,
                    //             'price'    => ($discount * $qty),
                    //         ];
                    //     }
                    // }

                    $tourPricing = TourPricing::find($pricingId);
                    $label = str_ireplace('Group', 'Participants', $tourPricing->label);
                    $pricing[] = [
                        'tour_id'         => $tour->id,
                        'tour_pricing_id' => $pricingId,
                        'label'           => $label,
                        'price_type'      => $tour->price_type,
                        'actual_price'    => $actual_price,
                        'price'           => $price,
                        'discount'        => $discount,
                        'quantity'        => $qty,
                        'total_price'     => $tour->price_type == 'FIXED'? $price :  $qty * $price,
                    ];
                    $subtotal += $tour->price_type == 'FIXED'? $price :  $qty * $price;// $qty * $price;
                    $quantity += $qty;
                }                

                // ===== Extras =====
                foreach ($tour_extra_ids as $i => $extraId) {
                    $qty   = $tour_extra_qtys[$i] ?? 0;
                    if($qty <1){
                        continue;
                    }
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
                // dd( $request->input('tour_startdate', []));
                $tourStartDates = $request->input('tour_startdate', []);

                $tourStartDates = array_map(function ($date) {
                    return Carbon::parse($date)->format('Y-m-d');
                }, $tourStartDates);
                $tourStartTimes = $request->input('tour_starttime', []);

                // For the current tour row (index 0 if only one tour)
                $selectedDate = $tourStartDates[0] ?? null;
                $selectedTime = $tourStartTimes[0] ?? null;

                // ===== Save OrderTour =====
                OrderTour::create([
                    'order_id'         => $order->id,
                    'tour_id'          => $tourId,
                    'tour_date'        => $selectedDate,
                    'tour_time'        => $selectedTime,
                    'tour_pricing'     => json_encode($pricing),
                    'tour_extra'       => json_encode($extras),
                    'tour_fees'        => json_encode($fees),
                    'discount'         => json_encode($discounts),
                    'number_of_guests' => $quantity,
                    'total_amount'     => $subtotal,
                ]);
            }

            $totalPaymentAmount = 0;
            if ($request->paymentType) {
                $payments = [];

                foreach ($request->paymentType as $i => $type) {

                    
                    //$amount = $request->amount[$i] ?? null;

                    $amount          = $request->amount[$i] ?? 0;
                    $collection_date = $request->collection_date[$i] ?? null;
                    $transactionId   = $request->transactionId[$i] ?? null;

                    // Skip empty rows
                    if (empty($type) && empty($amount) && $amount == 0) {
                        continue;
                    }

                    // Add amount to total (only if valid)
                    if (!empty($amount)) {
                        $totalPaymentAmount += floatval($amount);
                    }

                    if($type == "COMMISSION"){
                        $payments[] = [
                                'order_id'          => $order->id,
                                'payment_intent_id' => null,
                                'transaction_id'    => $transactionId,
                                'payment_type'      => "EXCLUDED",
                                'collection_type'   => 'Outside',
                                'collection_date'   => Carbon::parse($collection_date)->format('Y-m-d'),
                                'amount'            => $subtotal - $amount,
                                'currency'          => $order->currency,
                                'current_rate'      => (float) ($order->current_rate ?: 1),
                                'status'            => 'succeeded',
                                'created_at'        => now(),
                                'updated_at'        => now(),
                            ];
                    }

                    $payments[] = [
                                'order_id'          => $order->id,
                                'payment_intent_id' => null,
                                'transaction_id'    => $transactionId,
                                'payment_type'      => $type,
                                'collection_type'   => 'Outside',
                                'collection_date'   => Carbon::parse($collection_date)->format('Y-m-d'),
                                'amount'            => $amount,
                                'currency'          => $order->currency,
                                'current_rate'      => (float) ($order->current_rate ?: 1),
                                'status'            => 'succeeded',
                                'created_at'        => now(),
                                'updated_at'        => now(),
                            ];
                }
                OrderPayment::insert($payments);
            }


            // ===== Update Order totals =====
            $balanceAmount = $totalOrderAmount - $totalPaymentAmount;
            // $order->total_amount = $totalOrderAmount;
            // $order->balance_amount = $balanceAmount;
            // $order->booked_amount = $totalOrderAmount - $balanceAmount;


            $totals = $this->calculateTotals(
                $totalOrderAmount,
                $totalPaymentAmount,
                $request->paymentType ?? [],
                $request->amount ?? []
            );
            
            $order->total_amount   = $totals['total'];
            $order->booked_amount  = $totals['paid'];
            $order->balance_amount = $totals['balance'];
            $balanceAmount = $totals['balance'];
            $balanceAmount = $totals['balance'];

            
            
            // dd($request->payment_type);
            if( $order->save() ){

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
                        $name = $customer->first_name.' '.$customer->last_name;
                        $stripeCustomer = \Stripe\Customer::retrieve($order->stripe_customer_id);
                    }

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

                    $add_ccnow      = (int)$request->add_ccnow;
                    $charge_ccnow   = (int)$request->charge_ccnow;
                    $chargeAmount   = (float)$request->charge_ccnow_amount;


                    if ($chargeAmount > 0) {
                        $order_actions = [];
                        if ( $charge_ccnow ) {
                            $pi = \Stripe\PaymentIntent::create([
                                'customer'  => $stripeCustomer->id,
                                'amount' => intval(round($chargeAmount * 100)),
                                'currency' => $order->currency,
                                'automatic_payment_methods' => [
                                    'enabled' => true,
                                    'allow_redirects' => 'never',  // 🔥 prevents Stripe from requiring return_url
                                ],
                                
                                'capture_method' => 'manual',
                                'description' => $tour->title,
                                'statement_descriptor_suffix' => $order->order_number,
                                'metadata'  => $metaData,
                                'setup_future_usage'=> 'off_session',
                            ]);
                            $order->balance_amount  = max($balanceAmount - ($chargeAmount ?? 0), 0); 
                            $order->booked_amount   = $chargeAmount;
                            $order->payment_status  = 1; // Paid
                            $order->payment_method  = 'card';
                            $order->payment_intent_client_secret = $pi->client_secret;
                            $order->payment_intent_id = $pi->id;
                            $order->payment_method_id = $request->payment_intent_id;

                            // ✅ If frontend sent payment_method_id, confirm & capture immediately
                            if ($request->filled('payment_intent_id')) {
                                $pi = \Stripe\PaymentIntent::retrieve($pi->id);
                                $pi->confirm(['payment_method' => $request->payment_intent_id]);
                                $pi->capture();

                                // $payments = [
                                //     'order_id'       => $order->id,
                                //     'payment_type'   => 'CREDITCARD',
                                //     'transaction_id' => $pi->id,
                                //     'date'           => now(),
                                //     'amount'         => $chargeAmount,
                                //     'collection_type'=> 'Inside',
                                //     'created_at'     => now(),
                                //     'updated_at'     => now(),
                                // ];

                                // OrderPaymentDetail::insert($payments);

                                $paymentMethod = \Stripe\PaymentMethod::retrieve($request->payment_intent_id);

                                $card = $paymentMethod->card;

                                $brand  = $card->brand;          // visa, mastercard
                                $last4  = $card->last4;          // 4242
                                // $expMonth = $card->exp_month;    // 12
                                // $expYear  = $card->exp_year;     // 2029
                                // $funding = $card->funding;       // credit/debit/prepaid
                                // $country = $card->country;       // US, IN

                                OrderPayment::create([
                                    'order_id'          => $order->id,
                                    'payment_intent_id' => $pi->id,
                                    'transaction_id'    => $pi->charges->data[0]->id ?? $pi->id,
                                    'payment_method'    => 'card',
                                    'payment_type'      => 'CREDITCARD',
                                    'collection_type'   => 'Inside',
                                    'collection_date'   => date('Y-m-d'),
                                    'card_brand'        => $brand,
                                    'card_last4'        => $last4,
                                    'amount'            => $chargeAmount,
                                    'currency'          => $order->currency,
                                    'status'            => 'succeeded',
                                    'action'            => 'manual_charge',
                                    'response_payload'  => json_encode($pi),
                                    'created_at'        => now(),
                                    'updated_at'        => now(),
                                ]);

                                // ===== Save OrderActions =====
                                $order_actions = [[
                                        'order_id'         => $order->id,
                                        'performed_by'     => Auth::id(),
                                        'notes'            => "<a href='". route('admin.customers.edit', encrypt($customer->id)) ."'>".$name."</a> made a new order on your booking form",
                                        'created_at'       => now(),
                                        'updated_at'       => now()
                                    ],[
                                        'order_id'         => $order->id,
                                        'performed_by'     => Auth::id(),
                                        'notes'            => "Order created for ".$tour->title." on ".$order->created_at->format('Y-m-d H:i:s'),
                                        'created_at'       => now(),
                                        'updated_at'       => now()
                                    ],[
                                        'order_id'         => $order->id,
                                        'performed_by'     => Auth::id(),
                                        'notes'            => "System attached credit card $last4 ($brand) to this order",
                                        'created_at'       => now(),
                                        'updated_at'       => now()
                                    ],[
                                        'order_id'         => $order->id,
                                        'performed_by'     => Auth::id(),
                                        'notes'            => "System charged credit card $last4 for $chargeAmount " .$order->currency. " Reference number is ".$pi->id,
                                        'created_at'       => now(),
                                        'updated_at'       => now()
                                    ]
                                ];
                            }
                        }
                        else if( $add_ccnow ) {
                            // 2️⃣ Attach Payment Method to Customer
                            $paymentMethod = \Stripe\PaymentMethod::retrieve($request->payment_intent_id)
                                ->attach(['customer' => $stripeCustomer->id]);

                            // 3️⃣ Set this payment method as default
                            \Stripe\Customer::update($stripeCustomer->id, [
                                'invoice_settings' => [
                                    'default_payment_method' => $request->payment_intent_id,
                                ]
                            ]);


                            $card = $paymentMethod->card;
                            $brand  = $card->brand;          // visa, mastercard
                            $last4  = $card->last4;          // 4242

                            $order->balance_amount      = $balanceAmount; 
                            $order->booked_amount       = 0;
                            $order->payment_status      = 0; // Unpaid                        
                            $order->payment_method_id   = $request->payment_intent_id;
                            $order->payment_method      = 'card';

                            // $payments = [
                            //     'order_id'       => $order->id,
                            //     'payment_type'   => 'CREDITCARD',
                            //     'transaction_id' => $request->payment_intent_id,
                            //     'date'           => now(),
                            //     'amount'         => 0,
                            //     'collection_type'=> 'Outside',
                            //     'created_at'     => now(),
                            //     'updated_at'     => now(),
                            // ];

                            // OrderPaymentDetail::insert($payments);

                            OrderPayment::create([
                                'order_id'          => $order->id,
                                'payment_intent_id' => $request->payment_intent_id,
                                'payment_type'      => 'CREDITCARD',
                                'collection_type'   => 'Inside',
                                'collection_date'   => date('Y-m-d'),
                                'amount'            => $chargeAmount,
                                'currency'          => $order->currency,
                                'status'            => 'pending',
                                'created_at'        => now(),
                                'updated_at'        => now(),
                            ]);

                            // ===== Save OrderActions =====
                            $order_actions = [[
                                    'order_id'         => $order->id,
                                    'performed_by'     => $customer->id,
                                    'notes'            => "<a href='". route('admin.customers.edit', encrypt($customer->id)) ."'>".$name."</a> made a new order on your booking form",
                                    'created_at'       => now(),
                                    'updated_at'       => now()
                                ],[
                                    'order_id'         => $order->id,
                                    'performed_by'     => $customer->id,
                                    'notes'            => "Order created for ".$tour->title." on ".$order->created_at->format('Y-m-d H:i:s'),
                                    'created_at'       => now(),
                                    'updated_at'       => now()
                                ],[
                                    'order_id'         => $order->id,
                                    'performed_by'     => $customer->id,
                                    'notes'            => "System attached credit card $last4 ($brand) to this order",
                                    'created_at'       => now(),
                                    'updated_at'       => now()
                                ]
                            ];
                        }
                        else {
                            $order->balance_amount      = $balanceAmount; 
                            $order->booked_amount       = 0;
                            $order->payment_status      = 0; // Unpaid  
                            
                            // ===== Save OrderActions =====
                            $order_actions = [[
                                    'order_id'         => $order->id,
                                    'performed_by'     => $customer->id,
                                    'notes'            => "<a href='". route('admin.customers.edit', encrypt($customer->id)) ."'>".$name."</a> made a new order on your booking form",
                                    'created_at'       => now(),
                                    'updated_at'       => now()
                                ],[
                                    'order_id'         => $order->id,
                                    'performed_by'     => $customer->id,
                                    'notes'            => "Order created for ".$tour->title." on ".$order->created_at->format('Y-m-d H:i:s'),
                                    'created_at'       => now(),
                                    'updated_at'       => now()
                                ]
                            ];
                        }

                        OrderActions::insert($order_actions);


                    } /*else {
                        $si = \Stripe\SetupIntent::create([
                            'customer'  => $stripeCustomer->id,
                            'automatic_payment_methods' => ['enabled' => true],
                            'usage'     => 'off_session',
                            'metadata'  => $metaData
                        ]);
                        $order->payment_intent_client_secret = $si->client_secret;
                        $order->payment_intent_id = $si->id;
                    }*/

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

            DB::commit();

            return redirect()->route('admin.orders.edit', [encrypt($order->id)]);

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
        $paymentSummary = (new OrderPaymentSummaryService())->summarize(
            $order->payments
        );
        $grossOrderTotal = round((float) $order->orderTours->sum('total_amount'), 2);
        $netOrderTotal = round(max(
            $grossOrderTotal
            - $paymentSummary['special_discount']
            - $paymentSummary['promo_discount'],
            0
        ), 2);
        $accountingBalance = round(max(
            $grossOrderTotal - $paymentSummary['total_credits'],
            0
        ), 2);
        
        $tours = Tour::orderBy('title', 'ASC')->get();
        // $email_templates = EmailTemplate::get();


        $actions = $order->actions()
            ->orderByDesc('created_at')
            ->paginate(7, ['*'], 'actions_page');
        if (request()->ajax()) {
            return view('admin.partials.order.recent-actions-table', compact('actions'))->render();
        }

        $emailHistories = $order->emailHistories()
            ->orderByDesc('created_at')
            ->paginate(7, ['*'], 'emails_page');

        $paymentIntentIds = $order->payments()
            ->whereNotNull('payment_intent_id')
            ->pluck('payment_intent_id');
        $paymentLogs = StripeWebhookLog::query()
            ->where(function ($query) use ($order, $paymentIntentIds) {
                $query->where('order_id', $order->id);
                if ($paymentIntentIds->isNotEmpty()) {
                    $query->orWhereIn('payment_intent_id', $paymentIntentIds);
                }
            })
            ->orderByDesc('created_at')
            ->paginate(7, ['*'], 'payments_page');
        $paymentLedgerLogs = $order->payments()
            ->whereNotNull('payment_intent_id')
            ->orderByDesc('created_at')
            ->get();

        $email_templates = EmailTemplate::whereIn('identifier', [
            'order_detail',
            'order_cancelled',
            'order_confirmed',
            'trip_completed',
            'payment_receipt',
            'order_pending',
            'payment_request',
            'follow_up',
            // 'abandoned_reminder',
            'request_quote',
        ])->get();
        $sms_templates = SmsTemplate::get();
        $customers = User::where('user_type', 'member')->get();
        $pickupLocations = PickupLocation::get();
        return view('admin.order.edit', compact(['order', 'paymentSummary', 'grossOrderTotal', 'netOrderTotal', 'accountingBalance', 'tours', 'email_templates', 'sms_templates', 'pickupLocations', 'actions', 'emailHistories', 'paymentLogs', 'paymentLedgerLogs']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        
        $validator = Validator::make($request->all(), [
            'order_status'   => 'required|max:255',

            'tour_startdate'   => 'required|array',
            'tour_startdate.*' => 'required|date',

            'tour_starttime'   => 'nullable|array',
            'tour_starttime.*' => 'nullable|string|max:40',
        ],
        [
            'order_status.required'   => 'Please select order status',
            'tour_startdate.required'   => 'Please select tour start date',
            'tour_starttime.required'   => 'Please select tour start time',
        ]);

        if( $validator->fails() ){
            return redirect()->back()->withErrors($validator)->withInput();
        }
        
        $order = Order::findOrFail( $id );
        $order->order_status    = $request->order_status;
        $order->additional_info = $request->additional_info;

        $tourIds = $request->tour_id; // [19, 21, 90, 11]       
        
        $orderId = $id;
        $total   = 0;
        if($orderId && is_array($request->tour_id)) {
            foreach ($tourIds as $index => $tourId) {
                $tour = Tour::findOrFail( $tourId );
                $totalBeforeTour = $total;
                $orderTour = OrderTour::where('order_id', $orderId)
                    ->where('tour_id', $tourId)
                    ->first();
                $paymentSummaryBeforeEdit = (new OrderPaymentSummaryService())->summarize(
                    $order->payments()->get()
                );
                $hasSettledPayments = $paymentSummaryBeforeEdit['paid_amount'] > 0;
                $hasCommittedPayments = $hasSettledPayments
                    || $paymentSummaryBeforeEdit['authorized_amount'] > 0;
                $checkoutTotals = new CheckoutTotalService();
                $discountsLocked = $hasCommittedPayments
                    || strtolower((string) $order->action_name) === 'reserve';
                $previousGross = (float) ($orderTour?->total_amount ?? 0);
                $previousPricing = collect(json_decode($orderTour?->tour_pricing ?: '[]', true))
                    ->keyBy(fn ($item) => (int) ($item['tour_pricing_id'] ?? 0));
                $previousExtras = collect(json_decode($orderTour?->tour_extra ?: '[]', true))
                    ->keyBy(fn ($item) => (int) ($item['tour_extra_id'] ?? 0));
                $previousFeesForAdjustment = json_decode(
                    $orderTour?->tour_fees ?: '[]',
                    true
                ) ?: [];
                $previousAdjustmentAmount = $checkoutTotals->adjustmentAmount(
                    $previousPricing->values()->all(),
                    $previousExtras->values()->all(),
                    $previousFeesForAdjustment
                );
                // A stale tax/rounding balance must not carry already-paid
                // lines into the next customer payment request.
                $previousAdjustmentSettled = $hasSettledPayments && (
                    (float) $order->balance_amount <= 0.01
                    || $checkoutTotals->hasMatchingAdjustmentPayment(
                        $previousAdjustmentAmount,
                        $order->payments()->get()
                    )
                );
                $savedDiscountRows = json_decode($orderTour?->discount ?: '[]', true) ?: [];
                // Older admin updates could save one discount row as an object
                // (or even a scalar) instead of a list. Normalize the snapshot
                // before reading it so those orders remain editable.
                if (is_array($savedDiscountRows) && isset($savedDiscountRows['price'])) {
                    $savedDiscountRows = [$savedDiscountRows];
                }
                $savedDiscountRows = is_array($savedDiscountRows)
                    ? array_values(array_filter($savedDiscountRows, 'is_array'))
                    : [];
                $savedSpecialDiscount = collect($savedDiscountRows)->first();
                $itemAmountDelta = 0.0;
                $addonAmountDelta = 0.0;
                $addedSpecialDiscountDelta = 0.0;
                $updatedFees = null;
                $specialDiscountTotal = (float) $paymentSummaryBeforeEdit['special_discount'];
                $promoDiscountTotal = (float) $paymentSummaryBeforeEdit['promo_discount'];
                $minList = $request['tour_pricing_min_'.$tourId]; // you can send this hidden
                $qtyList = $request['tour_pricing_qty_'.$tourId];

                $valid = false;
                
                foreach ($qtyList as $indexQ => $qty) {
                    $minRequired = $minList[$indexQ];

                    // qty must be >= min AND qty must not be zero
                    if ((int)$qty > 0 && (int)$qty >= (int)$minRequired) {

                        $valid = true;
                        break;
                    }
                }

                if (!$valid) {
                    return back()->withErrors([
                        'quantity_error' => 'At least one quantity must meet the minimum requirement.'
                    ])->withInput();
                }

                $startDate = Carbon::parse($request->tour_startdate[$index])->format('Y-m-d');// $request->tour_startdate[$index];
                // $startTime = $request->tour_starttime[$index];

                $startTimes = $request->input('tour_starttime', []);
                $startTime  = $startTimes[$index] ?? null;
                $depositRule = TourSpecialDeposit::where('use_deposit', 1)
                    ->where('tour_id', $tour->id)
                    ->first() ?: TourSpecialDeposit::where('type', 'global')->first();
                $specialDiscountEligible = $depositRule
                    && (int) $depositRule->is_discount === 1
                    && $depositRule->charge === 'NONE'
                    && $order->action_name === 'book'
                    && Carbon::parse($startDate)->startOfDay()->gte(
                        Carbon::today()->addDays((int) ($depositRule->notice_days ?? 0))
                    );
                // Preserve a discount already committed to the order snapshot,
                // but never accept a new/stale form discount when the rule or
                // selected tour date is not eligible.
                $hasSavedSpecialDiscount = !empty($savedSpecialDiscount)
                    || $specialDiscountTotal > 0;
                $specialDiscountAllowed = $specialDiscountEligible
                    || $hasSavedSpecialDiscount;
                $effectiveDiscountType = $hasCommittedPayments
                    ? ($savedSpecialDiscount['type'] ?? $depositRule?->discount_type)
                    : $depositRule?->discount_type;
                $effectiveDiscountValue = (float) ($hasCommittedPayments
                    ? ($savedSpecialDiscount['discount'] ?? $depositRule?->discount_value ?? 0)
                    : ($depositRule?->discount_value ?? 0));


                // dd($startTime);
                // $tourStartDates = $request->input('tour_startdate', []);

                // $tourStartDates = array_map(function ($date) {
                //     return Carbon::parse($date)->format('Y-m-d');
                // }, $tourStartDates);

                //TOUR PRICING
                $pricingIds = $request->input("tour_pricing_id_{$tourId}", []);
                $pricingQtys = $request->input("tour_pricing_qty_{$tourId}", []);
                $pricingPrice = $request->input("tour_pricing_price_{$tourId}", []);
                $pricingActualPrice = $request->input("tour_pricing_actual_price_{$tourId}", []);
                $pricingDiscount = $request->input("tour_pricing_discount_{$tourId}", []);




                $pricingDetails = [];
                $discount = $hasCommittedPayments
                    ? $savedDiscountRows
                    : [];
                $total_amount = 0;
                $nog = 0;


                foreach ($pricingIds as $key => $pricingId) {
                    $qty    = isset($pricingQtys[$key]) ? (int)$pricingQtys[$key] : 0;
                    $price  = isset($pricingPrice[$key]) ? (float)$pricingPrice[$key] : 0;
                    $actualPrice  = isset($pricingActualPrice[$key]) ? (float)$pricingActualPrice[$key] : 0;
                    $discount_price  = isset($pricingDiscount[$key]) ? (float)$pricingDiscount[$key] : 0;
                    if (!$discountsLocked && !$specialDiscountAllowed) {
                        $discount_price = 0;
                    }
                    $previousLine = $previousPricing->get((int) $pricingId, []);
                    $previousQty = (int) ($previousLine['quantity'] ?? 0);
                    $qtyDelta = $qty - $previousQty;
                    $tourPricing = TourPricing::findOrFail($pricingId);
                    $currentUnitPrice = currencyConvertWithoutRound(
                        (float) $tourPricing->price,
                        $tour->currency,
                        $order->currency
                    );
                    $currencySnapshot = $checkoutTotals->currencySnapshotLine(
                        $previousQty,
                        $qty,
                        (float) ($previousLine['gross_total_price']
                            ?? (($previousLine['actual_price'] ?? $actualPrice) * $previousQty)),
                        (int) ($previousLine['newly_added_quantity'] ?? 0),
                        (float) ($previousLine['newly_added_price'] ?? $previousLine['actual_price'] ?? 0),
                        $currentUnitPrice,
                        $previousAdjustmentSettled
                    );
                    $newlyAddedQuantity = $currencySnapshot['new_quantity'];
                    $actualPrice = $currencySnapshot['average_unit_price'];
                    $discountableQtyDelta = $tour->price_type == 'PER_PERSON'
                        ? max($qtyDelta, 0)
                        : (($qty > 0 ? 1 : 0) - ($previousQty > 0 ? 1 : 0));
                    $itemAmountDelta += $tour->price_type == 'PER_PERSON'
                        ? $qtyDelta * $actualPrice
                        : $discountableQtyDelta * $actualPrice;
                    $previousLineDiscount = (float) ($previousLine['discount'] ?? 0) * (
                        $tour->price_type == 'PER_PERSON' ? $previousQty : 1
                    );
                    $addedDiscount = 0.0;
                    // Discounts belong to the original checkout snapshot.
                    // Quantities added later by an admin are always charged at
                    // their full price and taxed normally.
                    if ($discountsLocked) {
                        $lineUnits = $tour->price_type == 'PER_PERSON' ? max($qty, 1) : 1;
                        $lineDiscountTotal = round($previousLineDiscount + $addedDiscount, 2);
                        $discount_price = round($lineDiscountTotal / $lineUnits, 2);
                        $price = max($actualPrice - $discount_price, 0);
                    } else {
                        $lineDiscountTotal = round(
                            $discount_price * ($tour->price_type == 'PER_PERSON' ? $qty : 1),
                            2
                        );
                    }

                    $lineGrossTotal = $tour->price_type == 'PER_PERSON'
                        ? $currencySnapshot['gross_total']
                        : ($qty > 0 ? $currencySnapshot['gross_total'] : 0);
                    $total_amount += $lineGrossTotal;
                    $nog += $qty;

                    // Skip all zero-quantity if needed
                    if ($qty <= 0) continue;

                    $label = str_ireplace('Group', 'Participants', $tourPricing->label);
                    $isDiscountablePricing = (new CheckoutDiscountService())
                        ->isDiscountablePricingLabel($tourPricing->label);
                    if (!$isDiscountablePricing) {
                        $discount_price = 0;
                        $lineDiscountTotal = 0;
                        $price = $actualPrice;
                    }

                    $pricingDetails[] = [
                        'tour_id'           => $tourId,
                        'tour_pricing_id'   => $pricingId,
                        'price_type'        => $tour->price_type,
                        'label'             => $label,
                        'actual_price'      => $actualPrice,
                        'price'             => $price,
                        // 'discount'          => $discount_price * $qty,
                        'discount'          => $discount_price,
                        'quantity'          => $qty,
                        'total_price'       => round($lineGrossTotal - $lineDiscountTotal, 2),
                        'gross_total_price' => $lineGrossTotal,
                        'newly_added_quantity' => $newlyAddedQuantity,
                        'newly_added_price' => $currencySnapshot['new_unit_price'],
                        'newly_added_rate' => (float) $tourPricing->price > 0
                            ? round($currentUnitPrice / (float) $tourPricing->price, 8)
                            : 1,
                        'is_newly_added'    => $newlyAddedQuantity > 0,
                    ];


                   $discountEntryAmount = $isDiscountablePricing && $discountsLocked
                        ? $addedDiscount
                        : ($isDiscountablePricing ? $lineDiscountTotal : 0);
                   if(
                        $specialDiscountAllowed
                        && $order->action_name == "book"
                        && $order->adv_deposite
                        && $discountEntryAmount > 0
                    ){
                    $depositRule = TourSpecialDeposit::where('use_deposit', 1)
                                    ->where('tour_id', $tour->id)
                                    ->first();
                    if(!$depositRule){
                        $depositRule = TourSpecialDeposit::where('type', 'global')->first();
                    }

                    if ($depositRule->is_discount && $depositRule->charge === 'NONE') {

                        $discount[] = [
                            'tour_id'  => $tourId,
                            'discount' => $effectiveDiscountValue,
                            'label'    => 'Special Discount',
                            'type'     => $effectiveDiscountType,
                            'price'    => round($discountEntryAmount, 2),
                        ];
                    }
                }

                    
                }


                
                // $total += $total_amount - $discount_price;
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
                    $previousQty = (int) data_get($previousExtras->get((int) $extraId), 'quantity', 0);
                    $previousExtra = $previousExtras->get((int) $extraId, []);
                    $extraAddon = Addon::findOrFail($extraId);
                    $currentUnitPrice = currencyConvertWithoutRound(
                        (float) $extraAddon->price,
                        $extraAddon->currency ?? $tour->currency,
                        $order->currency
                    );
                    $currencySnapshot = $checkoutTotals->currencySnapshotLine(
                        $previousQty,
                        $qty,
                        (float) ($previousExtra['gross_total_price']
                            ?? $previousExtra['total_price']
                            ?? ($previousQty * ($previousExtra['price'] ?? 0))),
                        (int) ($previousExtra['newly_added_quantity'] ?? 0),
                        (float) ($previousExtra['newly_added_price'] ?? $previousExtra['price'] ?? 0),
                        $currentUnitPrice,
                        $previousAdjustmentSettled
                    );
                    $newlyAddedQuantity = $currencySnapshot['new_quantity'];
                    $price = $currencySnapshot['average_unit_price'];
                    $addonAmountDelta += ($qty - $previousQty) * $price;

                    $total_amount += $currencySnapshot['gross_total'];
                    // $nog += $qty;

                    // Skip all zero-quantity if needed
                    if ($qty <= 0) continue;
                    $extraDetails[] = [
                        'tour_id'       => $tourId,
                        'label'         => $extraAddon->name,
                        'tour_extra_id' => $extraId,
                        'quantity'      => $qty,
                        'price'         => $price,
                        'total_price'   => $currencySnapshot['gross_total'],
                        'gross_total_price' => $currencySnapshot['gross_total'],
                        'newly_added_quantity' => $newlyAddedQuantity,
                        'newly_added_price' => $currencySnapshot['new_unit_price'],
                        'newly_added_rate' => (float) $extraAddon->price > 0
                            ? round($currentUnitPrice / (float) $extraAddon->price, 8)
                            : 1,
                        'is_newly_added' => $newlyAddedQuantity > 0,
                    ];

                }

                $total += $total_amount;

               
                // dd($total);


                // Update or create based on order_id + tour_id
                if ($hasCommittedPayments && $orderTour) {
                    $currentRawSubtotal = round($total - $totalBeforeTour, 2);
                    $isSingleTourOrder = $order->orderTours()->count() === 1;
                    $currentDiscountTotal = $isSingleTourOrder
                        ? round($specialDiscountTotal, 2)
                        : round(collect($discount)->sum(
                            fn ($row) => (float) ($row['price'] ?? 0)
                        ), 2);
                    if ($isSingleTourOrder && $currentDiscountTotal > 0) {
                        $discount = [[
                            'tour_id' => $tourId,
                            'discount' => $effectiveDiscountValue,
                            'label' => 'Special Discount',
                            'type' => $effectiveDiscountType,
                            'price' => $currentDiscountTotal,
                        ]];
                    }
                    $bookingFeeForTour = $totalBeforeTour <= 0.01
                        ? (float) $order->booking_fee
                        : 0.0;
                    $currentTaxableSubtotal = (new CheckoutTotalService())
                        ->discountAdjustedTaxableSubtotal(
                            $currentRawSubtotal,
                            $currentDiscountTotal,
                            $isSingleTourOrder ? $promoDiscountTotal : 0,
                            $bookingFeeForTour
                        );
                    $recalculatedTax = 0.0;
                    $updatedFees = json_decode($orderTour->tour_fees ?: '[]', true) ?: [];

                    foreach ($updatedFees as &$fee) {
                        $feeType = strtoupper(trim((string) ($fee['type'] ?? '')));
                        $feeValue = (float) ($fee['value'] ?? 0);
                        $previousTaxAmount = (float) ($fee['price'] ?? 0);
                        $previousAddedTaxAmount = (float) ($fee['newly_added_amount'] ?? 0);
                        $taxAmount = round(max(
                            (float) (get_tax(
                                $currentTaxableSubtotal,
                                $feeType,
                                $feeValue
                            ) ?? 0),
                            0
                        ), 2);

                        $fee['price'] = $taxAmount;
                        $fee['newly_added_amount'] = $checkoutTotals->nextAdjustmentTax(
                            $previousTaxAmount,
                            $taxAmount,
                            $previousAddedTaxAmount,
                            $previousAdjustmentSettled
                        );
                        $recalculatedTax += $taxAmount;
                    }
                    unset($fee);

                    $total = round(
                        $totalBeforeTour
                        + $currentRawSubtotal
                        + $bookingFeeForTour
                        + $recalculatedTax,
                        2
                    );
                    if ($specialDiscountTotal > 0) {
                        OrderPayment::updateOrCreate(
                            ['order_id' => $order->id, 'payment_type' => 'DISCOUNT'],
                            [
                                'order_id' => $order->id,
                                'payment_type' => 'DISCOUNT',
                                'transaction_id' => 'Special Discount',
                                'amount' => round($specialDiscountTotal, 2),
                                'currency' => $order->currency,
                                'status' => 'discount',
                                'collection_type' => 'Inside',
                                'collection_date' => now()->format('Y-m-d'),
                            ]
                        );
                    }
                }

                if ($orderTour) {
                    if (!$hasCommittedPayments) {
                        // Use the freshly recalculated rows. This preserves the
                        // special discount when quantities change and keeps the
                        // saved order total aligned with the updated snapshot.
                        foreach ($discount as $currentDiscountRow) {
                            $discount_price = (float) ($currentDiscountRow['price'] ?? 0);
                            $total = $total - $discount_price;
                            // dd($total, $discount_price);
                        }

                    }

                    $updateData = [
                        // 'tour_date'         => $startDate,
                        // 'tour_time'         => $startTime,
                        'tour_pricing'      => json_encode($pricingDetails),
                        'tour_extra'        => json_encode($extraDetails),
                        'tour_fees'         => is_array($updatedFees)
                            ? json_encode($updatedFees)
                            : $orderTour->tour_fees,
                        'discount'          => json_encode($discount ?? []),
                        'total_amount'      => $total,
                        'number_of_guests'  => $nog

                    ];

                    // $orderTour->update([
                    //     'tour_date'         => $startDate,
                    //     'tour_time'         => $startTime,
                    //     'tour_pricing'      => json_encode($pricingDetails),
                    //     'tour_extra'        => json_encode($extraDetails),
                    //     'total_amount'      => $total,
                    //     'number_of_guests'  => $nog

                    // ]);

                    if (!empty($startTime)) {
                        $updateData['tour_date'] = $startDate;
                        $updateData['tour_time'] = $startTime;
                    }
                    $orderTour->update($updateData);


                } else {
                    $order_tours = new OrderTour();
                    $order_tours->order_id          = $orderId;
                    $order_tours->tour_id           = $tourId;
                    $order_tours->tour_date         = $startDate;
                    $order_tours->tour_time         = $startTime;
                    $order_tours->tour_pricing      = json_encode($pricingDetails);
                    $order_tours->tour_extra        = json_encode($extraDetails);
                    $order_tours->number_of_guests  = $nog;
                    $order_tours->total_amount      = $total;
                    $order_tours->save();
                    $orderTour = $order_tours;
                }

                if($tour && !$hasCommittedPayments) {
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
                //  if($orderTour && $orderTour->discount && $discount){
                //         $orderTour->discount = json_encode($discount);
                //         $orderTour->save();
                //     }

                // if($orderTour && $orderTour->discount){


                //         $discounts = json_decode($orderTour->discount, true);
                        

                //         foreach ($discounts as &$discount) {

                //             if (!isset($discount['discount'], $discount['type'])) {
                //                 continue;
                //             }

                            
                //             if ($discount['type'] === 'PERCENT') {

                //                 $discountAmount = round(($total * $discount['discount']) / 100, 2);

                //             } elseif ($discount['type'] === 'FIXED') {

                //                 $discountAmount = round($discount['discount'], 2);

                //             } else {
                //                 $discountAmount = 0;
                //             }

                //             // Update price field (final price after discount)
                //             $discount['price'] = round($discountAmount, 2);

                //             // Reduce item total
                //             $total -= $discountAmount;
                //         }

                //         // Save updated discount JSON
                //         $orderTour->discount = json_encode($discounts);
                //         $orderTour->save();

                //         //[{"tour_id":23,"discount":"20.00","label":"Discount 20.00%","type":"PERCENT","price":158.88}]
                //     }
            }
            $orderTour->update([                
                        'total_amount'  => $total
            ]);

            $totalPaymentAmount = 0;
            if ($request->paymentType) {
                $existingPaymentIds = $order->payments
                    ->whereNotIn('payment_type', ['DISCOUNT', 'PROMOCODE', 'BOOKINGFEE'])
                    ->pluck('id')
                    ->toArray();
                
                foreach ($request->paymentType as $i => $type) {

                    $paymentId       = $request->paymentId[$i] ?? null;
                    $amount          = $request->amount[$i] ?? 0;
                    $refundAmount    = $request->refund_amount[$i] ?? 0;
                    $status          = $request->status[$i] ?? null;
                    $collection_date = $request->collection_date[$i] ?? null;
                    $transactionId   = $request->transactionId[$i] ?? null;
                    
                    // Skip empty rows
                    if (empty($type) && empty($amount) && $amount == 0) {
                        continue;
                    }

                    // Add amount to total (only if valid) 
                    if ($status === 'succeeded') {

                        $netAmount = floatval($amount) - floatval($refundAmount);

                        if ($netAmount > 0) {
                            $totalPaymentAmount += $netAmount;
                        }
                    }

                    if ($paymentId) {

                        // REMOVE the ID from existing IDs list
                        if (($key = array_search($paymentId, $existingPaymentIds)) !== false) {
                            unset($existingPaymentIds[$key]);
                        }

                        // UPDATE existing row
                        // OrderPayment::where('id', $paymentId)->update([
                        //     'payment_type'   => $type,
                        //     'transaction_id' => $request->transactionId[$i] ?? null,
                        //     'collection_date'=> $request->date[$i] ?? null,
                        //     'amount'         => $amount,
                        //     'updated_at'     => now(),
                        // ]);

                    } else {
                        // INSERT new row


                        
                        OrderPayment::create([
                            'order_id'       => $order->id,
                            'payment_type'   => $type,
                            'transaction_id' => $transactionId,
                            'collection_date'=> Carbon::parse($collection_date)->format('Y-m-d'),
                            'currency'       => $order->currency,
                            'amount'         => $amount,
                            'collection_type'=> 'Outside',
                            'status'         => 'succeeded',
                            'created_at'     => now(),
                            'updated_at'     => now(),
                        ]);

                        if($type == "COMMISSION"){

                            if($order->total_amount - $amount){
                                OrderPayment::create([
                                    'order_id'          => $order->id,
                                    'payment_intent_id' => null,
                                    'transaction_id'    => $transactionId,
                                    'payment_type'      => "EXCLUDED",
                                    'collection_type'   => 'Outside',
                                    'collection_date'   => Carbon::parse($collection_date)->format('Y-m-d'),
                                    'amount'            => $order->total_amount - $amount,
                                    'currency'          => $order->currency,
                                    'status'            => 'succeeded',
                                    'created_at'        => now(),
                                    'updated_at'        => now(),
                                ]);
                                }

                            }
                            
                    }
                }  
                
                // These are the payments that were NOT included in updated request

                
                if (!empty($existingPaymentIds)) {
                    OrderPayment::whereIn('id', $existingPaymentIds)->delete();
                }
            }
        }

        $order->load('payments');

        $totalPaymentAmount = $order->payments
        ->where('status', 'succeeded')
        ->sum(function ($payment) {
            return floatval($payment->amount) - floatval($payment->refund_amount);
        });

        // if()

        $balanceAmount = max($total - $totalPaymentAmount, 0);

        if($order->payment_status == 3){
            $balanceAmount = $balanceAmount - $order->payments->where('status', 'uncaptured')->first()?->amount;
        
        }
        // dd($total, $balanceAmount, $totalPaymentAmount, $order->balance_amount, $order->booked_amount );
        // $order->total_amount    = $total;
        // $order->balance_amount  = $balanceAmount;
        // $order->booked_amount  = $totalPaymentAmount;


        $totals = $this->calculateTotals(
            $total,
            $totalPaymentAmount,
            $request->paymentType ?? [],
            $request->amount ?? []
        );
        $accountingSummary = (new OrderPaymentSummaryService())->summarize(
            $order->payments()->get()
        );
        if (!$totals['exclude']) {
            $netOrderAmount = round(max(
                $total
                - $accountingSummary['special_discount']
                - $accountingSummary['promo_discount'],
                0
            ), 2);
            $totals['total'] = $netOrderAmount;
            $totals['paid'] = $accountingSummary['paid_amount'];
            $totals['balance'] = round(max(
                $netOrderAmount - $accountingSummary['paid_amount'],
                0
            ), 2);
        }

        $order->total_amount   = $totals['total'];
        $order->booked_amount  = $totals['paid'];
        $order->balance_amount = $totals['balance'];
        $balanceAmount = $totals['balance'];
        $balanceAmount = $totals['balance'];
        $total =         $totals['total'];
        

        if( $order->save() ) {

            // ===== Stripe Payment Handling =====
            try {
                \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

                $customer = $order->customer;

                // Check if customer already linked
                if (!$order->stripe_customer_id) {
                    $name = $customer->first_name.' '.$customer->last_name;
                    $stripeCustomer = \Stripe\Customer::create([
                        'name'  => $name,
                        'email' => $customer->email,
                        'phone' => $customer->phone,
                    ]);
                } else {
                    $name = $customer->first_name.' '.$customer->last_name;
                    $stripeCustomer = \Stripe\Customer::retrieve($order->stripe_customer_id);
                }

                $add_ccnow      = (int)$request->add_ccnow;
                $charge_ccnow   = (int)$request->charge_ccnow;
                $chargeAmount   = (float)$request->charge_ccnow_amount;

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
                    'totalAmount'   => $chargeAmount,
                ];

                //echo '<pre>'; print_r( $request->payment_intent_id ); echo '</pre>'; exit;

                if ($chargeAmount > 0) {
                    $order_actions = [];
                    if ( $charge_ccnow ) {
                        $pi = \Stripe\PaymentIntent::create([
                            'customer'  => $stripeCustomer->id,
                            'amount' => intval(round($chargeAmount * 100)),
                            'currency' => $order->currency,
                            'automatic_payment_methods' => [
                                'enabled' => true,
                                'allow_redirects' => 'never',  // 🔥 prevents Stripe from requiring return_url
                            ],
                            
                            'capture_method' => 'manual',
                            'description' => $tour->title,
                            'statement_descriptor_suffix' => $order->order_number,
                            'metadata'  => $metaData,
                            'setup_future_usage'=> 'off_session',
                        ]);
                        $order->balance_amount  = max($balanceAmount - ($chargeAmount ?? 0), 0); 
                        $order->booked_amount   = $order->booked_amount + $chargeAmount;
                        $order->payment_status  = 1; // Paid
                        $order->payment_method  = 'card';

                        // ✅ If frontend sent payment_method_id, confirm & capture immediately
                        if ($request->filled('payment_intent_id')) {
                            $pi = \Stripe\PaymentIntent::retrieve($pi->id);
                            $pi->confirm(['payment_method' => $request->payment_intent_id]);
                            $pi->capture();

                            $paymentMethod = \Stripe\PaymentMethod::retrieve($request->payment_intent_id);

                            $card = $paymentMethod->card;
                            $brand  = $card->brand;          // visa, mastercard
                            $last4  = $card->last4;          // 4242

                            OrderPayment::create([
                                'order_id'          => $order->id,
                                'payment_intent_id' => $pi->id,
                                'transaction_id'    => $pi->charges->data[0]->id ?? $pi->id,
                                'payment_method'    => 'card',
                                'payment_type'      => 'CREDITCARD',
                                'collection_type'   => 'Inside',
                                'collection_date'   => date('Y-m-d'),
                                'card_brand'        => $brand,
                                'card_last4'        => $last4,
                                'amount'            => $chargeAmount,
                                'currency'          => $order->currency,
                                'status'            => 'succeeded',
                                'action'            => 'manual_charge',
                                'response_payload'  => json_encode($pi),
                                'created_at'        => now(),
                                'updated_at'        => now(),
                            ]);

                            // ===== Save OrderActions =====
                            $order_actions = [
                                [
                                    'order_id'         => $order->id,
                                    'performed_by'     => Auth::id(),
                                    'notes'            => "System attached new credit card $last4 ($brand) to this order",
                                    'created_at'       => now(),
                                    'updated_at'       => now()
                                ],[
                                    'order_id'         => $order->id,
                                    'performed_by'     => Auth::id(),
                                    'notes'            => "System charged credit card $last4 for $chargeAmount ".$order->currency. " Reference number is ".$pi->id,
                                    'created_at'       => now(),
                                    'updated_at'       => now()
                                ]
                            ];
                        }
                    }
                    else if( $add_ccnow ) {
                        // 2️⃣ Attach Payment Method to Customer
                        $paymentMethod = \Stripe\PaymentMethod::retrieve($request->payment_intent_id)
                            ->attach(['customer' => $stripeCustomer->id]);

                        // 3️⃣ Set this payment method as default
                        \Stripe\Customer::update($stripeCustomer->id, [
                            'invoice_settings' => [
                                'default_payment_method' => $request->payment_intent_id,
                            ]
                        ]);


                        $card = $paymentMethod->card;
                        $brand  = $card->brand;          // visa, mastercard
                        $last4  = $card->last4;          // 4242

                        $order->balance_amount      = $balanceAmount; 
                        $order->booked_amount       = 0;
                        $order->payment_status      = 0; // Unpaid                        
                        $order->payment_method_id   = $request->payment_intent_id;
                        $order->payment_method      = 'card';

                        OrderPayment::create([
                            'order_id'          => $order->id,
                            'payment_intent_id' => $request->payment_intent_id,
                            'payment_type'      => 'CREDITCARD',
                            'collection_type'   => 'Inside',
                            'collection_date'   => date('Y-m-d'),
                            'amount'            => $chargeAmount,
                            'currency'          => $order->currency,
                            'status'            => 'pending',
                            'created_at'        => now(),
                            'updated_at'        => now(),
                        ]);

                        // ===== Save OrderActions =====
                        $order_actions = [
                            [
                                'order_id'         => $order->id,
                                'performed_by'     => Auth::id(),
                                'notes'            => "System attached new credit card $last4 ($brand) to this order",
                                'created_at'       => now(),
                                'updated_at'       => now()
                            ]
                        ];
                    }
                    else {
                        $order->balance_amount      = $balanceAmount; 
                        $order->booked_amount       = 0;
                        $order->payment_status      = 0; // Unpaid  
                        
                        // ===== Save OrderActions =====
                        $order_actions = [
                            [
                                'order_id'         => $order->id,
                                'performed_by'     => Auth::id(),
                                'notes'            => "Order updated for ".$tour->title." on ".$order->created_at->format('Y-m-d H:i:s'),
                                'created_at'       => now(),
                                'updated_at'       => now()
                            ]
                        ];
                    }

                    $order->save();

                    OrderActions::insert($order_actions);

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

            $order_actions = [
                'order_id'         => $order->id,
                'performed_by'     => Auth::id(),
                'notes'            => Auth::user()->name. " updated <a href='". route('admin.customers.edit', encrypt($customer->id)) ."'>".$customer->first_name ." ".$customer->last_name."'s</a> order",
                'created_at'       => now(),
                'updated_at'       => now()
            ];
            OrderActions::insert($order_actions);

            return redirect()->back()->withErrors($validator)->withInput()->with('success', 'Order has beend updated!');
        }

        return redirect()->back()->withErrors($validator)->withInput()->with('error', 'Something went wrong!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {

        $order = Order::where('id', decrypt($id))->first();
        // $tour->title .= '-deleted';
        // $tour->slug .= '-deleted-' . Str::random(6);
        // $tour->save();
        if ($order->delete()) {
            return redirect()->route('admin.orders.index')->with('success', 'Order info has been deleted successfull');
        } else {
            return back()->route('admin.orders.index')->with('error', 'Sorry! Something went wrong.');;
        }
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
        $cc_mail = $request->input('cc_mail');
        $bcc_mail= $request->input('bcc_mail');
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

                $order_actions = [
                    'order_id'         => $order_id,
                    'performed_by'     => Auth::id(),
                    'notes'            => Auth::user()->name." sent order details by email to {$email}",
                    'created_at'       => now(),
                    'updated_at'       => now()
                ];
                OrderActions::insert($order_actions);

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

            $paymentSummary = (new OrderPaymentSummaryService())->summarize($order->payments);
            $grossOrderTotal = round((float) $order->orderTours->sum('total_amount'), 2);
            $balanceAmountValue = round(max(
                $grossOrderTotal - (float) $paymentSummary['total_credits'],
                0
            ), 2);
            $rawSubTotal = 0.0;
            $adjustmentSubTotal = 0.0;
            $adjustmentTaxTotal = 0.0;
            $number_of_guests = 0;
            foreach ($order->orderTours as $summaryOrderTour) {
                foreach (json_decode($summaryOrderTour->tour_pricing ?: '[]', true) ?: [] as $pricingRow) {
                    $quantity = (int) ($pricingRow['quantity'] ?? 0);
                    $actualPrice = (float) ($pricingRow['actual_price'] ?? $pricingRow['price'] ?? 0);
                    $rawSubTotal += (float) ($pricingRow['gross_total_price']
                        ?? (($pricingRow['price_type'] ?? 'PER_PERSON') === 'FIXED'
                            ? ($quantity > 0 ? $actualPrice : 0)
                            : $quantity * $actualPrice));
                    $adjustmentSubTotal += max(
                        (int) ($pricingRow['newly_added_quantity'] ?? 0),
                        0
                    ) * max((float) ($pricingRow['newly_added_price'] ?? $actualPrice), 0);
                }
                foreach (json_decode($summaryOrderTour->tour_extra ?: '[]', true) ?: [] as $extraRow) {
                    $rawSubTotal += (float) ($extraRow['total_price']
                        ?? ((int) ($extraRow['quantity'] ?? 0) * (float) ($extraRow['price'] ?? 0)));
                    $adjustmentSubTotal += max(
                        (int) ($extraRow['newly_added_quantity'] ?? 0),
                        0
                    ) * max((float) ($extraRow['newly_added_price'] ?? $extraRow['price'] ?? 0), 0);
                }
                foreach (json_decode($summaryOrderTour->tour_fees ?: '[]', true) ?: [] as $feeRow) {
                    $adjustmentTaxTotal += max((float) ($feeRow['newly_added_amount'] ?? 0), 0);
                }
            }
            $rawSubTotal = round($rawSubTotal, 2);
            $discountAmount = (float) $paymentSummary['special_discount'];
            $promoAmount = (float) $paymentSummary['promo_discount'];
            $paidAmount = round(
                (float) $paymentSummary['paid_amount']
                    + (float) $paymentSummary['authorized_amount'],
                2
            );
            $bookingFee = (float) ($paymentSummary['booking_fee'] > 0
                ? $paymentSummary['booking_fee']
                : ($order->booking_fee ?? $order->bookingFee->value('value') ?? 0));
            $taxAmount = round(max(
                $grossOrderTotal - ($rawSubTotal - $discountAmount - $promoAmount + $bookingFee),
                0
            ), 2);
            $adjustmentSubTotal = round($adjustmentSubTotal, 2);
            $adjustmentTaxTotal = round($adjustmentTaxTotal, 2);
            $adjustmentTotal = round($adjustmentSubTotal + $adjustmentTaxTotal, 2);
            $isAdjustmentTemplate = in_array(
                $email_template->identifier,
                ['payment_request', 'payment_receipt'],
                true
            ) && $adjustmentTotal > 0.01 && (
                $paymentSummary['paid_amount'] > 0
                || $paymentSummary['authorized_amount'] > 0
                || strtolower((string) $order->action_name) === 'reserve'
            );
            if ($isAdjustmentTemplate) {
                $adjustmentPaid = (new CheckoutTotalService())->hasMatchingAdjustmentPayment(
                    $adjustmentTotal,
                    $order->payments
                );
                $rawSubTotal = $adjustmentSubTotal;
                $discountAmount = 0.0;
                $promoAmount = 0.0;
                $bookingFee = 0.0;
                $taxAmount = $adjustmentTaxTotal;
                $grossOrderTotal = $adjustmentTotal;
                $paidAmount = $adjustmentPaid ? $adjustmentTotal : 0.0;
                $balanceAmountValue = $adjustmentPaid ? 0.0 : $adjustmentTotal;
            }

            $TOUR_PAYMENT_HISTORY = '
            <table width="640" bgcolor="#ffffff" cellpadding="0" cellspacing="0" border="0" align="center" class="header_table" style="width:640px; margin-left:0px">
                <tbody>
                    <tr>
                        <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; text-align: left; padding: 30px 30px 15px; width:640px;">
                            <h3 style="font-size:19px; "><strong>Payment Summary</strong></h3>
                        </td>
                    </tr>
                </tbody>
            </table>

            <table width="640" bgcolor="#ffffff" cellpadding="0" cellspacing="0" border="0" align="left" class="table" style="border-width:0 30px 30px; border-color:#fff; border-style:solid; background-color:#fff">
                <tbody>

                    <tr>
                        <td style="font-family:\'Lato\', Helvetica, Arial, sans-serif; border-top:1pt solid #000; padding:5px 0;">
                            <small style="font-size:14px; text-transform:uppercase;">Sub Total</small>
                        </td>
                        <td style="text-align:right; border-top:1pt solid #000;">
                            <strong>' . price_format_with_currency($rawSubTotal, $order->currency) . '</strong>
                        </td>
                    </tr>';

                    if( $discountAmount > 0) {
                     $TOUR_PAYMENT_HISTORY .= '
                        <tr style="color:red;">
                        <td style="font-family:\'Lato\', Helvetica, Arial, sans-serif; border-top:1pt solid #000; padding:5px 0;">
                            <small style="font-size:14px; text-transform:uppercase;">Special Discount</small>
                        </td>
                        <td style="text-align:right; border-top:1pt solid #000;">
                            <strong>-' . price_format_with_currency($discountAmount, $order->currency) . '</strong>
                        </td>
                    </tr>';
                    }

                    if ($promoAmount > 0) {
                        $promoLabel = 'Promo Code' . ($paymentSummary['promo_code']
                            ? ' (' . e($paymentSummary['promo_code']) . ')'
                            : '');
                        $TOUR_PAYMENT_HISTORY .= '<tr style="color:red;">
                            <td style="font-family:\'Lato\', Helvetica, Arial, sans-serif; border-top:1pt solid #000; padding:5px 0;">
                                <small style="font-size:14px; text-transform:uppercase;">' . $promoLabel . '</small>
                            </td>
                            <td style="text-align:right; border-top:1pt solid #000;">
                                <strong>-' . price_format_with_currency($promoAmount, $order->currency) . '</strong>
                            </td>
                        </tr>';
                    }

                    if ($bookingFee > 0) {
                        $TOUR_PAYMENT_HISTORY .= '<tr>
                            <td style="font-family:\'Lato\', Helvetica, Arial, sans-serif; border-top:1pt solid #000; padding:5px 0;">
                                <small style="font-size:14px; text-transform:uppercase;">Booking Fee</small>
                            </td>
                            <td style="text-align:right; border-top:1pt solid #000;">
                                <strong>' . price_format_with_currency($bookingFee, $order->currency) . '</strong>
                            </td>
                        </tr>';
                    }

                    $TOUR_PAYMENT_HISTORY .= '<tr>
                            <td style="font-family:\'Lato\', Helvetica, Arial, sans-serif; border-top:1pt solid #000; padding:5px 0;">
                                <small style="font-size:14px; text-transform:uppercase;">HST / Tax</small>
                            </td>
                            <td style="text-align:right; border-top:1pt solid #000;">
                                <strong>' . price_format_with_currency($taxAmount, $order->currency) . '</strong>
                            </td>
                        </tr>';

                   $TOUR_PAYMENT_HISTORY .= '
                    <tr>
                        <td style="font-family:\'Lato\', Helvetica, Arial, sans-serif; border-top:2pt solid #000; padding:5px 0;">
                            <small style="font-size:14px; text-transform:uppercase;">Total</small>
                        </td>
                        <td style="text-align:right; border-top:2pt solid #000;">
                            <h3 style="margin:0; font-size:19px;">
                                <strong>' . price_format_with_currency($grossOrderTotal, $order->currency) . '</strong>
                            </h3>
                        </td>
                    </tr>
                    <tr style="color:green;">
                        <td style="font-family:\'Lato\', Helvetica, Arial, sans-serif; border-top:1pt solid #000; padding:5px 0;">
                            <small style="font-size:14px; text-transform:uppercase;">Total Paid</small>
                        </td>
                        <td style="text-align:right; border-top:1pt solid #000;">
                            <strong>' . price_format_with_currency($paidAmount, $order->currency) . '</strong>
                        </td>
                    </tr>
                    <tr style="color:' . ($balanceAmountValue > 0.01 ? 'red' : 'green') . ';">
                        <td style="font-family:\'Lato\', Helvetica, Arial, sans-serif; border-top:1pt solid #000; padding:5px 0;">
                            <small style="font-size:14px; text-transform:uppercase;">Balance</small>
                        </td>
                        <td style="text-align:right; border-top:1pt solid #000;">
                            <strong>' . price_format_with_currency($balanceAmountValue, $order->currency) . '</strong>
                        </td>
                    </tr>

                </tbody>
            </table>';



            $TOUR_ITEM_SUMMARY = '';
            $paymentDetailsAddedToItemSummary = false;
 
            foreach ($order->orderTours as $order_tour) {
                $subtotal = 0;
                $subtotal2 = 0;
                $adjustmentRows = '';
                $_tourId = $order_tour->tour_id;
                $tour_pricing = !empty($order_tour->tour_pricing) ? json_decode($order_tour->tour_pricing, true) : [];
                $tour_extra = !empty($order_tour->tour_extra) ? json_decode($order_tour->tour_extra, true) : [];
                $tour_discount = !empty($order_tour->discount) ? json_decode($order_tour->discount, true) : [];
                $tour_fees = !empty($order_tour->tour_fees) ? json_decode($order_tour->tour_fees, true) : [];
                if ($isAdjustmentTemplate) {
                    $tour_pricing = collect($tour_pricing)
                        ->filter(fn ($row) => (int) ($row['newly_added_quantity'] ?? 0) > 0)
                        ->map(function ($row) {
                            $row['quantity'] = (int) $row['newly_added_quantity'];
                            $row['actual_price'] = (float) ($row['newly_added_price'] ?? $row['actual_price'] ?? $row['price'] ?? 0);
                            $row['price'] = $row['actual_price'];
                            $row['discount'] = 0;
                            $row['gross_total_price'] = $row['quantity'] * $row['actual_price'];
                            $row['total_price'] = $row['gross_total_price'];
                            return $row;
                        })->values()->all();
                    $tour_extra = collect($tour_extra)
                        ->filter(fn ($row) => (int) ($row['newly_added_quantity'] ?? 0) > 0)
                        ->map(function ($row) {
                            $row['quantity'] = (int) $row['newly_added_quantity'];
                            $row['price'] = (float) ($row['newly_added_price'] ?? $row['price'] ?? 0);
                            $row['gross_total_price'] = $row['quantity'] * $row['price'];
                            $row['total_price'] = $row['gross_total_price'];
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
                // $totalFixedDiscount = 0;
                // if($tour_discount){
                //     foreach ($tour_discount as $discountFixed) {
                //        $totalFixedDiscount = $discountFixed->price;
                //     }
                // }

                // dd($order_tour);

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
                        ?? ($result['price_type'] == "FIXED" ? $actual_price : $actual_price * $qty));
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
                        $subtotal2 += $total;
                        $TOUR_ITEM_SUMMARY .= '
                        <tr>
                            <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; border-top:1pt solid #ddd; text-align: left;padding: 5px 0px;">' . $qty . '</td>
                            <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; border-top:1pt solid #ddd; text-align: left;padding: 5px 0px;">' . $extra['label'] . ' </td>
                            <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; border-top:1pt solid #ddd; text-align: left;padding: 5px 0px;">' . price_format_with_currency($price, $order->currency) . '</td>
                            <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; border-top:1pt solid #ddd; text-align: right;padding: 5px 0px;">' . price_format_with_currency($total, $order->currency) . '</td>
                        </tr>';
                    }
                }

                foreach ($tour_pricing as $result) {
                    $qty = $result['quantity'] ?? 0;
                    $number_of_guests += $qty;
                }

                $tourDiscountAmount = !$paymentDetailsAddedToItemSummary ? $discountAmount : 0;
                $tourPromoAmount = !$paymentDetailsAddedToItemSummary ? $promoAmount : 0;
                $promoLabel = 'Promo Code' . ($paymentSummary['promo_code']
                    ? ' (' . e($paymentSummary['promo_code']) . ')'
                    : '');

                foreach ([
                    ['label' => 'Special Discount', 'amount' => $tourDiscountAmount],
                    ['label' => $promoLabel, 'amount' => $tourPromoAmount],
                ] as $adjustment) {
                    if ($adjustment['amount'] > 0) {
                        $subTotalRequired = 1;
                        $adjustmentRows .= '
                        <tr>
                            <td></td><td></td>
                            <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; border-top:1pt solid #ddd; text-align:left; padding:5px 0; color:#f64747;">' . $adjustment['label'] . '</td>
                            <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; border-top:1pt solid #ddd; text-align:right; padding:5px 0; color:#f64747;">-' . price_format_with_currency($adjustment['amount'], $order->currency) . '</td>
                        </tr>';
                    }
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
                                    ' . price_format_with_currency($subtotal2, $order->currency) . '
                            </td>
                        </tr>' . $adjustmentRows;
                }

                $subtotal2 = max($subtotal2 - $tourDiscountAmount - $tourPromoAmount, 0);

                // Taxes
                $taxRows = '';


                if (!empty($tour_fees)) {
                    foreach ($tour_fees as $tax) {
                        $taxAmount = (float) ($tax['price'] ?? 0);
                        $subtotal += $taxAmount;
                        $subtotal2 += $taxAmount;
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

                // $subTotalRequired = 1;
                // if($subTotalRequired){
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
                // }

                // Total Row
                $TOUR_ITEM_SUMMARY .= $taxRows . '

                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; border-top:2pt solid #000; text-align: left;padding: 5px 0px;">
                            <h3 style="color:#000; margin:0; font-size:15px"><strong>Total</strong></h3>
                        </td>
                        <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; border-top:2pt solid #000; text-align: right;padding: 5px 0px;">
                            <h3 style="color:#000; margin:0; font-size:15px"><strong>' . price_format_with_currency($subtotal2, $order->currency) . '</strong></h3>
                        </td>
                    </tr>';

                
                
                // $paid = $order->total_amount - $order->balance_amount;

                $paid = $paidAmount;
                if (!$paymentDetailsAddedToItemSummary && $paid > 0) {
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
                $balance_amount = $balanceAmountValue;
                if (!$paymentDetailsAddedToItemSummary) {
                    $balanceColor = $balance_amount > 0.01 ? 'red' : 'green';
                    // balance amount
                    $TOUR_ITEM_SUMMARY .= '
                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; border-top:2pt solid #000; text-align: left;padding: 5px 0px;">
                            <h3 style="color:' . $balanceColor . '; margin:0; font-size:15px"><strong>Balance</strong></h3>
                        </td>
                        <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; border-top:2pt solid #000; text-align: right;padding: 5px 0px;">
                            <h3 style="color:' . $balanceColor . '; margin:0; font-size:15px"><strong>' . price_format_with_currency($balance_amount, $order->currency)  . '</strong></h3>
                        </td>
                    </tr>'; 
                }  
                $paymentDetailsAddedToItemSummary = true;
                
                $TOUR_ITEM_SUMMARY .=  '</tbody>
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
            

            if($order->tour && $order->sub_tour_id){
                $to_address = $order->tour->location->destination ?? '';
                $to_address.= $order->tour->location->address ? ' ('.$order->tour->location->address.')' : '';

                $tourLocationAddress = $order->tour->location->address;
            }else{
                
                $to_address = $tour->location->destination ?? '';
                $to_address.= $tour->location->address ? ' ('.$tour->location->address.')' : '';
                $tourLocationAddress = $tour->location->address;

            }
            
            $order_paid = $order->total_amount - $balance_amount;

            $token = encrypt($order->id);

            $timestamp = round(microtime(true) * 1000);
            $checkoutUrl = "https://tourbeez.com/checkout/{$timestamp}?token={$token}";
            if ($email_template->identifier === 'payment_request') {
                $checkoutUrl .= '&payment_request=1';
            }

            $replacements = [
                "[[CUSTOMER_NAME]]"         => $customer->name ?? '',
                "[[CUSTOMER_EMAIL]]"        => $customer->email ?? '',
                "[[CUSTOMER_PHONE]]"        => $customer->phone ?? '',



                "[[TOUR_TITLE]]"            => $tourTitleFormatted,
                "[[PARENT_TOUR_TITLE]]"     => ($order->sub_tour_id) ? $order->tour->title : '',
                "[[TOUR_SKU]]"              => $tour->unique_code ?? '',
                "[[TOUR_MAP_FORMATTED]]"    => $tourLocationAddress ? str_replace(',', ',<br>', $tourLocationAddress) : '',
                "[[TOUR_MAP]]"              => $pickup_address,
                "[[TOUR_ADDRESS]]"          => $tourLocationAddress ?? '',
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
                "[[ORDER_STATUS]]"          => str_contains($order->status, 'Pending') ? "Pending" :  $order->status,
                "[[ORDER_TOUR_DATE]]"       => date('l, F j, Y', strtotime($orderTour->tour_date)),
                "[[ORDER_TOUR_TIME]]"       => $orderTour->tour_time,
                "[[ORDER_TOTAL]]"           => price_format_with_currency($grossOrderTotal, $order->currency),
                // "[[ORDER_BALANCE]]"         => ($order->payment_status === 3) ? price_format_with_currency($order->balance_amount + $order->payments->where('status', 'uncaptured')->sum('amount'), $order->currency) : price_format_with_currency($order->balance_amount, $order->currency),
                "[[ORDER_BALANCE]]"         => price_format_with_currency($balance_amount, $order->currency),
                // "[[ORDER_BALANCE_COLOR]]"   => (abs($order->payment_status === 3? $order->balance_amount + $order->payments->where('status', 'uncaptured')->sum('amount'): $order->balance_amount) < 0.01) ? '008000' : 'f64747',
                "[[ORDER_BALANCE_COLOR]]"   => ($balance_amount < 0.01) ? '008000' : 'f64747',
                "[[ORDER_BOOKING_FEE]]"     => price_format_with_currency($bookingFee, $order->currency),
                "[[ORDER_CREATED_DATE]]"    => date('M d, Y', strtotime($order->created_at)) ?? '',
                "[[YEAR]]"                  => date('Y'),
                "[[ORDER_LINK]]"            => $checkoutUrl,
                "[[NUMBER_OF_GUESTS]]"      => $number_of_guests,
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
                        'location' => $tourLocationAddress,
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

    public function order_confirmation_message(Request $request) 
    {
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


            if($order->tour && $order->sub_tour_id){
                $to_address = $order->tour->location->destination ?? '';
                $to_address.= $order->tour->location->address ? ' ('.$order->tour->location->address.')' : '';

                $tourLocationAddress = $order->tour->location->address;
            }else{
                
                $to_address = $tour->location->destination ?? '';
                $to_address.= $tour->location->address ? ' ('.$tour->location->address.')' : '';
                $tourLocationAddress = $tour->location->address;

            }


            $replacements = [
                "[[CUSTOMER_NAME]]"         => $customer->name ?? '',
                "[[CUSTOMER_EMAIL]]"        => $customer->email ?? '',
                "[[CUSTOMER_PHONE]]"        => $customer->name ?? '',
                "[[CUSTOMER_FIRST_NAME]]"   => $customer->first_name ?? '',
                "[[CUSTOMER_LAST_NAME]]"   => $customer->last_name ?? '',

                "[[TOUR_TITLE]]"            => $tour->title ?? '',
                "[[TOUR_SKU]]"              => $tour->unique_code ?? '',
                "[[TOUR_MAP]]"              => $tourLocationAddress ?? '',
                "[[TOUR_ADDRESS]]"          => $tourLocationAddress ?? '',
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
                "[[ORDER_BALANCE_COLOR]]"   => $order->balance_amount > 0.01 ? 'f64747' : '008000',
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
        $order = Order::findOrFail($id);
        $order->order_status = $request->status;

        $order_actions = [
            [
                'order_id'         => $order->id,
                'performed_by'     => Auth::id(),
                'notes'            => Auth::user()->name . " Updated the status to $request->status in this order",
                'created_at'       => now(),
                'updated_at'       => now()
            ]
        ];
        OrderActions::insert($order_actions);
        
        if($request->status == 'Confirmed234' ){

            $order->payment_status == 1;
            $order->save();

            // dd($order->order_status, $request->status, $order->payment_status);
            if($order->payment_status == 3) {

                $confirmPayment = self::confirmPayment($order->id, $order->adv_deposite, $order->booked_amount);

                $confirmPayment = $confirmPayment->getData();
                
                if($confirmPayment->status === 'succeeded'){
                    
                    $order->payment_status == 1;
                    $order->save();
                    $order_actions = [
                        [
                            'order_id'         => $order->id,
                            'performed_by'     => Auth::id(),
                            'notes'            => "Payment $order->booked_amount is captured",
                            'created_at'       => now(),
                            'updated_at'       => now()
                        ]
                    ];
                    OrderActions::insert($order_actions);

                    return response()->json(['success' => true, 'message' => 'Order confirmed']);

                } else{
                    return response()->json(['success' => false, 'message' => $confirmPayment->message]);
                }
            } 
            elseif($order->payment_status == 5) {
                return response()->json(['success' => true, 'message' => 'Please enter payment details before confirming the order']);
            }
            elseif($order->payment_status == 7) {
                
                return response()->json(['success' => true, 'message' => 'Payment is already cancelled']);
            }else {

                $order->save();
                return response()->json(['success' => true, 'message' => 'Order status updated']);
            }

            return response()->json(['success' => false, 'message' => 'Order is not confirmed Yet']);
            
            
        }
        else if ($request->status == 'Cancelled234324') {

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

                    $order_actions = [
                        [
                            'order_id'         => $order->id,
                            'performed_by'     => Auth::id(),
                            'notes'            => "Uncaptured Payment is Cancelled",
                            'created_at'       => now(),
                            'updated_at'       => now()
                        ]
                    ];
                    OrderActions::insert($order_actions);

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
        return response()->json(['success' => true, 'message' => 'Order status updated']);
    }

    public function manifest(Request $request)
    {

        $date = $request->input('date') ?? \Carbon\Carbon::today()->toDateString();

        $pricingLabels = \App\Models\TourPricing::pluck('label', 'id')->toArray();

        $orders = \App\Models\Order::with(['customer', 'orderTours.tour', 'payments'])
            ->where('order_status', 5)
            ->get();

        $sessions = [];

        foreach ($orders as $order) {

            foreach ($order->orderTours as $ot) {

                // ✅ filter by date
                if ($ot->tour_date != $date) {
                    continue;
                }

                $slotTime = trim($ot->tour_time);
                $tourTitle = $ot->tour?->title ?? 'N/A';

                if (!$slotTime) {
                    continue;
                }

                // ✅ KEY = time + tour (IMPORTANT)
                $key = $slotTime . '||' . $tourTitle;

                if (!isset($sessions[$key])) {
                    $sessions[$key] = [
                        'slot_time' => $slotTime,
                        'tour_title' => $tourTitle,
                        'orders' => collect(),
                        'total_guests' => 0,
                    ];
                }

                $guests = collect();
                $extras = collect();
                $guestCount = 0;

                // ✅ guests from THIS orderTour
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

                // ✅ extras
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

                // attach summaries (per order)
                $order->guest_summary = $guests->isNotEmpty() ? $guests->implode(', ') : '-';
                $order->extras_summary = $extras->isNotEmpty() ? $extras->implode(', ') : '-';

                // ✅ avoid duplicate order
                if (!$sessions[$key]['orders']->contains('id', $order->id)) {
                    $sessions[$key]['orders']->push($order);
                }

                // ✅ correct guest count per session
                $sessions[$key]['total_guests'] += $guestCount;
            }
        }

        // ✅ SORT by time
        uasort($sessions, function ($a, $b) {
            return strtotime($a['slot_time']) <=> strtotime($b['slot_time']);
        });

        return view('admin.order.manifest', [
            'sessions' => collect($sessions)->values(), // important for blade
            'date' => $date
        ]);
    }

    public function downloadManifest(Request $request)
    {
        $date = $request->input('date') ?? \Carbon\Carbon::today()->toDateString();

        $pricingLabels = \App\Models\TourPricing::pluck('label', 'id')->toArray();

        $orders = \App\Models\Order::with(['customer', 'orderTours.tour'])
            ->where('order_status', 5)
            ->get();

        $sessions = [];

        foreach ($orders as $order) {

            foreach ($order->orderTours as $ot) {

                // ✅ filter by date
                if ($ot->tour_date != $date) {
                    continue;
                }

                $slotTime = trim($ot->tour_time);
                $tourTitle = $ot->tour?->title ?? 'N/A';

                if (!$slotTime) {
                    continue;
                }

                // ✅ KEY = time + tour (IMPORTANT FIX)
                $key = $slotTime . '||' . $tourTitle;

                if (!isset($sessions[$key])) {
                    $sessions[$key] = [
                        'slot_time' => $slotTime,
                        'tour_title' => $tourTitle,
                        'orders' => collect(),
                    ];
                }

                $guests = collect();
                $extras = collect();
                $guestCount = 0;

                // Guests
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

                // Extras
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

                // attach summaries
                $order->guest_summary = $guests->isNotEmpty() ? $guests->implode(', ') : '-';
                $order->extras_summary = $extras->isNotEmpty() ? $extras->implode(', ') : '-';
                $order->paid_amount = $order->total_amount - ($order->balance_amount ?? 0);
                $order->guest_count = $guestCount;

                // avoid duplicate order
                if (!$sessions[$key]['orders']->contains('id', $order->id)) {
                    $sessions[$key]['orders']->push($order);
                }
            }
        }

        // ✅ SORT by time
        uasort($sessions, function ($a, $b) {
            return strtotime($a['slot_time']) <=> strtotime($b['slot_time']);
        });

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\ManifestExport(collect($sessions)->values(), $date),
            "Manifest_{$date}.xlsx"
        );
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

            if(str_contains( $order->payment_method_id, 'pm_')){
                $paymentMethodId = $order->payment_method_id;

            }elseif(str_contains( $order->payment_intent_id, 'pm_')){

                $paymentMethodId   = $order->payment_intent_id;

            } else {

                if (!$customerId || !$intentId) {
                    throw new \Exception("Stripe customer or PaymentIntent not found.");
                }

                // Retrieve previous PaymentIntent

                $paymentIntent = \Stripe\PaymentIntent::retrieve($intentId);

               
                $paymentMethodId = $paymentIntent->payment_method;
            }          

            
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

            $tour = Tour::find($order->orderTours()->first()->tour_id);
            if (!$tour) {
                return response()->json([
                    'status' => false,
                    'message' => 'Tour not found.'
                ], 404);
            }

            $customer = User::find($order->user_id);
            if (!$customer) {

                $customer = $order->customer;
                if(!$customer){
                    return response()->json([
                        'status' => false,
                        'message' => 'Customer not found.'
                    ], 404);
                }
                    
            }

            // $chargeAmount = 6883.1740398;
            $chargeAmount = round($chargeAmount, 2);
            $stripeAmount = (int) round($chargeAmount * 100);

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
                'totalAmount'   => $chargeAmount
            ];

            // Create a new PaymentIntent for off-session charge
            $newIntent = \Stripe\PaymentIntent::create([
                'customer'             => $customerId,
                'amount'               => $stripeAmount,
                'currency'             => $order->currency ?? 'eur',
                'payment_method'       => $paymentMethodId,
                // 'payment_method_types' => ['card', 'link'], // card and link allowed
                'automatic_payment_methods' => ['enabled' => true],
                'off_session'          => true,
                'confirm'              => true,
                'description'          => "#{$order->order_number} - {$tour->title}",
                'metadata'             => $metaData,
                'statement_descriptor_suffix' =>  $order->order_number,
            ]);

            // Save card info
            $card = $paymentMethod->card;
            $last4 = $card->last4 ?? null;
            $brand = $card->brand ?? null;


            $order->booked_amount += $chargeAmount;

            $balanceAmount = max($order->total_amount - $order->booked_amount, 0);

            if($order->payment_status == 3 && $balanceAmount != 0){
                $balanceAmount = $balanceAmount - $order->payments->where('status', 'uncaptured')->first()?->amount;
            
            }
            $order->balance_amount = round($balanceAmount, 2);
            $order->save();

            // Save payment record
            OrderPayment::create([
                'order_id'          => $order->id,
                'payment_intent_id' => $newIntent->id,
                'transaction_id'    => $newIntent->charges->data[0]->id ?? $newIntent->id,
                'payment_method'    => 'card',
                'payment_type'      => 'CREDITCARD',
                'collection_type'   => 'Inside',
                'collection_date'   => now(),
                'card_brand'        => $brand,
                'card_last4'        => $last4,
                'amount'            => $chargeAmount,
                'currency'          => $order->currency,
                'status'            => 'succeeded',
                'action'            => 'manual_charge',
                'response_payload'  => json_encode($newIntent),
            ]);

            $order_actions = [
                'order_id'         => $order->id,
                'performed_by'     => Auth::id(),
                'notes'            => Auth::user()->name ." charged {$order->currency} {$chargeAmount} on credit card XXXXXXXXXXXX{$last4}. Reference number is {$newIntent->id}",
                'created_at'       => now(),
                'updated_at'       => now()
            ];
            OrderActions::insert($order_actions);

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
             $payment->update([
                'status' => 'refunded',
            ]);

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

            // Save payment record
            OrderPayment::create([
                'order_id'          => $order->id,
                'payment_intent_id' => $refund->id,
                'transaction_id'    => $refund->id,
                'payment_method'    => 'card',
                'payment_type'      => 'REFUND',
                'collection_type'   => 'Inside',
                'collection_date'   => now(),
                'amount'            => $request->amount,
                'currency'          => $order->currency,
                'status'            => 'refunded',
                'action'            => 'manual_charge',
                'response_payload'  => json_encode($refund),
            ]);

            $order_actions = [
                'order_id'         => $order->id,
                'performed_by'     => Auth::id(),
                'notes'            => "Refund of payment (STRIPE: {$refund->id}) has been processed by ".Auth::user()->name.". Refund amount is : {$order->currency} {$request->amount} ",
                'created_at'       => now(),
                'updated_at'       => now()
            ];
            OrderActions::insert($order_actions);

            $this->reconcilePaymentLedger($order);

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

    public function tourManifestmain(Request $request)
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
                ->where('order_status', 5)

                ->whereHas('orderTours', function ($q) use ($date, $slot) {
                    $q->whereDate('tour_date', $date)
                      ->whereTime('tour_date', '>=', $slot['start'])
                      ->whereTime('tour_date', '<', $slot['end']);
                })
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

    public function tourManifest(Request $request)
    {
        $date = $request->input('date') ?? Carbon::today()->toDateString();

        // Preload pricing labels indexed by ID
        $pricingLabels = TourPricing::pluck('label', 'id')->toArray();

        $sessions = [];

        $orders = Order::with(['customer', 'orderTours.tour'])
            ->where('order_status', 5)
            ->whereHas('orderTours', function ($q) use ($date) {
                $q->whereDate('tour_date', $date);
            })
            ->get();

        foreach ($orders as $order) {

            foreach ($order->orderTours as $ot) {

                if ($ot->tour_date != $date) {
                    continue;
                }

                $tourTitle = $ot->tour->title ?? 'Unknown Tour';
                $slotTime  = $ot->tour_time ?? 'Unknown Time';

                $key = "{$slotTime} || {$tourTitle}";

                // Parse guest pricing
                $guests = collect();
                $extras = collect();

                $pricingItems = json_decode($ot->tour_pricing, true);


                $guestCount = 0;
                if (is_array($pricingItems)) {
                    foreach ($pricingItems as $p) {

                        $qty = $p['quantity'] ?? 0;
                        $pricingId = $p['tour_pricing_id'] ?? null;

                        $label = $pricingLabels[$pricingId] ?? ($p['label'] ?? null);
                        $guestCount += (int) ($p['quantity'] ?? 0);
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

                $order->guest_summary  = $guests->isNotEmpty() ? $guests->implode(', ') : '-';
                $order->extras_summary = $extras->isNotEmpty() ? $extras->implode(', ') : '-';
                $order->paid_amount    = $order->total_amount - ($order->balance_amount ?? 0);

                // Initialize session
                if (!isset($sessions[$key])) {
                    $sessions[$key] = [
                        'slot_time' => $slotTime,
                        'tour_title' => $tourTitle,
                        'orders' => [],
                        'total_guests' => 0
                    ];
                }

                // Add order
                $sessions[$key]['orders'][] = $order;

                // Add guest count
                $sessions[$key]['total_guests'] += $guestCount;
            }
        }

        // Sort sessions by time
        uasort($sessions, function ($a, $b) {

            try {
                $timeA = \Carbon\Carbon::createFromFormat('g:i A', trim($a['slot_time']));
                $timeB = \Carbon\Carbon::createFromFormat('g:i A', trim($b['slot_time']));
            } catch (\Exception $e) {
                return 0; // fallback, avoid crash
            }

            return $timeA->timestamp <=> $timeB->timestamp;
        });

        // dd($sessions);

        return view('admin.order.tour-manifest', compact('sessions', 'date'));
    }

    public function downloadTourManifestMain(Request $request)
    {
        $date = $request->input('date') ?? Carbon::today()->toDateString();

        // Preload pricing labels
        $pricingLabels = TourPricing::pluck('label', 'id')->toArray();

        $sessions = [];

        $orders = Order::with(['customer', 'orderTours.tour'])
            ->where('order_status', 5)
            ->whereHas('orderTours', function ($q) use ($date) {
                $q->whereDate('tour_date', $date);
            })
            ->get();

        foreach ($orders as $order) {

            foreach ($order->orderTours as $ot) {

                if ($ot->tour_date != $date) {
                    continue;
                }

                $tourTitle = optional($ot->tour)->title ?? 'Unknown Tour';
                $slotTime  = $ot->tour_time ?? 'Unknown Time';

                $key = "{$slotTime} || {$tourTitle}";

                // Parse guest pricing
                $guests = collect();
                $extras = collect();

                $pricingItems = json_decode($ot->tour_pricing, true);

                if (is_array($pricingItems)) {
                    foreach ($pricingItems as $p) {

                        $qty = (int) ($p['quantity'] ?? 0);
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

                        $qty = (int) ($e['quantity'] ?? 0);
                        $label = $e['label'] ?? null;

                        if ($qty && $label) {
                            $extras->push("{$qty} {$label}");
                        }
                    }
                }

                $order->guest_summary  = $guests->isNotEmpty() ? $guests->implode(', ') : '-';
                $order->extras_summary = $extras->isNotEmpty() ? $extras->implode(', ') : '-';
                $order->paid_amount    = $order->total_amount - ($order->balance_amount ?? 0);

                // Initialize session
                if (!isset($sessions[$key])) {
                    $sessions[$key] = [
                        'title' => $tourTitle,
                        'slot_time' => $slotTime,
                        'orders' => []
                    ];
                }

                // Prevent duplicate order
                $sessions[$key]['orders'][$order->id] = $order;
            }
        }

        // Reindex orders
        foreach ($sessions as &$session) {
            $session['orders'] = array_values($session['orders']);
        }
        unset($session);

        // Sort by time
        uasort($sessions, function ($a, $b) {

            try {
                $timeA = \Carbon\Carbon::createFromFormat('g:i A', trim($a['slot_time']));
                $timeB = \Carbon\Carbon::createFromFormat('g:i A', trim($b['slot_time']));
            } catch (\Exception $e) {
                return 0; // fallback, avoid crash
            }

            return $timeA->timestamp <=> $timeB->timestamp;
        });

        return Excel::download(
            new ManifestExport($sessions, $date, 'tour'),
            "Manifest_{$date}.xlsx"
        );
    }

    public function downloadTourManifest(Request $request)
    {
        $date = $request->input('date') ?? \Carbon\Carbon::today()->toDateString();

        $rows = [];

        $orders = Order::with(['customer', 'orderTours.tour.categories'])
            ->where('order_status', 5)
            ->get();

        foreach ($orders as $order) {

            $customer = $order->customer;

            if (!$customer) continue;

            foreach ($order->orderTours as $ot) {

                if ($ot->tour_date != $date) continue;

                $serviceType = optional($ot->tour->category)->name ?? 'Tour';

                $serviceType = $ot->tour && $ot->tour->categories->isNotEmpty()
                                ? $ot->tour->categories->pluck('name')->implode(', ')
                                : 'Tour';
                
                $pickupDate = \Carbon\Carbon::parse($ot->tour_date)->format('m/d/Y');
                $pickupTime = $ot->tour_time;

                $breakdown = $this->getPassengerBreakdown($ot);

                $passengerCount = $breakdown['total'];
                $infantCount    = $breakdown['infants'];
                $childCount     = $breakdown['children'];

                $rows[] = [
                    // passenger
                    $customer->first_name,
                    $customer->last_name,
                    $customer->phone,
                    $customer->email,

                    // booked by
                    $customer->first_name,
                    $customer->last_name,

                    // pickup
                    $pickupDate,
                    $pickupTime,
                    $customer->pickup_name ?? '',
                    '', '', '', '',

                    optional($ot->tour)->title,

                    // dropoff
                    $pickupDate,
                    '5:30 PM',
                    '',
                    '',
                    $customer->pickup_name ?? '',
                    '', '', '', '', '',

                    // service
                    $serviceType,
                    '',
                    'Confirmed',

                    // payment
                    "TourBeez",

                    // airline (empty)
                    '', '', '', '',
                    '', '', '', '',

                    // counts
                    $passengerCount - $infantCount - $childCount,
                    0, // luggage
                    $infantCount,
                    $childCount,
                    0, // booster

                    // notes
                    '',
                    $customer->instructions ?? '',
                    '',
                    '',
                    '',

                    // billing
                    '',
                    '',
                    '',
                    '',

                    // reference
                    $order->order_number,

                    // amount
                    $order->total_amount
                ];
            }
        }
        // dd($rows);
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\ManifestExport($rows, $date, 'tour'),
            "Manifest_{$date}.xlsx"
        );
    }

    private function getPassengerBreakdown($ot)
    {
        $adults = 0;
        $children = 0;
        $infants = 0;

        $pricingItems = json_decode($ot->tour_pricing, true);

        if (is_array($pricingItems)) {
            foreach ($pricingItems as $p) {

                $label = strtolower($p['label'] ?? '');
                $qty   = (int) ($p['quantity'] ?? 0);

                if (str_contains($label, 'adult')) {
                    $adults += $qty;
                } elseif (str_contains($label, 'child')) {
                    $children += $qty;
                } elseif (str_contains($label, 'infant')) {
                    $infants += $qty;
                }
            }
        }

        return [
            'adults' => $adults,
            'children' => $children,
            'infants' => $infants,
            'total' => $adults + $children + $infants
        ];
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
        
        try {
            \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

            $tour       = $order->tour;
            $customer   = $order->customer;

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
                'totalAmount'   => $order->balance_amount
            ];

            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount'            => (int) ($order->balance_amount * 100),
                'currency'          => strtoupper($order->currency ?? 'usd'),
                'customer'          => $order->stripe_customer_id,
                'payment_method'    => $request->payment_method_id,
                'off_session'       => true,
                'confirm'           => true,
                'description'       => '(#' . $order->order_number . ') ' .$tour->title,
                'metadata'          => $metaData,
                'statement_descriptor_suffix' =>  $order->order_number,
            ]);

            // Store in order_payments table
            $order->payments()->create([
                'order_id'          => $order->id,
                'payment_intent_id' => $paymentIntent->id,
                'transaction_id'    => NULL,
                'payment_method'    => 'stripe',
                'amount'            => (float) ($request->amount),
                'currency'          => strtoupper($order->currency ?? 'usd'),
                'status'            => $paymentIntent->status,
                'action'            => 'deposit',
                'response_payload'  => json_encode($paymentIntent),
                'card_last4'        => $request->card_last4,
                'card_brand'        => $request->card_brand,
                'card_exp_month'    => $request->card_exp_month,
                'card_exp_year'     => $request->card_exp_year,
            ]);

            // Update main order summary
            $order->booked_amount += $request->amount;
            $order->balance_amount = max($order->total_amount - $order->balance_amount, 0);
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

    public function captureInitialPayment(Request $request, $orderId)
     {

        $order = Order::findOrFail($orderId);

        $uncaptureAmount = $request->amount;

        $confirmPayment = self::confirmPayment($order->id, $order->adv_deposite, $uncaptureAmount);
        $confirmPayment = $confirmPayment->getData();
          
        if($confirmPayment->status === 'succeeded'){
            
            $order->payment_status = 1;
            $order->save();
            $order_actions = [
                [
                    'order_id'         => $order->id,
                    'performed_by'     => Auth::id(),
                    'notes'            => "Capture of payment has been processed by ".Auth::user()->name.". Capture amount is : {$order->currency} {$uncaptureAmount} ",
                    'created_at'       => now(),
                    'updated_at'       => now()
                ]
            ];
            OrderActions::insert($order_actions);

            return response()->json(['success' => true, 'message' => 'Payment is captured']);

        } else if($confirmPayment->status === 'already_capture'){
            $order->payment_status = 1;
            $order->save();
            
        }
        return response()->json(['success' => false, 'message' => $confirmPayment->message]);
    }

    public function cancelInitialPayment($orderId)
    {
        $order = Order::findOrFail($orderId);

        $cancel = self::cancelUncapturedAmount($order->id);

        $response = $cancel->getData();

        if ($response->success) {

            // Update to payment_status = 0 (payment cancelled)
            $order->payment_status = 7;
            $order->booked_amount = 0;
            $order->save();

            $order_actions = [
                [
                    'order_id'         => $order->id,
                    'performed_by'     => Auth::id(),
                    'notes'            => "Uncaptured Payment is Cancelled",
                    'created_at'       => now(),
                    'updated_at'       => now()
                ]
            ];
            OrderActions::insert($order_actions);

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

    public function confirmPayment($orderId, $action_name, $amount)
    {
        DB::beginTransaction();

        try {
            $order = Order::findOrFail($orderId);

            \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

            $intentId = $order->payment_intent_id;
            $customerId = $order->stripe_customer_id;

            if (!$intentId) {
                throw new \Exception("No payment intent found for this order.");
            }

            // Retrieve payment intent
            $paymentIntent = \Stripe\PaymentIntent::retrieve([
                'id' => $intentId
            ]);

            // ----------------------------------------------------
            // CASE 1: Already captured
            // ----------------------------------------------------
            if ($paymentIntent->status === 'succeeded') {

                $orderPayment = OrderPayment::where('payment_intent_id', $paymentIntent->id)->first();
                $orderPayment->status = 'succeeded';
                $orderPayment->save();
                $this->reconcilePaymentLedger($order);
                DB::commit();



                return response()->json([
                    'status'  => 'already_capture',
                    'success' => false,
                    'message' => 'This payment has already been captured.'
                ], 400);
            }

            // ----------------------------------------------------
            // CAPTURE PAYMENT
            // ----------------------------------------------------
            $captureAmount = (int) round($amount * 100);
            
            $capturedIntent = $paymentIntent->capture([
                'amount_to_capture' => $captureAmount,
            ]);

            // ----------------------------------------------------
            // CARD DETAILS (FIXED & RELIABLE)
            // ----------------------------------------------------
            $transactionId = $capturedIntent->latest_charge ?? null;
            $cardBrand = null;
            $cardLast4 = null;

            if ($transactionId) {
                $charge = \Stripe\Charge::retrieve([
                    'id' => $transactionId,
                    'expand' => ['payment_method_details.card']
                ]);

                if (
                    isset($charge->payment_method_details) &&
                    isset($charge->payment_method_details->card)
                ) {
                    $cardBrand = $charge->payment_method_details->card->brand ?? null;
                    $cardLast4 = $charge->payment_method_details->card->last4 ?? null;
                }
            }

            

            OrderPayment::updateOrCreate(
            // ✅ Unique condition
            [
                'payment_intent_id' => $capturedIntent->id,
            ],
            // ✅ Data to update or insert
            [
                'order_id'         => $order->id,
                'transaction_id'   => $transactionId,
                'payment_method'   => 'card',                
                'collection_type'  => 'Inside',
                'collection_date'  => now()->toDateString(),
                'card_brand'       => $cardBrand,
                'card_last4'       => $cardLast4,
                'amount'           => $amount,
                'currency'         => $capturedIntent->currency ?? $order->currency,
                'status'           => 'succeeded',
                'action'           => $action_name,
                'response_payload' => json_encode($capturedIntent),
                'updated_at'       => now(),
            ]
        );

            // ----------------------------------------------------
            // UPDATE ORDER STATUS
            // ----------------------------------------------------
            $this->reconcilePaymentLedger($order);

            DB::commit();

            return response()->json([
                'success' => true,
                'status'  => 'succeeded',
                'message' => 'Payment captured successfully.',
                'data'    => $capturedIntent,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'status'  => 'failed',
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




            $orderPayment = OrderPayment::where('payment_intent_id', $paymentIntent->id)->first();
            $orderPayment->status = 'capture_canceled';
            $orderPayment->save();


            // Log the cancellation
            // OrderPayment::create([
            //     'order_id'          => $order->id,
            //     'payment_intent_id' => $intentId,
            //     'transaction_id'    => $intentId,
            //     'payment_method'    => 'card',
            //     'status'            => 'canceled',
            //     'amount'            => 0,
            //     'currency'          => $order->currency,
            //     'action'            => 'cancel_uncaptured',
            //     'response_payload'  => json_encode($canceledIntent),
            // ]);

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

        // Prevent negative booked amount updates
        if (($order->booked_amount - $refundTarget) < 0) {
            return response()->json([
                'success' => false,
                'message' => "Refund exceeds available refundable amount."
            ], 400);
        }


        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

        $payments = $order->payments()
            ->where('payment_type', '!=', 'REFUND')
            ->whereIn('status', ['succeeded', 'partial_refunded'])
            ->orderBy('id')
            ->get();

        foreach ($payments as $payment) {

            if ($remaining <= 0) break;

            $alreadyRefunded = $payment->refund_amount ?? 0;
            $remainingRefundable = $payment->amount - $alreadyRefunded;

            if ($remainingRefundable <= 0) continue;

            $refundNow = min($remaining, $remainingRefundable);

            $refund = \Stripe\Refund::create([
                'payment_intent' => $payment->payment_intent_id,
                'amount' => (float)($refundNow * 100)
            ]);
            
            $payment->update([
                'refund_amount' => $alreadyRefunded + $refundNow,
                'refunded_at'   => now(),
                'refund_id'     => $refund->id,
                'status'        => ($alreadyRefunded + $refundNow) >= $payment->amount ? 'refunded' : 'partial_refunded'
            ]);

            $remaining -= $refundNow;
        }

        OrderPayment::create([
            'order_id'          => $order->id,
            'payment_intent_id' => $refund->id,
            'transaction_id'    => $refund->id,
            'payment_method'    => 'card',
            'payment_type'      => 'REFUND',
            'collection_type'   => 'Inside',
            'collection_date'   => now(),
            'amount'            => $refundTarget,
            'currency'          => $order->currency,
            'status'            => 'refunded',
            'action'            => 'manual_charge',
            'response_payload'  => json_encode($refund),
        ]);

        $order_actions = [
            'order_id'         => $order->id,
            'performed_by'     => Auth::id(),
            'notes'            => "Refund of payment (STRIPE: {$refund->id}) has been processed by ".Auth::user()->name.". Refund amount is : {$order->currency} {$refundTarget}",
            'created_at'       => now(),
            'updated_at'       => now()
        ];
        OrderActions::insert($order_actions);
        
        $this->reconcilePaymentLedger($order);

        return response()->json([
            'success' => true,
            'message' => "Refunded $refundTarget successfully."
        ]);
    }

    private function reconcilePaymentLedger(Order $order): void
    {
        $summary = (new OrderPaymentSummaryService())->summarize(
            $order->payments()->get()
        );
        $grossAmount = round((float) $order->orderTours()->sum('total_amount'), 2);
        $balance = round(max($grossAmount - $summary['total_credits'], 0), 2);

        $order->update([
            'booked_amount' => $summary['paid_amount'],
            'balance_amount' => $balance,
            'payment_status' => $summary['authorized_amount'] > 0
                ? 3
                : ($balance <= 0.01 ? 1 : 0),
        ]);
    }

    public function removeCard(Order $order)
    {
        try {
            \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));


            $paymentIntentId = $order->payment_intent_id;

            if (!$paymentIntentId) {
                return response()->json(['message' => 'No card found'], 400);
            }

            // CASE 1: Stored as PaymentMethod directly (pm_xxx)
            if (str_starts_with($paymentIntentId, 'pm_')) {
                $paymentMethod = \Stripe\PaymentMethod::retrieve($paymentIntentId);
                $paymentMethod->detach();
            }

            // CASE 2: Stored as PaymentIntent (pi_xxx)
            if (str_starts_with($paymentIntentId, 'pi_')) {
                $intent = \Stripe\PaymentIntent::retrieve($paymentIntentId);

                if ($intent->payment_method) {
                    $paymentMethod = \Stripe\PaymentMethod::retrieve($intent->payment_method);
                    $paymentMethod->detach();
                }
            }

            // Clean DB references
            $order->update([
                'payment_intent_id' => null,
                'payment_method_id' => null
            ]);

            // if ($order->latestPayment) {
            //     $order->latestPayment->update([
            //         'card_last4' => null,
            //         'card_brand' => null,
            //     ]);
            // }
            $order_actions = [
                    'order_id'         => $order->id,
                    'performed_by'     => Auth::id(),
                    'notes'            => "Credit card removed successfully has been processed by " . Auth::user()->name,
                    'created_at'       => now(),
                    'updated_at'       => now()
                ];
            OrderActions::insert($order_actions);

            return response()->json([
                'message' => 'Credit card removed successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function addCard(Request $request, Order $order)
    {
        $request->validate([
            'payment_method'      => 'required|string',
            'charge_ccnow'        => 'nullable|boolean',
            'charge_ccnow_amount' => 'nullable|numeric|min:0.01'
        ]);

        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

        $shouldCharge = $request->boolean('charge_ccnow') 
                        && $request->filled('charge_ccnow_amount');

        // Ensure Stripe customer exists
        if (
            empty($order->stripe_customer_id) ||
            !str_starts_with($order->stripe_customer_id, 'cus_')
        ) {
            $customer = \Stripe\Customer::create([
                'name'  => $order->customer?->name,
                'email' => $order->customer?->email,
            ]);

            $order->update([
                'stripe_customer_id' => $customer->id
            ]);
        }

        // Retrieve PaymentMethod
        $paymentMethod = \Stripe\PaymentMethod::retrieve($request->payment_method);

        // Attach card to customer
        $paymentMethod->attach([
            'customer' => $order->stripe_customer_id
        ]);

        // Set as default card
        \Stripe\Customer::update($order->stripe_customer_id, [
            'invoice_settings' => [
                'default_payment_method' => $paymentMethod->id
            ]
        ]);

        // Store minimal info for UI (your original logic)
        $order->update([
            'payment_intent_id' => $paymentMethod->id
        ]);

        /*
        |--------------------------------------------------------------------------
        | CHARGE LOGIC (NEW PART)
        |--------------------------------------------------------------------------
        */

        if ($shouldCharge) {

            $chargeAmount = (float) $request->charge_ccnow_amount;

            if ($chargeAmount > $order->balance_amount) {
                return response()->json([
                    'message' => 'Charge exceeds remaining balance.'
                ], 400);
            }

            $intent = \Stripe\PaymentIntent::create([
                'customer'       => $order->stripe_customer_id,
                'amount'         => intval($chargeAmount * 100),
                'currency'       => $order->currency ?? 'eur',
                'payment_method' => $paymentMethod->id,
                
                'off_session'    => true,
                'confirm'        => true,
                'description'    => "#{$order->order_number}",
            ]);

            // Update order financials
            $order->booked_amount += $chargeAmount;
            $order->balance_amount = max(
                $order->total_amount - $order->booked_amount,
                0
            );

            $order->save();

            $order->payments()->create([
                'payment_type'   => 'CREDITCARD',
                'transaction_id' => $intent->id,
                'payment_intent_id' => $intent->id,
                'card_last4'     => $paymentMethod->card->last4,
                'card_brand'     => $paymentMethod->card->brand,
                'amount'         => $chargeAmount,
                'currency'       => $order->currency,
                'collection_type'=> 'Inside',
                'status'         =>  'succeeded'
            ]);

            $note = "Credit card added and charged {$chargeAmount} {$order->currency} by " . Auth::user()->name;

        } else {

            // Only card added (your original behaviour)
            $order->payments()->create([
                'payment_type'   => 'CREDITCARD',
                'transaction_id' => $paymentMethod->id,
                'card_last4'     => $paymentMethod->card->last4,
                'card_brand'     => $paymentMethod->card->brand,
                'amount'         => 0,
                'currency'       => $order->currency,
                'collection_type'=> 'Inside'
            ]);

            $note = "Credit card added successfully has been processed by " . Auth::user()->name;
        }

        OrderActions::insert([
            'order_id'     => $order->id,
            'performed_by' => Auth::id(),
            'notes'        => $note,
            'created_at'   => now(),
            'updated_at'   => now()
        ]);

        return response()->json([
            'message' => $shouldCharge 
                ? 'Card added and charged successfully.'
                : 'Card added successfully to this customer'
        ]);
    }

    public function importOrders(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        try {

            $import = new OrdersImport();
            // dd( $request->file('file'));
            \Maatwebsite\Excel\Facades\Excel::import($import, $request->file('file'));

            // $summary = $import->getSummary();

            // dd(323432);


            return back()->with('import_summary', []);

        } catch (\Throwable $e) {

            \Log::error('Multi sheet import crashed', [
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Import failed unexpectedly.');
        }
    }

    public function sampleExcel()
    {
        $data = [
            [
                'Date',
                'Check-in',
                'Order Number',
                'Customer Full Name',
                'Customer Phone',
                'Product name',
                'Quantities',
                'Quantities Label',
                'Quantities Price',
                'Extras',
                'Extras Label',
                'Extras Price',
                'Order Balance',
                'Order Total Amount',
                'Order Total Paid',
                'Pick-up Time',
                'Pick-up Location',
                'Order Status',
            ],
            // Optional sample row (remove if you want header only)
            [
                '2025-02-01',
                '2025-02-10',
                'ORD12345',
                'John Doe',
                '9876543210',
                'Desert Safari',
                '2',
                'Adults',
                '100',
                '3',
                'Boat Cruise Ride - Adult',
                '50',
                '50',
                '500',
                '450',
                '10:00 AM',
                'Dubai Mall',
                'Confirmed',
            ]
        ];

        return Excel::download(
            new class($data) implements FromArray {
                public function __construct(private array $data) {}
                public function array(): array { 
                    return $this->data; 
                }
            },
            'order_import_sample.xlsx'
        );
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

    public function addCard2342(Request $request, Order $order)
    {

        $request->validate([
            'payment_method' => 'required|string'
        ]);

        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

        // Ensure Stripe customer exists
        if (
            empty($order->stripe_customer_id) ||
            !str_starts_with($order->stripe_customer_id, 'cus_')
        ) {
            $customer = \Stripe\Customer::create([
                'name'  => $order->customer?->name,
                'email' => $order->customer?->email,
            ]);

            $order->update([
                'stripe_customer_id' => $customer->id
            ]);
        }

        // Retrieve PaymentMethod
        $paymentMethod = \Stripe\PaymentMethod::retrieve($request->payment_method);

        // Attach card to SAME customer (FIXED)
        $paymentMethod->attach([
            'customer' => $order->stripe_customer_id
        ]);

        // Set as default card
        \Stripe\Customer::update($order->stripe_customer_id, [
            'invoice_settings' => [
                'default_payment_method' => $paymentMethod->id
            ]
        ]);

        // Store minimal info for UI
        $order->update([
            'payment_intent_id' => $paymentMethod->id
        ]);

        $order->payments()->create([
            'payment_type'   => 'CREDITCARD',
            'transaction_id' => $paymentMethod->id,
            'card_last4'     => $paymentMethod->card->last4,
            'card_brand'     => $paymentMethod->card->brand,
            'amount'         => 0,
            'currency'       => $order->currency,
            'collection_type'=> 'Inside'
        ]);
        $order_actions = [
                'order_id'         => $order->id,
                'performed_by'     => Auth::id(),
                'notes'            => "Credit card added successfully has been processed by " . Auth::user()->name,
                'created_at'       => now(),
                'updated_at'       => now()
            ];
        OrderActions::insert($order_actions);

        return response()->json([
            'message' => 'Card added successfully to this customer'
        ]);
    }

    public function manifest23423(Request $request)
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
    
    public function downloadManifest323423(Request $request)
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
                ->where('order_status', 5)
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
    public function removeOrderTour(Request $request)
    {
        $orderId = $request->order_id;
        $tourId  = $request->order_tour_id;

        // Validate input
        if (!$orderId || !$tourId) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid request data.'
            ], 422);
        }

        $order = Order::find($orderId);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found.'
            ], 404);
        }

        // Lock rows to prevent race condition
        $orderTours = OrderTour::where('order_id', $orderId)
            ->lockForUpdate()
            ->get();

        if ($orderTours->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No tours found for this order.'
            ], 404);
        }

        // 🚨 Prevent deleting last tour
        if ($orderTours->count() <= 1) {
            return response()->json([
                'success' => true,
                'message' => 'At least one tour is required in the order.'
            ], 200);
        }

        $orderTour = $orderTours->firstWhere('id', $tourId);

        if (!$orderTour) {
            return response()->json([
                'success' => false,
                'message' => 'Order tour not found.'
            ], 404);
        }

        // Use transaction for safety
        \DB::beginTransaction();

        try {
            // Deduct amount
            $order->total_amount -= $orderTour->total_amount;
            $order->balance_amount = max(
                $order->total_amount - ($order->booked_amount ?? 0),
                0
            );

            // Delete
            $orderTour->delete();
            $order->save();

            // Log
            OrderActions::create([
                'order_id'     => $order->id,
                'performed_by' => Auth::id(),
                'notes'        => Auth::user()->name . " removed a tour from order #" . $order->order_number,
            ]);

            \DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tour removed from order successfully.'
            ], 200);

        } catch (\Exception $e) {

            \DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again.'
            ], 500);
        }
    }

    private function calculateTotals(
        float $totalOrderAmount,
        float $totalPaymentAmount,
        array $paymentTypes,
        array $amounts
    ): array
    {
        $excludeAmount = 0;
        
        foreach ($paymentTypes as $index => $type) {
            if ($type === 'EXCLUDEDPAYMENT') {
                
                $excludeAmount += (float)($amounts[$index] ?? 0);
            }
        }
        
        if ($excludeAmount > 0) {


            return [
                'total'   => $excludeAmount,
                'paid'    => $excludeAmount,
                'balance' => 0,
                'exclude' => true,
            ];
        }

        return [
            'total'   => $totalOrderAmount,
            'paid'    => $totalPaymentAmount,
            'balance' => max($totalOrderAmount - $totalPaymentAmount, 0),
            'exclude' => false,
        ];
    }
}
