<?php

namespace App\Http\Controllers;

use App\Exports\CustomerExport;
use App\Exports\InvoiceWithDetailsExport;
use App\Exports\RevenueExport;
use App\Models\Category;
use App\Models\Order;
use App\Models\Partner;
use App\Models\Tour;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{

public function overview(Request $request)
{
    $excludedStatuses = [1, 2, 6, 7];

    /*
    |--------------------------------------------------------------------------
    | DATE FILTER
    |--------------------------------------------------------------------------
    */
    $startDate = $request->start_date 
        ? Carbon::parse($request->start_date)->startOfDay()
        : Carbon::today()->startOfDay();

    $endDate = $request->end_date 
        ? Carbon::parse($request->end_date)->endOfDay()
        : Carbon::today()->endOfDay();

    /*
    |--------------------------------------------------------------------------
    | BASE QUERY (FILTERED ORDERS)
    |--------------------------------------------------------------------------
    */
    $orderQuery = DB::table('orders')
        ->leftJoin('order_tours', 'orders.id', '=', 'order_tours.order_id')
        ->whereNull('orders.deleted_at')
        ->whereNotIn('orders.order_status', $excludedStatuses)
        ->whereBetween('orders.created_at', [$startDate, $endDate]);

    // Filters
    if ($request->filled('tour_start_date') && $request->filled('tour_end_date')) {
        $orderQuery->whereBetween('order_tours.tour_date', [
            Carbon::parse($request->tour_start_date)->startOfDay(),
            Carbon::parse($request->tour_end_date)->endOfDay(),
        ]);
    }

    if ($request->filled('order_status')) {
        $orderQuery->where('orders.order_status', $request->order_status);
    }

    if ($request->filled('payment_status')) {
        $orderQuery->where('orders.payment_status', $request->payment_status);
    }

    if ($request->filled('partner')) {
        $orderQuery->where('orders.source', $request->partner);
    }

    if ($request->action_type === 'pay_now') {
        $orderQuery->where('orders.action_name', 'book');
    } elseif ($request->action_type === 'pay_later') {
        $orderQuery->where(function ($q) {
            $q->where('orders.action_name', '!=', 'book')
              ->orWhereNull('orders.action_name');
        });
    }

    /*
    |--------------------------------------------------------------------------
    | GET ORDERS
    |--------------------------------------------------------------------------
    */
    $orders = $orderQuery
        ->select('orders.id', 'orders.currency')
        ->distinct()
        ->get();

    $orderIds = $orders->pluck('id');

    /*
    |--------------------------------------------------------------------------
    | FETCH RELATED DATA (ONLY FILTERED IDS)
    |--------------------------------------------------------------------------
    */
    $orderTours = DB::table('order_tours')
        ->whereIn('order_id', $orderIds)
        ->get()
        ->groupBy('order_id');

    $payments = DB::table('order_payments')
        ->whereIn('order_id', $orderIds)
        ->whereNull('deleted_at')
        ->get()
        ->groupBy('order_id');

    /*
    |--------------------------------------------------------------------------
    | CALCULATIONS
    |--------------------------------------------------------------------------
    */
    $gross = 0;
    $totalPaidAll = 0;
    $totalBalanceAll = 0;
    $refund = 0;

    foreach ($orders as $order) {

        $tours = $orderTours[$order->id] ?? collect();
        $orderPayments = $payments[$order->id] ?? collect();

        $finalTotal = 0;

        foreach ($tours as $tour) {

            $productValue = 0;
            $extraValue = 0;
            $discountAmount = 0;
            $subtotal = 0;

            $pricing = json_decode($tour->tour_pricing, true) ?? [];
            $extras  = json_decode($tour->tour_extra, true) ?? [];
            $discounts = json_decode($tour->discount, true) ?? [];

            // Pricing
            foreach ($pricing as $p) {
                $qty = $p['quantity'] ?? 0;
                $price = $p['actual_price'] ?? $p['price'] ?? 0;

                if ($qty > 0) {
                    $productValue += ($p['price_type'] == 'FIXED')
                        ? $price
                        : $price * $qty;
                }
            }

            $subtotal += $productValue;

            // Extras
            foreach ($extras as $e) {
                $qty = $e['quantity'] ?? 0;
                $price = $e['price'] ?? 0;

                if ($qty > 0) {
                    $extraValue += ($e['total_price'] ?? ($qty * $price));
                }
            }

            $subtotal += $extraValue;

            // Discount
            foreach ($discounts as $d) {
                $discountAmount += $d['price'] ?? 0;
            }

            $subtotal -= $discountAmount;

            // Tax
            if (!empty($tour->tour_fees)) {
                $taxes = is_string($tour->tour_fees)
                    ? json_decode($tour->tour_fees, true)
                    : $tour->tour_fees;

                foreach ($taxes as $tax) {
                    $taxAmount = get_tax($subtotal, $tax['type'], 13);
                    $subtotal += $taxAmount;
                }
            }

            $finalTotal += $subtotal;
        }

        // Payments
        $totalPaid = $orderPayments->where('status', 'succeeded')->sum('amount')
            - $orderPayments->where('status', 'refunded')->sum('amount');

        $promoPayment = $orderPayments
            ->where('collection_type', 'Outside')
            ->where('payment_type', 'PROMO_CODE')
            ->sum('amount');

        $paid = $totalPaid - $promoPayment;

        $balance = $finalTotal - $paid;

        // Refund
        $refundAmount = $orderPayments->sum('refund_amount');

        // Convert to CAD
        $gross += currencyConvertWithoutRound($finalTotal, $order->currency, 'CAD');
        $totalPaidAll += currencyConvertWithoutRound($paid, $order->currency, 'CAD');
        $totalBalanceAll += currencyConvertWithoutRound($balance, $order->currency, 'CAD');
        $refund += currencyConvertWithoutRound($refundAmount, $order->currency, 'CAD');
    }

    /*
    |--------------------------------------------------------------------------
    | FINAL OUTPUT
    |--------------------------------------------------------------------------
    */
    $performance = [
        'total_orders'     => $orders->count(),
        'gross_sales'      => round($gross, 2),
        'payment_received' => round($totalPaidAll, 2),
        'pending_amount'   => round($totalBalanceAll, 2),
        'refund'           => round($refund, 2),
        'net_sales'        => round($gross - $refund, 2),
    ];

    $partners = Partner::get();

    return view('admin.reports.overview', compact('performance', 'partners'));
}
    public function overview234(Request $request)
{
    /*
    |--------------------------------------------------------------------------
    | BASE QUERY
    |--------------------------------------------------------------------------
    */
    $excludedStatuses = [1, 2, 6, 7];

    $orderQuery = DB::table('orders')
        ->leftJoin('order_tours', 'orders.id', '=', 'order_tours.order_id')
        ->whereNull('orders.deleted_at')
        ->whereNotIn('orders.order_status', $excludedStatuses);

    // Filters

        // ✅ Default dates
    $startDate = $request->start_date 
        ? $request->start_date . ' 00:00:00' 
        : Carbon::today()->startOfDay();

    $endDate = $request->end_date 
        ? $request->end_date . ' 23:59:59' 
        : Carbon::today()->endOfDay();

    // if ($request->filled('start_date') && $request->filled('end_date')) {
        // $orderQuery->whereBetween('orders.created_at', [
        //     $request->start_date . ' 00:00:00',
        //     $request->end_date . ' 23:59:59'
        // ]);
    // }

    $orderQuery->whereBetween('orders.created_at', [$startDate, $endDate]);



    if ($request->filled('tour_start_date') && $request->filled('tour_end_date')) {
        $orderQuery->whereBetween('order_tours.tour_date', [
            $request->tour_start_date,
            $request->tour_end_date
        ]);
    }

    if ($request->filled('order_status')) {
        $orderQuery->where('orders.order_status', $request->order_status);
    }

    if ($request->filled('payment_status')) {
        $orderQuery->where('orders.payment_status', $request->payment_status);
    }
    if ($request->filled('partner')) {
        $orderQuery->where('orders.source', $request->partner);
    }

    if ($request->action_type === 'pay_now') {
        $orderQuery->where('orders.action_name', 'book');
    } elseif ($request->action_type === 'pay_later') {
        $orderQuery->where(function ($q) {
            $q->where('orders.action_name', '!=', 'book')
              ->orWhereNull('orders.action_name');
        });
    }

    /*
    |--------------------------------------------------------------------------
    | GET FILTERED ORDERS (ONLY REQUIRED FIELDS)
    |--------------------------------------------------------------------------
    */
    $orders = $orderQuery
        ->select('orders.id', 'orders.total_amount', 'orders.currency')
        ->distinct()
        ->get();

    /*
    |--------------------------------------------------------------------------
    | GROSS SALES (CONVERT TO CAD)
    |--------------------------------------------------------------------------
    */
    $gross = 0;

    foreach ($orders as $order) {
        $gross += currencyConvertWithoutRound(
            $order->total_amount,
            $order->currency,
            'CAD'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | REFUNDS (CONVERT TO CAD)
    |--------------------------------------------------------------------------
    */
    $refundRecords = DB::table('order_payments')
        ->join('orders', 'orders.id', '=', 'order_payments.order_id')
        ->whereIn('order_payments.order_id', $orders->pluck('id'))
        ->whereNull('order_payments.deleted_at')
        ->select('order_payments.refund_amount', 'orders.currency')
        ->get();

    $refund = 0;

    foreach ($refundRecords as $r) {
        $refund += currencyConvertWithoutRound(
            $r->refund_amount,
            $r->currency,
            'CAD'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | FINAL CALCULATION
    |--------------------------------------------------------------------------
    */
    $performance = [
        'total_orders' => $orders->count(),
        'gross_sales'  => round($gross, 2),
        'refund'       => round($refund, 2),
        'net_sales'    => round($gross - $refund, 2),
    ];
    $partners = Partner::get();

    return view('admin.reports.overview', compact('performance', 'partners'));
}


// use Illuminate\Http\Request;
//



public function revenue(Request $request)
{
    /*
    |--------------------------------------------------------------------------
    | DATE FILTER (DEFAULT TODAY)
    |--------------------------------------------------------------------------
    */

    $excludedStatuses = [1, 2, 6, 7];
    $startDate = $request->start_date
        ? Carbon::parse($request->start_date)->startOfDay()
        : Carbon::today()->startOfDay();

    $endDate = $request->end_date
        ? Carbon::parse($request->end_date)->endOfDay()
        : Carbon::today()->endOfDay();

    /*
    |--------------------------------------------------------------------------
    | BASE QUERY
    |--------------------------------------------------------------------------
    */

    
        


    $query = DB::table('orders')
        ->leftJoin('order_tours', 'orders.id', '=', 'order_tours.order_id')
        ->leftJoin('tours', 'order_tours.tour_id', '=', 'tours.id')
        ->leftJoin('order_customers', 'orders.id', '=', 'order_customers.order_id')
        ->whereNull('orders.deleted_at')
        ->whereNotIn('orders.order_status', $excludedStatuses)
        ->whereBetween('orders.created_at', [$startDate, $endDate])->groupBy('orders.id');

    /*
    |--------------------------------------------------------------------------
    | FILTERS
    |--------------------------------------------------------------------------
    */

    // ✅ Order Status
    if ($request->filled('order_status')) {
        $query->where('orders.order_status', $request->order_status);
    }
    if ($request->filled('partner')) {
        $query->where('orders.source', $request->partner);
    }

    // ✅ Payment Status
    if ($request->filled('payment_status')) {
        $query->where('orders.payment_status', $request->payment_status);
    }

    // ✅ Pay Type
    if ($request->action_type === 'pay_now') {
        $query->where('orders.action_name', 'book');
    } elseif ($request->action_type === 'pay_later') {
        $query->where(function ($q) {
            $q->where('orders.action_name', '!=', 'book')
              ->orWhereNull('orders.action_name');
        });
    }

    // ✅ Tour Date Filter (IMPORTANT FIX)
    if ($request->filled('tour_start_date') && $request->filled('tour_end_date')) {
        $query->whereBetween('order_tours.tour_date', [
            Carbon::parse($request->tour_start_date)->startOfDay(),
            Carbon::parse($request->tour_end_date)->endOfDay(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | FETCH DATA (PAGINATED)
    |--------------------------------------------------------------------------
    */
    $orders = $query
        ->select(
            'orders.id',
            'orders.order_number',
            'orders.order_status',
            'orders.payment_status',
            'orders.source',
            'orders.created_by',
            'orders.created_at as booking_date',
            'order_tours.tour_date as fulfilment_date',
            'order_tours.tour_id',

            // ✅ REQUIRED FOR CALCULATION
            'order_tours.tour_pricing',
            'order_tours.tour_extra',
            'order_tours.discount',

            'order_tours.tour_fees',

            'order_customers.first_name as customer_first_name',
            'order_customers.last_name as customer_last_name',

            'orders.total_amount',
            'orders.balance_amount',
            'orders.currency',

            'orders.booking_fee',

            'order_tours.number_of_guests as pax',

            'order_customers.promo_code',
            'order_customers.instructions',

            'orders.payment_method',
             'tours.title as product_name'
        )
        ->orderByDesc('orders.id')
        ->paginate(8)
        ->withQueryString();

    /*
    |--------------------------------------------------------------------------
    | CATEGORY MAP
    |--------------------------------------------------------------------------
    */
    $categoryMap = DB::table('category_tour')
        ->leftJoin('categories', 'categories.id', '=', 'category_tour.category_id')
        ->select('category_tour.tour_id', DB::raw('GROUP_CONCAT(categories.name) as categories'))
        ->groupBy('category_tour.tour_id')
        ->pluck('categories', 'tour_id');


    $orderIds = $orders->pluck('id');

    $payments = DB::table('order_payments')
        ->whereIn('order_id', $orderIds)
        ->get()
        ->groupBy('order_id');

    /*
    |--------------------------------------------------------------------------
    | FINAL FORMAT + CURRENCY CONVERSION (CAD)
    |--------------------------------------------------------------------------
    */
    $orders->getCollection()->transform(function ($order) use ($payments, $categoryMap) {

        $finalTotal = 0;
        $totalTax = 0;
        $productValue = 0;
        $extraValue = 0;

        $pricing = json_decode($order->tour_pricing, true) ?? [];
        $extras  = json_decode($order->tour_extra, true) ?? [];
        $discounts = json_decode($order->discount, true) ?? [];
        
        $subtotal2 = 0;

        // ✅ Pricing
        foreach ($pricing as $p) {
            $qty = $p['quantity'] ?? 0;
            $actual_price = $p['actual_price'] ?? $p['price'] ?? 0;

            if ($qty > 0) {
                $productValue += ($p['price_type'] == 'FIXED')
                    ? $actual_price
                    : $actual_price * $qty;
            }
        }

        $subtotal2 +=$productValue;

        // ✅ Extras
        foreach ($extras as $e) {
            $qty = $e['quantity'] ?? 0;
            $price = $e['price'] ?? 0;

            if ($qty > 0) {
                $extraValue += ($e['total_price'] ?? ($qty * $price));
            }
        }

        $subtotal2 +=$extraValue;

        // ✅ Discount
        $discountAmount = 0;
        if (!empty($discounts)) {
            foreach ($discounts as $d) {
                $discountAmount = $d['price'] ?? 0;
            }
        }

        $subtotal2 -= $discountAmount;
        // ✅ Taxes (if JSON)

        // dd($order->taxes_fees);
        if (!empty($order->tour_fees)) {
            $taxes = is_string($order->tour_fees)
                ? json_decode($order->tour_fees, true)
                : $order->tour_fees;
            
            foreach ($taxes as $tax) {
                $taxAmount = get_tax($subtotal2, $tax['type'], 13);
                $subtotal2 += $taxAmount;
                $totalTax += $taxAmount;
            }
        }

        $finalTotal = $subtotal2;

        // dd($check,$taxes, $taxAmount, $finalTotal, $subtotal2, $discountAmount, $extraValue, $productValue);

        // ✅ Payments
        $orderPayments = $payments[$order->id] ?? collect();

        $totalPaid = $orderPayments->where('status', 'succeeded')->sum('amount')
            - $orderPayments->where('status', 'refunded')->sum('amount');

        $promoPayment = $orderPayments
            ->where('collection_type', 'Outside')
            ->where('payment_type', 'PROMO_CODE')
            ->sum('amount');

        $paid = $totalPaid - $promoPayment;

        $balance = $finalTotal - $paid;

        // ✅ Convert to CAD
        $order->total_amount_converted = round(currencyConvertWithoutRound($finalTotal, $order->currency, 'CAD'), 2);
        $order->paid_amount_converted = round(currencyConvertWithoutRound($paid, $order->currency, 'CAD'), 2);
        $order->balance_converted = round(currencyConvertWithoutRound($balance, $order->currency, 'CAD'), 2);
        $order->tax_converted = round(currencyConvertWithoutRound($totalTax, $order->currency, 'CAD'), 2);
        $order->product_value_converted = round(currencyConvertWithoutRound($productValue, $order->currency, 'CAD'), 2);
        $order->extra_value_converted = round(currencyConvertWithoutRound($extraValue, $order->currency, 'CAD'), 2);
        $order->discount_value_converted = round(currencyConvertWithoutRound($discountAmount, $order->currency, 'CAD'), 2);

    
        $order->net_sales_converted = round(
            $order->total_amount_converted - $order->tax_converted,
            2
        );

        $order->all_paid = $order->balance_converted <= 0 ? 'Yes' : 'No';



        /*
        |--------------------------------------------------------------------------
        | CATEGORY
        |--------------------------------------------------------------------------
        */
        $order->category = $categoryMap[$order->tour_id] ?? '-';

        return $order;
    });


    /*
|--------------------------------------------------------------------------
| CUSTOMER REPORT QUERY
|--------------------------------------------------------------------------
*/
$customers = DB::table('orders')
    ->leftJoin('order_tours', 'orders.id', '=', 'order_tours.order_id')
    ->leftJoin('order_customers', 'orders.id', '=', 'order_customers.order_id')
    ->whereNull('orders.deleted_at')
    ->whereNotIn('orders.order_status', $excludedStatuses)
    ->whereBetween('orders.created_at', [$startDate, $endDate])->groupBy('orders.id');

// SAME FILTERS (IMPORTANT)
if ($request->filled('payment_status')) {
    $customers->where('orders.payment_status', $request->payment_status);
}
 if ($request->filled('order_status')) {
        $customers->where('orders.order_status', $request->order_status);
    }

if ($request->action_type === 'pay_now') {
    $customers->where('orders.action_name', 'book');
} elseif ($request->action_type === 'pay_later') {
    $customers->where(function ($q) {
        $q->where('orders.action_name', '!=', 'book')
          ->orWhereNull('orders.action_name');
    });
}

if ($request->filled('partner')) {
        $customers->where('orders.source', $request->partner);
    }
 if ($request->filled('tour_start_date') && $request->filled('tour_end_date')) {
        $customers->whereBetween('order_tours.tour_date', [
            Carbon::parse($request->tour_start_date)->startOfDay(),
            Carbon::parse($request->tour_end_date)->endOfDay(),
        ]);
    }

$customers = $customers->select(
        'orders.order_number',
        'orders.created_at as booking_date',
        'order_tours.tour_date as fulfilment_date',

        'order_customers.id',
        'order_customers.first_name',
        'order_customers.last_name',
        'order_customers.email',
        'order_customers.phone',
        'order_customers.instructions',

        'order_customers.promo_code'
    )
    ->orderByDesc('orders.id')
    ->paginate(8, ['*'], 'customer_page') // IMPORTANT (separate pagination)
    ->withQueryString();

    $partners = Partner::get();

    return view('admin.reports.revenue', compact('orders', 'customers', 'partners'));
}

public function invoice(Request $request)
{
    $data = $this->getInvoiceData($request, true);

    return view('admin.reports.invoice', [
        'rows' => $data['rows'],
        'orders' => $data['pagination'],
        'partners' => Partner::get()
    ]);
}

public function invoiceExport(Request $request)
{
    $data = $this->getInvoiceData($request);

    return \Maatwebsite\Excel\Facades\Excel::download(
        new \App\Exports\InvoiceExport($data),
        'invoice-report.xlsx'
    );
}


private function getInvoiceData($request, $paginate = false)
{
    $excludedStatuses = [1, 2, 6, 7];

    $startDate = $request->start_date
        ? Carbon::parse($request->start_date)->startOfDay()
        : Carbon::today()->startOfDay();

    $endDate = $request->end_date
        ? Carbon::parse($request->end_date)->endOfDay()
        : Carbon::today()->endOfDay();

    $query = DB::table('orders')
        ->leftJoin('order_tours', 'orders.id', '=', 'order_tours.order_id')
        ->leftJoin('order_customers', 'orders.id', '=', 'order_customers.order_id')
        ->leftJoin('tours', 'order_tours.tour_id', '=', 'tours.id')
        ->whereNull('orders.deleted_at')
        ->whereNotIn('orders.order_status', $excludedStatuses)
        ->whereBetween('orders.created_at', [$startDate, $endDate])
        ->groupBy('orders.id');

    // Filters
    if ($request->filled('order_status')) {
        $query->where('orders.order_status', $request->order_status);
    }

    if ($request->filled('payment_status')) {
        $query->where('orders.payment_status', $request->payment_status);
    }

    if ($request->filled('partner')) {
        $query->where('orders.source', $request->partner);
    }

    if ($request->action_type === 'pay_now') {
        $query->where('orders.action_name', 'book');
    } elseif ($request->action_type === 'pay_later') {
        $query->where(function ($q) {
            $q->where('orders.action_name', '!=', 'book')
              ->orWhereNull('orders.action_name');
        });
    }

    if ($request->filled('tour_start_date') && $request->filled('tour_end_date')) {
        $query->whereBetween('order_tours.tour_date', [
            Carbon::parse($request->tour_start_date)->startOfDay(),
            Carbon::parse($request->tour_end_date)->endOfDay(),
        ]);
    }

    $query->select(
        'orders.id',
        'orders.order_number',
        'orders.payment_status',
        'orders.created_at',
        'orders.currency',
        'orders.booking_fee',

        'order_tours.tour_date',
        'order_tours.tour_pricing',
        'order_tours.tour_extra',
        'order_tours.discount',
        'order_tours.tour_fees',

        'order_customers.first_name',
        'order_customers.last_name',

        'tours.title as product_name'
    )->orderByDesc('orders.id');

    /*
    |--------------------------------------------------------------------------
    | PAGINATION SWITCH
    |--------------------------------------------------------------------------
    */
    $orders = $paginate
        ? $query->paginate(8)->withQueryString()
        : $query->get();

    /*
    |--------------------------------------------------------------------------
    | TRANSFORM
    |--------------------------------------------------------------------------
    */
    $collection = $paginate ? $orders->getCollection() : $orders;

    $rows = [];
    $index = $paginate
        ? ($orders->currentPage() - 1) * $orders->perPage() + 1
        : 1;

    foreach ($collection as $order) {

        $productValue = 0;
        $extraValue = 0;
        $taxValue = 0;
        $discountAmount = 0;
        $subtotal = 0;

        $pricing = json_decode($order->tour_pricing, true) ?? [];
        $extras  = json_decode($order->tour_extra, true) ?? [];
        $discounts = json_decode($order->discount, true) ?? [];

        foreach ($pricing as $p) {
            $qty = $p['quantity'] ?? 0;
            $price = $p['actual_price'] ?? $p['price'] ?? 0;

            if ($qty > 0) {
                $productValue += ($p['price_type'] == 'FIXED')
                    ? $price
                    : $price * $qty;
            }
        }

        $subtotal += $productValue;

        foreach ($extras as $e) {
            $qty = $e['quantity'] ?? 0;
            $price = $e['price'] ?? 0;

            if ($qty > 0) {
                $extraValue += ($e['total_price'] ?? ($qty * $price));
            }
        }

        $subtotal += $extraValue;

        foreach ($discounts as $d) {
            $discountAmount += $d['price'] ?? 0;
        }

        $subtotal -= $discountAmount;

        if (!empty($order->tour_fees)) {
            $taxes = is_string($order->tour_fees)
                ? json_decode($order->tour_fees, true)
                : $order->tour_fees;

            foreach ($taxes as $tax) {
                $taxAmount = get_tax($subtotal, $tax['type'], 13);
                $subtotal += $taxAmount;
                $taxValue += $taxAmount;
            }
        }

        $finalTotal = $subtotal;

        $rows[] = [
            'no' => $index++,
            'order_number' => $order->order_number,
            'customer_name' => $order->first_name . ' ' . $order->last_name,
            'order_date' => $order->created_at,
            'fulfilment_date' => $order->tour_date,

            'product_price' => round(currencyConvertWithoutRound($productValue, $order->currency, 'CAD'), 2),
            'extra_amount' => round(currencyConvertWithoutRound($extraValue, $order->currency, 'CAD'), 2),
            'tax_amount' => round(currencyConvertWithoutRound($taxValue, $order->currency, 'CAD'), 2),
            'booking_fee' => round(currencyConvertWithoutRound($order->booking_fee ?? 0, $order->currency, 'CAD'), 2),
            'customer_total' => round(currencyConvertWithoutRound($finalTotal, $order->currency, 'CAD'), 2),

            'payment_status' => config('constants.payment_status')[$order->payment_status] ?? '-',

            'product_name' => $order->product_name,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | RETURN FORMAT
    |--------------------------------------------------------------------------
    */
    return $paginate
        ? ['rows' => $rows, 'pagination' => $orders]
        : $rows;
}


public function invoiceWithDetails(Request $request)
{
    $data = $this->getInvoiceWithDetailsData($request, true);

    return view('admin.reports.invoice_details', [
        'rows' => $data['rows'],
        'orders' => $data['pagination'],
        'partners' => Partner::get()
    ]);
}

public function invoiceWithDetailsExport(Request $request)
{
    $data = $this->getInvoiceWithDetailsData($request, false);

    return Excel::download(
        new \App\Exports\InvoiceWithDetailsExport($data),
        'invoice-details.xlsx'
    );
}

private function getInvoiceWithDetailsData($request, $paginate = false)
{
    $excludedStatuses = [1, 2, 6, 7];

    $startDate = $request->start_date
        ? Carbon::parse($request->start_date)->startOfDay()
        : Carbon::today()->startOfDay();

    $endDate = $request->end_date
        ? Carbon::parse($request->end_date)->endOfDay()
        : Carbon::today()->endOfDay();

    /*
    |--------------------------------------------------------------------------
    | MAPS
    |--------------------------------------------------------------------------
    */

    // tour_extra_id → addon_id
    $tourExtraMap = DB::table('addon_tour')
        ->pluck('addon_id', 'id')
        ->toArray();

    // FIXED REPORT COLUMNS
    $addonColumnMap = [
        1  => 'boat_cruise',
        2  => 'helicopter',
        4  => 'journey_falls',
        11 => 'sheraton',
        12 => 'skylon',
        21 => 'airport',
        22 => 'wine',
        25 => 'jet',
        26 => 'guide',
        27 => 'zipline',
    ];

    /*
    |--------------------------------------------------------------------------
    | QUERY
    |--------------------------------------------------------------------------
    */
    $query = DB::table('orders')
        ->leftJoin('order_tours', 'orders.id', '=', 'order_tours.order_id')
        ->leftJoin('order_customers', 'orders.id', '=', 'order_customers.order_id')
        ->leftJoin('tours', 'order_tours.tour_id', '=', 'tours.id')
        ->whereNull('orders.deleted_at')
        ->whereNotIn('orders.order_status', $excludedStatuses)
        ->whereBetween('orders.created_at', [$startDate, $endDate])
        ->groupBy('orders.id');

    // SAME FILTERS (copy-paste safe)
    if ($request->filled('order_status')) {
        $query->where('orders.order_status', $request->order_status);
    }

    if ($request->filled('payment_status')) {
        $query->where('orders.payment_status', $request->payment_status);
    }

    if ($request->filled('partner')) {
        $query->where('orders.source', $request->partner);
    }

    if ($request->action_type === 'pay_now') {
        $query->where('orders.action_name', 'book');
    } elseif ($request->action_type === 'pay_later') {
        $query->where(function ($q) {
            $q->where('orders.action_name', '!=', 'book')
              ->orWhereNull('orders.action_name');
        });
    }

    if ($request->filled('tour_start_date') && $request->filled('tour_end_date')) {
        $query->whereBetween('order_tours.tour_date', [
            Carbon::parse($request->tour_start_date)->startOfDay(),
            Carbon::parse($request->tour_end_date)->endOfDay(),
        ]);
    }

    $query->select(
        'orders.id',
        'orders.order_number',
        'orders.payment_status',
        'orders.created_at',
        'orders.currency',
        'orders.booking_fee',

        'order_tours.tour_date',
        'order_tours.tour_pricing',
        'order_tours.tour_extra',
        'order_tours.tour_fees',

        'order_customers.first_name',
        'order_customers.last_name',

        'tours.title as product_name'
    )->orderByDesc('orders.id');

    $orders = $paginate
        ? $query->paginate(8)->withQueryString()
        : $query->get();

    $collection = $paginate ? $orders->getCollection() : $orders;

    $rows = [];
    $index = $paginate
        ? ($orders->currentPage() - 1) * $orders->perPage() + 1
        : 1;

    foreach ($collection as $order) {

        $extras = json_decode($order->tour_extra, true) ?? [];

        // INIT
        $extraColumns = [];

        foreach ($addonColumnMap as $addonId => $key) {
            $extraColumns[$key] = [
                'description' => '*',
                'price' => '*',
                'tax' => '*',
                'fee' => '*',
                'total' => 0,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | MAP EXTRAS
        |--------------------------------------------------------------------------
        */
        foreach ($extras as $e) {

            $tourExtraId = $e['tour_extra_id'] ?? null;

            if (!$tourExtraId || !isset($tourExtraMap[$tourExtraId])) {
                continue;
            }

            $addonId = $tourExtraMap[$tourExtraId];

            if (!isset($addonColumnMap[$addonId])) {
                continue;
            }

            $key = $addonColumnMap[$addonId];

            $price = $e['price'] ?? 0;
            $qty   = $e['quantity'] ?? 1;
            $total = $e['total_price'] ?? ($price * $qty);

            $extraColumns[$key] = [
                'description' => $e['label'],
                'price' => round(currencyConvertWithoutRound($price, $order->currency, 'CAD'), 2),
                'tax' => '*',
                'fee' => '*',
                'total' => round(currencyConvertWithoutRound($total, $order->currency, 'CAD'), 2),
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | MAIN CALCULATION (CAD)
        |--------------------------------------------------------------------------
        */
        $productValue = 0;
        $extraValue = 0;
        $taxValue = 0;

        foreach ($extras as $e) {
            $extraValue += $e['total_price'] ?? 0;
        }

        if (!empty($order->tour_fees)) {
            $taxes = json_decode($order->tour_fees, true) ?? [];
            foreach ($taxes as $tax) {
                $taxValue += $tax['price'] ?? 0;
            }
        }

        $customerTotal = $productValue + $extraValue + $taxValue;

        /*
        |--------------------------------------------------------------------------
        | FINAL ROW
        |--------------------------------------------------------------------------
        */
        $row = [
            'no' => $index++,
            'order_number' => $order->order_number,
            'customer_name' => $order->first_name . ' ' . $order->last_name,
            'order_date' => $order->created_at,
            'fulfilment_date' => $order->tour_date,
            'customer_total' => round(currencyConvertWithoutRound($customerTotal, $order->currency, 'CAD'), 2),
            'payment_status' => $order->payment_status == 2 ? 'Yes' : 'No',
            'product_name' => $order->product_name,
        ];

        foreach ($extraColumns as $key => $extra) {
            $row[$key.'_desc'] = $extra['description'];
            $row[$key.'_price'] = $extra['price'];
            $row[$key.'_tax'] = $extra['tax'];
            $row[$key.'_fee'] = $extra['fee'];
            $row[$key.'_total'] = $extra['total'];
        }

        $rows[] = $row;
    }

    return $paginate
        ? ['rows' => $rows, 'pagination' => $orders]
        : $rows;
}


public function exportRevenue(Request $request)
{
    return Excel::download(
        new RevenueExport($request),
        'revenue_report_' . now()->format('Ymd_His') . '.xlsx'
    );
}

public function exportCustomer(Request $request)
{
    return Excel::download(
        new CustomerExport($request),
        'customer_report_' . now()->format('Ymd_His') . '.xlsx'
    );
}
}
