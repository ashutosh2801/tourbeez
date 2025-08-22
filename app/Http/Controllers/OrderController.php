<?php

namespace App\Http\Controllers;

use App\Exports\ManifestExport;
use App\Mail\EmailManager;
use App\Models\EmailTemplate;
use App\Models\Order;
use App\Models\OrderEmailHistory;
use App\Models\OrderTour;
use App\Models\SmsTemplate;
use App\Models\Tour;
use App\Models\TourPricing;
use App\Services\TwilioService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;


class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // public function index(Request $request)
    // {

    //     $query = Order::with(['customer', 'orderTours'])->orderBy('created_at', 'DESC');

    //     if ($search = $request->input('search')) {
    //         $query->where(function ($q) use ($search) {
    //             $q->where('order_number', 'like', "%{$search}%")
    //             ->orWhereHas('customer', function ($q2) use ($search) {
    //                 $q2->where('first_name', 'like', "%{$search}%");
    //                 $q2->orWhere('last_name', 'like', "%{$search}%");
    //             });
    //         });
    //     }

    //     $orders = $query->paginate(10);

    //     return view('admin.order.index', compact(['orders']));
    // }

//     use App\Models\Order;
// use App\Models\Tour;
// use Illuminate\Http\Request;

    public function index(Request $request)
    {
        $query = Order::with(['customer', 'orderTours.tour'])->orderBy('created_at', 'DESC');

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

        // if ($end = $request->input('tour_end_date')) {
        //     $query->whereHas('orderTours', function ($q) use ($end) {
        //         $q->whereDate('tour_date', '<=', $end);
        //     });
        // }
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
        // $email_templates = EmailTemplate::get();

        $email_templates = EmailTemplate::whereIn('identifier', [
            'order_detail',
            'order_cancelled',
            'order_confirmed',
            'payment_receipt',
            'order_pending',
            'payment_required'
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
        $order->balance_amount = $total - $order->total_amount;
        
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
        $email = $request->input('email');
        $subject = $request->input('subject');
        $header = $request->input('header');
        $body = $request->input('body');
        $footer = $request->input('footer');
        $event = $request->input('event');

        if (env('MAIL_FROM_ADDRESS') != null) {
            $array['view'] = 'emails.newsletter';
            $array['subject'] = $subject;
            $array['header'] = $header;
            $array['from'] = env('MAIL_FROM_ADDRESS');
            $array['content'] =  $header.$body.$footer;
            // dd($event);
            $array['event'] = json_decode($event, true);
 
            try {

                if(Mail::to($request->email)->send(new EmailManager($array))){
                    if($request->has('order')){
                        $order = $request->order;
                        OrderEmailHistory::create([
                            'order_id'  => $order->id,
                            'to_email'  => $request->email,
                            'from_email'=> env('MAIL_FROM_ADDRESS'),
                            'subject'   => $subject,
                            'body'      => $header.$body.$footer,
                            'status'    => 'sent'
                        ]);

                    }
                    
                    return response()->json(['status' => 'success']);
                }else{
                     return response()->json(['status' => 'Failed']);
                }
                 
            } catch (\Exception $e) {
                dd($e);
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

            $TOUR_ITEM_SUMMARY = '
                    <table width="640" bgcolor="#ffffff" cellpadding="0" cellspacing="0" border="0" align="center" class="header_table" style="width:640px;">
                        <tbody>
                        <tr>
                            <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; text-align: left; padding: 30px 30px 15px; width:640px;">
                                <h3 style="font-size:19px"><strong>Item Summary</strong></h3>
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
                                    <small style="font-size:10px; font-weight:400; text-transform: uppercase; color:#000">
                                        &nbsp;
                                    </small>
                                </td>
                                <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; width: 20%; border-bottom:2pt solid #000; text-align: right;padding: 5px 0px;">
                                    <small style="font-size:10px; font-weight:400; text-transform: uppercase; color:#000">Total</small>
                                </td>
                            </tr>

                            <tr>
                                <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; border-top:1pt solid #ddd; text-align: left;padding: 5px 0px;">
                                    5
                                </td>
                                <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; border-top:1pt solid #ddd; text-align: left;padding: 5px 0px;">
                                    Adult (13+)
                                </td>
                                <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; border-top:1pt solid #ddd; text-align: left;padding: 5px 0px;">
                                    $42.95
                                </td>
                                <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; border-top:1pt solid #ddd; text-align: right;padding: 5px 0px;">
                                    $214.75
                                </td>
                            </tr>

                            <tr>
                                <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; border-top:2pt solid #000;;padding: 5px 0px;padding: 5px 0px;">
                                    &nbsp;
                                </td>
                                <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; border-top:2pt solid #000;;padding: 5px 0px;padding: 5px 0px;">
                                    &nbsp;
                                </td>
                                <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; border-top:2pt solid #000; text-align: left;padding: 5px 0px;padding: 5px 0px;">
                                    <small style="font-size:10px; font-weight:400; text-transform: uppercase; color:#000;">HST ON</small>
                                </td>
                                <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; border-top:2pt solid #000; text-align: right;padding: 5px 0px;padding: 5px 0px;">
                                    $27.92
                                </td>
                            </tr>

                            <tr>
                                <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; ">
                                    &nbsp;
                                </td>
                                <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; ">
                                    &nbsp;
                                </td>
                                <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; border-top:2pt solid #000; text-align: left;padding: 5px 0px;">
                                    <small style="font-size:10px; font-weight:400; text-transform: uppercase; color:#000;">Total</small>
                                </td>
                                <td style="font-family: \'Lato\', Helvetica, Arial, sans-serif; border-top:2pt solid #000; text-align: right;padding: 5px 0px;">
                                    <h3 style="color:#000; margin:0; font-size:19px"><strong>$242.67</strong></h3>
                                </td>
                            </tr>
                        </tbody>
                    </table>';

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

                "[[ORDER_NUMBER]]"          => $order->order_number ?? '',
                "[[ORDER_STATUS]]"          => $order->status,
                "[[ORDER_TOUR_DATE]]"       => date('l, F j, Y', strtotime($order->created_at)),
                "[[ORDER_TOUR_TIME]]"       => date('H:i A', strtotime($order->created_at)),
                "[[ORDER_TOTAL]]"           => price_format_with_currency($order->total_amount, $order->currency) ?? '',
                "[[ORDER_BALANCE]]"         => price_format_with_currency($order->balance_amount, $order->currency) ?? '',
                "[[ORDER_BOOKING_FEE]]"     => price_format_with_currency($order->booking_fee, $order->currency) ?? '',
                "[[ORDER_CREATED_DATE]]"    => date('M d, Y', strtotime($order->created_at)) ?? '',
            ];
 
            $finalMessage = strtr($template, $replacements);
            $finalfooter = strtr($template_footer, $replacements);
            $finalsubject = strtr($template_subject, $replacements);
            
            if ($order) {
                $email_template->subject = $finalsubject;

                return response()->json([
                    'success' => true,
                    'email' => $customer->email,
                    'email_template' => $email_template,
                    'body'=>$finalMessage,
                    'footer'=>$finalfooter,
                    'event' => [
                        'uid' => "TB" . $order->order_number,
                        'start' => date('H:i A', strtotime($order->created_at)), // local time
                        'end' => date('H:i A', strtotime($order->created_at)),
                        'title' => $tour->title,
                        'description' => $email_template->subject,
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


        $order->save();

        $this->sendOrderStatusEmail($order);

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


    

}
