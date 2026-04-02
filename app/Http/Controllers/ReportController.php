<?php

namespace App\Http\Controllers;

use App\Exports\CustomerExport;
use App\Exports\RevenueExport;
use App\Models\Category;
use App\Models\Order;
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
    /*
    |--------------------------------------------------------------------------
    | BASE QUERY
    |--------------------------------------------------------------------------
    */
    $orderQuery = DB::table('orders')
        ->leftJoin('order_tours', 'orders.id', '=', 'order_tours.order_id')
        ->whereNull('orders.deleted_at')
        ->where('orders.order_status', '!=', 1);

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

    return view('admin.reports.overview', compact('performance'));
}


// use Illuminate\Http\Request;
//



public function revenuemain(Request $request)
{
    /*
    |--------------------------------------------------------------------------
    | DATE FILTER (DEFAULT TODAY)
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
    | BASE QUERY
    |--------------------------------------------------------------------------
    */
    $query = DB::table('orders')
        ->leftJoin('order_tours', 'orders.id', '=', 'order_tours.order_id')
        ->leftJoin('tours', 'order_tours.tour_id', '=', 'tours.id')
        ->leftJoin('order_customers', 'orders.id', '=', 'order_customers.order_id')
        ->whereNull('orders.deleted_at')
        ->whereBetween('orders.created_at', [$startDate, $endDate]);

    /*
    |--------------------------------------------------------------------------
    | FILTERS
    |--------------------------------------------------------------------------
    */
    if ($request->filled('payment_status')) {
        $query->where('orders.payment_status', $request->payment_status);
    }

    if ($request->action_type === 'pay_now') {
        $query->where('orders.action_name', 'book');
    } elseif ($request->action_type === 'pay_later') {
        $query->where(function ($q) {
            $q->where('orders.action_name', '!=', 'book')
              ->orWhereNull('orders.action_name');
        });
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
            'order_customers.first_name as customer_first_name',
            'order_customers.last_name as customer_last_name',

            'orders.total_amount',
            'orders.booked_amount',
            'orders.currency',

            'orders.booking_fee',
            // 'orders.custom_fee',
            // 'orders.surge_charge',
            // 'orders.cc_surcharge',
            // 'orders.platform_fee',
            // 'orders.commission',
            // 'orders.tax',

            'order_tours.number_of_guests as pax',
            'orders.total_amount',
            // 'orders.adjustment',
            // 'orders.extra_value',
            'order_tours.tour_date as tour_date',



            'order_customers.promo_code',
            'order_customers.promo_code',

            // 'orders.cc_payment',
            // 'orders.cash_payment',
            // 'orders.free_payment',
            // 'orders.other_refund',

            'orders.payment_method',
            // 'orders.gateway',
            // 'orders.gateway_type',

            'order_customers.instructions',
            // 'orders.heard_about',

            'tours.title as product_name'
            
        )
        ->orderByDesc('orders.id')
        ->paginate(8)->withQueryString();

    /*
    |--------------------------------------------------------------------------
    | CATEGORY MAP (MANY TO MANY)
    |--------------------------------------------------------------------------
    */
    $categoryMap = DB::table('category_tour')
        ->leftJoin('categories', 'categories.id', '=', 'category_tour.category_id')
        ->select('category_tour.tour_id', DB::raw('GROUP_CONCAT(categories.name) as categories'))
        ->groupBy('category_tour.tour_id')
        ->pluck('categories', 'tour_id');

    /*
    |--------------------------------------------------------------------------
    | FINAL FORMAT
    |--------------------------------------------------------------------------
    */
    $orders->getCollection()->transform(function ($order) use ($categoryMap) {

        $order->balance = ($order->total_amount ?? 0) - ($order->paid_amount ?? 0);

        $order->net_sales =
            ($order->total_amount ?? 0)
            - ($order->commission ?? 0)
            - ($order->tax ?? 0);

        $order->all_paid = $order->balance <= 0 ? 'Yes' : 'No';

        $order->category = $categoryMap[$order->tour_id ?? 0] ?? '-';

        return $order;
    });

    return view('admin.reports.revenue', compact('orders'));
}


public function revenue(Request $request)
{
    /*
    |--------------------------------------------------------------------------
    | DATE FILTER (DEFAULT TODAY)
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
    | BASE QUERY
    |--------------------------------------------------------------------------
    */
    $query = DB::table('orders')
        ->leftJoin('order_tours', 'orders.id', '=', 'order_tours.order_id')
        ->leftJoin('tours', 'order_tours.tour_id', '=', 'tours.id')
        ->leftJoin('order_customers', 'orders.id', '=', 'order_customers.order_id')
        ->whereNull('orders.deleted_at')
        ->where('orders.order_status', '!=', 1)
        ->whereBetween('orders.created_at', [$startDate, $endDate]);

    /*
    |--------------------------------------------------------------------------
    | FILTERS
    |--------------------------------------------------------------------------
    */

    // ✅ Order Status
    if ($request->filled('order_status')) {
        $query->where('orders.order_status', $request->order_status);
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

            'order_customers.first_name as customer_first_name',
            'order_customers.last_name as customer_last_name',

            'orders.total_amount',
            'orders.booked_amount',
            'orders.currency',

            'orders.booking_fee',
            // 'orders.tax',
            // 'orders.commission',

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

    /*
    |--------------------------------------------------------------------------
    | FINAL FORMAT + CURRENCY CONVERSION (CAD)
    |--------------------------------------------------------------------------
    */
    $orders->getCollection()->transform(function ($order) use ($categoryMap) {

        // ✅ Convert all money to CAD
        $total = currencyConvertWithoutRound($order->total_amount, $order->currency, 'CAD');
        $paid  = currencyConvertWithoutRound($order->booked_amount ?? 0, $order->currency, 'CAD');
        $tax   = currencyConvertWithoutRound($order->tax ?? 0, $order->currency, 'CAD');
        $commission = currencyConvertWithoutRound($order->commission ?? 0, $order->currency, 'CAD');

        // ✅ Calculations
        $order->total_amount = round($total, 2);
        $order->booked_amount = round($paid, 2);

        $order->balance = round($total - $paid, 2);

        $order->net_sales = round(
            $total - $commission - $tax,
            2
        );

        $order->all_paid = $order->balance <= 0 ? 'Yes' : 'No';

        // ✅ Category
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
    ->where('orders.order_status', '!=', 1)
    ->whereBetween('orders.created_at', [$startDate, $endDate]);

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

$customers = $customers->select(
        'orders.order_number',
        'orders.created_at as booking_date',
        'order_tours.tour_date as fulfilment_date',

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

    return view('admin.reports.revenue', compact('orders', 'customers'));
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
