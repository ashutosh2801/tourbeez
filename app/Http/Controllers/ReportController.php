<?php

namespace App\Http\Controllers;

use App\Exports\CustomerExport;
use App\Exports\InvoiceWithDetailsExport;
use App\Exports\OrderPriceScheduleExport;
use App\Exports\PriceScheduleExport;
use App\Exports\RevenueExport;
use App\Models\BusinessExpense;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\Partner;
use App\Models\Tour;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{

public function overview(Request $request)
{
    $excludedStatuses = [1, 2, 6, 7];
    $excludedPaymentSources = excluded_payment_sources(); 

    /*
    |--------------------------------------------------------------------------
    | DATE FILTER
    |--------------------------------------------------------------------------
    */
    /*
    |--------------------------------------------------------------------------
    | BASE QUERY
    |--------------------------------------------------------------------------
    */
    $orderQuery = DB::table('orders')
        
        ->leftJoin('order_tours', 'orders.id', '=', 'order_tours.order_id')
        ->whereNull('orders.deleted_at')
        ->whereNotIn('orders.order_status', $excludedStatuses);


    /*
    |--------------------------------------------------------------------------
    | CHECK IF ANY FILTER IS APPLIED
    |--------------------------------------------------------------------------
    */

        $selectedProducts = Tour::whereIn(
            'id',
            (array)$request->product
        )->get(['id','title']);

        $excludedProducts = Tour::whereIn(
            'id',
            (array)$request->exclude_product
        )->get(['id','title']);
    $hasFilter = $request->filled('booking_date')
        || $request->filled('tour_date')
        || $request->filled('product')
        || $request->filled('order_status')
        || $request->filled('payment_status')
        || $request->filled('partner')
        || $request->filled('action_type')
        || $request->filled('exclude_product');

     if (!$hasFilter) {
        return view('admin.reports.overview', [
                'performance' => [
                    'total_orders' => 0,
                    'gross_sales' => 0,
                    'payment_received' => 0,
                    'pending_amount' => 0,
                    'refund' => 0,
                    'net_sales' => 0,
                ],
                'partners' => Partner::get(),
                'selectedProducts' => $selectedProducts,
                'excludedProducts' => $excludedProducts
            ]);
    }


    /*
    |--------------------------------------------------------------------------
    | BOOKING DATE FILTER (DEFAULT = LAST 7 DAYS)
    |--------------------------------------------------------------------------
    */
    if ($request->filled('booking_date')) {

        try {
            [$start, $end] = explode(' - ', $request->booking_date);

            $orderQuery->whereBetween('orders.created_at', [
                Carbon::parse($start)->startOfDay(),
                Carbon::parse($end)->endOfDay(),
            ]);

        } catch (\Exception $e) {
            // fail silently
        }

    }


    /*
    |--------------------------------------------------------------------------
    | TOUR DATE FILTER
    |--------------------------------------------------------------------------
    */
    if ($request->filled('tour_date')) {

        try {
            [$start, $end] = explode(' - ', $request->tour_date);

            $orderQuery->whereBetween('order_tours.tour_date', [
                $start,
                $end,
            ]);

        } catch (\Exception $e) {
            // fail silently
        }
    }


    /*
    |--------------------------------------------------------------------------
    | OTHER FILTERS
    |--------------------------------------------------------------------------
    */
    // if ($request->filled('product')) {
    //     $orderQuery->where('order_tours.tour_id', $request->product);
    // }

      if ($products = $request->input('product')) {

            $products = array_filter((array)$products);

            if (!empty($products)) {

                $orderQuery->whereIn('orders.id', function ($q) use ($products) {

                    $q->select('order_id')
                      ->from('order_tours')
                      ->whereNull('deleted_at')
                      ->whereIn('tour_id', $products);

                });

            }
        }

        if ($excludeProducts = $request->input('exclude_product')) {

        $excludeProducts = array_filter((array)$excludeProducts);

        if (!empty($excludeProducts)) {

            $orderQuery->whereNotIn('orders.id', function ($q) use ($excludeProducts) {

                $q->select('order_id')
                  ->from('order_tours')
                  ->whereNull('deleted_at')
                  ->whereIn('tour_id', $excludeProducts);

            });

        }
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
        ->select('orders.id', 'orders.currency', 'orders.source')
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
        ->whereNull('deleted_at')
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

        $isExcludedFromPayment = in_array(
            strtolower($order->source),
            array_map('strtolower', $excludedPaymentSources)
        );
        $orderPayments = $payments[$order->id] ?? collect();

        $excludedCommissionPayment = 0;

         if ($isExcludedFromPayment) {


            $excludedCommissionPayment = $orderPayments
                    ->where('payment_type', 'EXCLUDED')
                    ->sum('amount');

            // $finalTotal = 0;
            // $paid = 0;
            // $balance = 0;
            // $refundAmount = 0;

        }

        // } else {

        $tours = $orderTours[$order->id] ?? collect();
        

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
                    $productValue += (isset($p['price_type']) && $p['price_type'] == 'FIXED')
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
                if($taxes){
                    foreach ($taxes as $tax) {
                        $taxAmount = get_tax($subtotal, $tax['type'], 13);
                        $subtotal += $taxAmount;
                    }
                }
                
            }

            $finalTotal += $subtotal;
        }

        // $finalTotal = $finalTotal - $excludedCommissionPayment;

        // // Payments
        // $totalPaid = $orderPayments->where('status', 'succeeded')->sum('amount')
        //     - $orderPayments->where('status', 'refunded')->sum('amount');
        // $totalPaid = $totalPaid - $excludedCommissionPayment;

        // $promoPayment = $orderPayments
        //     ->where('collection_type', 'Outside')
        //     ->where('payment_type', 'PROMO_CODE')
        //     ->sum('amount');

        // $paid = $totalPaid - $promoPayment;

        // $balance = $finalTotal - $paid;

        $customerTotal = $finalTotal;
        $excludedBalance = 0;

        if ($isExcludedFromPayment) {

            $totalPaymentAmount = $orderPayments
                ->where('status', 'succeeded')
                ->sum('amount')
                - $excludedCommissionPayment;

            $customerTotal = $finalTotal - $excludedCommissionPayment;

            if (round($totalPaymentAmount, 2) < round($customerTotal, 2)) {
                $excludedBalance = $customerTotal - $totalPaymentAmount;
                $customerTotal = $totalPaymentAmount;
            }
        }

        $totalPaid = $orderPayments
            ->where('status', 'succeeded')
            ->sum('amount')
            - $orderPayments->where('status', 'refunded')->sum('amount');

        $promoPayment = $orderPayments
            ->where('collection_type', 'Outside')
            ->where('payment_type', 'PROMO_CODE')
            ->sum('amount');

        $paid = $totalPaid - $promoPayment;

        if ($isExcludedFromPayment) {
            $paid -= $excludedCommissionPayment;
        }

        $balance = $customerTotal - $paid;

        // Refund
        $refundAmount = $orderPayments->sum('refund_amount');
    // }

        // Convert to CAD


        $gross += currencyConvertWithoutRound($customerTotal, $order->currency, 'CAD');
        $totalPaidAll += currencyConvertWithoutRound($paid, $order->currency, 'CAD');
        // $totalBalanceAll += currencyConvertWithoutRound($balance, $order->currency, 'CAD');
        $totalBalanceAll += ($customerTotal == 0)? 0 : currencyConvertWithoutRound(
                                $balance + $excludedBalance - $isExcludedFromPayment,
                                $order->currency,
                                'CAD'
                            );
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

    

    return view('admin.reports.overview', compact('performance', 'partners', 'selectedProducts','excludedProducts' ));
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
    $excludedPaymentSources = array_map('strtolower', excluded_payment_sources());
    /*
|--------------------------------------------------------------------------
| CHECK IF ANY FILTER IS APPLIED
|--------------------------------------------------------------------------
    */
    $hasFilter = $request->filled('booking_date')
        || $request->filled('tour_date')
        || $request->filled('product')
        || $request->filled('order_status')
        || $request->filled('payment_status')
        || $request->filled('partner')
        || $request->filled('action_type')
        || $request->filled('exclude_product');

    /*
    |--------------------------------------------------------------------------
    | DEFAULT BOOKING DATE (LAST 7 DAYS)
    |--------------------------------------------------------------------------
    */
    $selectedProducts = Tour::whereIn(
            'id',
            (array)$request->product
        )->get(['id','title']);

        $excludedProducts = Tour::whereIn(
            'id',
            (array)$request->exclude_product
        )->get(['id','title']);
    if (!$hasFilter) {

        // if (!$hasFilter) {
        $orders = new LengthAwarePaginator([], 0, 8);
        $customers = new LengthAwarePaginator([], 0, 8);
        $partners = Partner::get();

            return view('admin.reports.revenue', compact('orders', 'customers', 'partners', 'selectedProducts', 'excludedProducts'));
        // }
        $request->merge([
            'booking_date' => now()->subDays(7)->format('Y-m-d') . ' - ' . now()->format('Y-m-d')
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | PARSE BOOKING DATE
    |--------------------------------------------------------------------------
    */
    $startDate = null;
    $endDate = null;

    if ($request->filled('booking_date')) {
        try {
            [$start, $end] = explode(' - ', $request->booking_date);

            $startDate = Carbon::parse($start)->startOfDay();
            $endDate   = Carbon::parse($end)->endOfDay();
        } catch (\Exception $e) {}
    }

    /*
    |--------------------------------------------------------------------------
    | BASE QUERY
    |--------------------------------------------------------------------------
    */

    
        


    $query = DB::table('orders')
         ->leftJoin('order_tours', function ($join) {
            $join->on('orders.id', '=', 'order_tours.order_id')
                 ->whereNull('order_tours.deleted_at');
        })
        ->leftJoin('tours', 'order_tours.tour_id', '=', 'tours.id')
        ->leftJoin('order_customers', 'orders.id', '=', 'order_customers.order_id')
        ->whereNull('orders.deleted_at')
        ->whereNotIn('orders.order_status', $excludedStatuses);



    /*
    |--------------------------------------------------------------------------
    | FILTERS
    |--------------------------------------------------------------------------
    */

    // ✅ Order Status

    if ($startDate && $endDate) {
        $query->whereBetween('orders.created_at', [$startDate, $endDate]);
    }

    // if ($product = $request->input('product')) {
    //     $query->where('order_tours.tour_id', $product);
    // }
      if ($products = $request->input('product')) {

        $products = array_filter((array)$products);

        if (!empty($products)) {

            $query->whereIn('orders.id', function ($q) use ($products) {

                $q->select('order_id')
                  ->from('order_tours')
                  ->whereNull('deleted_at')
                  ->whereIn('tour_id', $products);

            });

        }
    }

    if ($excludeProducts = $request->input('exclude_product')) {

    $excludeProducts = array_filter((array)$excludeProducts);

    if (!empty($excludeProducts)) {

        $query->whereNotIn('orders.id', function ($q) use ($excludeProducts) {

            $q->select('order_id')
              ->from('order_tours')
              ->whereNull('deleted_at')
              ->whereIn('tour_id', $excludeProducts);

        });

    }
}

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
    if ($request->filled('tour_date')) {
        try {
            [$start, $end] = explode(' - ', $request->tour_date);

            $query->whereBetween('order_tours.tour_date', [$start, $end]);
        } catch (\Exception $e) {}
    }

    
    // dd( $request->tour_start_date, Carbon::parse($request->tour_start_date)->startOfDay());

    /*
    |--------------------------------------------------------------------------
    | FETCH DATA (PAGINATED)
    |--------------------------------------------------------------------------
    */

    apply_report_sorting($query, $request); 
    $orders = $query
        ->select(
            'orders.id',
            'orders.order_number',
            'orders.order_status',
            'orders.payment_status',
            'orders.source',
            'orders.created_by',
            DB::raw('DATE(orders.created_at) as booking_date'),
            'order_tours.tour_date as fulfilment_date',
            'order_tours.tour_id',

            // ✅ REQUIRED FOR CALCULATION
            'order_tours.tour_pricing',
            'order_tours.tour_extra',
            'order_tours.discount',
            'order_tours.deleted_at',

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
        // ->orderByDesc('order_tours.tour_date')
        ->paginate(20)
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
        ->whereNull('deleted_at')
        ->get()
        ->groupBy('order_id');

    /*
    |--------------------------------------------------------------------------
    | FINAL FORMAT + CURRENCY CONVERSION (CAD)
    |--------------------------------------------------------------------------
    */
    $orders->getCollection()->transform(function ($order) use ($payments, $categoryMap, $excludedPaymentSources) {

        $isExcludedFromPayment = in_array(
            strtolower($order->source),
            $excludedPaymentSources
        );

        // $finalTotal = 0;
        // $totalTax = 0;
        // $productValue = 0;
        // $extraValue = 0;

        
        
        // $subtotal2 = 0;

        $finalTotal = 0;
        $totalTax = 0;
        $productValue = 0;
        $extraValue = 0;
        $discountAmount = 0;
        $paid = 0;
        $balance = 0;
        $subtotal2 = 0;
        $pricing = json_decode($order->tour_pricing, true) ?? [];
        $extras  = json_decode($order->tour_extra, true) ?? [];
        $discounts = json_decode($order->discount, true) ?? [];

        // if (!$isExcludedFromPayment) {
            

            // ✅ Pricing
            foreach ($pricing as $p) {
                $qty = $p['quantity'] ?? 0;
                $actual_price = $p['actual_price'] ?? $p['price'] ?? 0;

                if ($qty > 0) {
                    $productValue += (isset($p['price_type']) && $p['price_type'] == 'FIXED')
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



            // ✅ Excluded payment handling (same as invoice report)
$customerTotal = $finalTotal;
$excludedBalance = 0;
$hideSuplierExcludeExtraCost = false;

$orderPayments = $payments[$order->id] ?? collect();
$excludedCommissionPayment = 0;

if ($isExcludedFromPayment) {

    $excludedCommissionPayment = $orderPayments
        ->where('payment_type', 'EXCLUDED')
        ->sum('amount');

    $totalPaymentAmount = $orderPayments
        ->where('status', 'succeeded')
        ->sum('amount')
        - $excludedCommissionPayment;

    $customerTotal = $finalTotal - $excludedCommissionPayment;

    if (round($totalPaymentAmount, 2) < round($customerTotal, 2)) {
        $excludedBalance = $customerTotal - $totalPaymentAmount;
        $customerTotal = $totalPaymentAmount;
        $hideSuplierExcludeExtraCost = true;
    }
}
// ✅ Payments
$totalPaid = $orderPayments
    ->where('status', 'succeeded')
    ->sum('amount')
    - $orderPayments->where('status', 'refunded')->sum('amount');

$promoPayment = $orderPayments
    ->where('collection_type', 'Outside')
    ->where('payment_type', 'PROMO_CODE')
    ->sum('amount');

$paid = $totalPaid - $promoPayment;

if ($isExcludedFromPayment) {
    $paid -= $excludedCommissionPayment;
}

$balance = $customerTotal - $paid;
            // dd($check,$taxes, $taxAmount, $finalTotal, $subtotal2, $discountAmount, $extraValue, $productValue);

            // ✅ Payments
            // $orderPayments = $payments[$order->id] ?? collect();

            // $totalPaid = $orderPayments->where('status', 'succeeded')->sum('amount')
            //     - $orderPayments->where('status', 'refunded')->sum('amount');

            // $promoPayment = $orderPayments
            //     ->where('collection_type', 'Outside')
            //     ->where('payment_type', 'PROMO_CODE')
            //     ->sum('amount');

            // $paid = $totalPaid - $promoPayment;

            // $balance = $finalTotal - $paid;
            // ✅ Payments
            // $totalPaid = $orderPayments
            //     ->where('status', 'succeeded')
            //     ->sum('amount')
            //     - $orderPayments->where('status', 'refunded')->sum('amount');

            // $promoPayment = $orderPayments
            //     ->where('collection_type', 'Outside')
            //     ->where('payment_type', 'PROMO_CODE')
            //     ->sum('amount');

            // $paid = $totalPaid - $promoPayment;

            // if ($isExcludedFromPayment) {
            //     $paid -= $excludedCommissionPayment;
            // }

            // $balance = $customerTotal - $paid;

        // }

        // ✅ Excluded payment handling (same as invoice report)
        $customerTotal = $finalTotal;
        $excludedBalance = 0;
        $hideSuplierExcludeExtraCost = false;

        $orderPayments = $payments[$order->id] ?? collect();
        $excludedCommissionPayment = 0;

        if ($isExcludedFromPayment) {

            $excludedCommissionPayment = $orderPayments
                ->where('payment_type', 'EXCLUDED')
                ->sum('amount');

            $totalPaymentAmount = $orderPayments
                ->where('status', 'succeeded')
                ->sum('amount')
                - $excludedCommissionPayment;

            $customerTotal = $finalTotal - $excludedCommissionPayment;

            if (round($totalPaymentAmount, 2) < round($customerTotal, 2)) {
                $excludedBalance = $customerTotal - $totalPaymentAmount;
                $customerTotal = $totalPaymentAmount;
                $hideSuplierExcludeExtraCost = true;
            }
        }

        // ✅ Convert to CAD
        $order->total_amount_converted = round(currencyConvertWithoutRound($customerTotal, $order->currency, 'CAD'), 2);
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

        $adult = 0;
        $child = 0;
        $infant = 0;
        $other = 0;

        foreach ($pricing as $p) {
            $qty = (int) ($p['quantity'] ?? 0);
            $label = strtolower($p['label'] ?? '');
            $priceType = $p['price_type'] ?? '';

            // ✅ FIXED → treat as Adults
            if ($priceType === 'FIXED') {
                $adult += $qty;
                continue;
            }

            if (str_contains($label, 'adult')) {
                $adult += $qty;
            } elseif (str_contains($label, 'child')) {
                $child += $qty;
            } elseif (str_contains($label, 'infant')) {
                $infant += $qty;
            } else {
                $other += $qty;
            }
        }

        // attach to order
        $order->adult = $adult;
        $order->child = $child;
        $order->infant = $infant;
        $order->other = $other;

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
    ->whereNotIn('orders.order_status', $excludedStatuses)->groupBy('orders.id');

if ($startDate && $endDate) {
    $customers->whereBetween('orders.created_at', [$startDate, $endDate]);
}
// SAME FILTERS (IMPORTANT)
if ($request->filled('payment_status')) {
    $customers->where('orders.payment_status', $request->payment_status);
}
 if ($request->filled('order_status')) {
        $customers->where('orders.order_status', $request->order_status);
    }



if ($products = $request->input('product')) {

        $products = array_filter((array)$products);

        if (!empty($products)) {

            $customers->whereIn('orders.id', function ($q) use ($products) {

                $q->select('order_id')
                  ->from('order_tours')
                  ->whereNull('deleted_at')
                  ->whereIn('tour_id', $products);

            });

        }
    }

    if ($excludeProducts = $request->input('exclude_product')) {

    $excludeProducts = array_filter((array)$excludeProducts);

    if (!empty($excludeProducts)) {

        $customers->whereNotIn('orders.id', function ($q) use ($excludeProducts) {

            $q->select('order_id')
              ->from('order_tours')
              ->whereNull('deleted_at')
              ->whereIn('tour_id', $excludeProducts);

        });

    }
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
 if ($request->filled('tour_date')) {
    try {
        [$start, $end] = explode(' - ', $request->tour_date);

        $customers->whereBetween('order_tours.tour_date', [$start, $end]);
    } catch (\Exception $e) {}
}
apply_report_sorting($customers, $request); 
$customers = $customers->select(
        'orders.order_number',
        DB::raw('DATE(orders.created_at) as booking_date'),
        'order_tours.tour_date as fulfilment_date',

        'order_customers.id',
        'order_customers.first_name',
        'order_customers.last_name',
        'order_customers.email',
        'order_customers.phone',
        'order_customers.instructions',

        'order_customers.promo_code'
    )
    // ->orderByDesc('orders.id')
    ->paginate(20, ['*'], 'customer_page') // IMPORTANT (separate pagination)
    ->withQueryString();

    $partners = Partner::get();
    

    return view('admin.reports.revenue', compact('orders', 'customers', 'partners', 'selectedProducts', 'excludedProducts'));
}

public function invoice(Request $request)
{
    
    $data = $this->getInvoiceData($request, true);

    $selectedProducts = Tour::whereIn(
            'id',
            (array)$request->product
        )->get(['id','title']);

        $excludedProducts = Tour::whereIn(
            'id',
            (array)$request->exclude_product
        )->get(['id','title']);

    return view('admin.reports.invoice', [
        'rows' => $data['rows'],
        'orders' => $data['pagination'],
        'partners' => Partner::get(),
        'selectedProducts' => $selectedProducts,
        'excludedProducts' => $excludedProducts
    ]);
}

public function invoiceExport(Request $request)
{
    ini_set('memory_limit', '1024M');
    $data = $this->getInvoiceData($request);

    return \Maatwebsite\Excel\Facades\Excel::download(
        new \App\Exports\InvoiceExport($data),
        'invoice-report' . now()->format('Ymd_His') . '.xlsx'
    );
}


public function getInvoiceData($request, $paginate = false)
{
    $excludedStatuses = [1, 2, 6, 7];
    $excludedPaymentSources = array_map('strtolower', excluded_payment_sources());

    $hasFilter = $request->filled('booking_date')
    || $request->filled('tour_date')
    || $request->filled('product')
    || $request->filled('order_status')
    || $request->filled('payment_status')
    || $request->filled('partner')
    || $request->filled('action_type')
    || $request->filled('exclude_product');

    if (!$hasFilter) {
        return $paginate
            ? [
                'rows' => [],
                'pagination' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20)
            ]
            : [];
    }

    $query = DB::table('orders')
         ->leftJoin('order_tours', function ($join) {
            $join->on('orders.id', '=', 'order_tours.order_id')
                 ->whereNull('order_tours.deleted_at');
        })
        ->leftJoin('order_customers', 'orders.id', '=', 'order_customers.order_id')
        ->leftJoin('order_payments', 'orders.id', '=', 'order_payments.order_id')
        ->leftJoin('tours', 'order_tours.tour_id', '=', 'tours.id')
        ->whereNull('orders.deleted_at')
        ->whereNotIn('orders.order_status', $excludedStatuses)
        ->groupBy('orders.id');

    // Filters

    $startDate = null;
    $endDate = null;

    if ($request->filled('booking_date')) {
        try {
            [$start, $end] = explode(' - ', $request->booking_date);

            $startDate = Carbon::parse($start)->startOfDay();
            $endDate   = Carbon::parse($end)->endOfDay();
            if ($startDate && $endDate) {
                $query->whereBetween('orders.created_at', [$startDate, $endDate]);
            }
        } catch (\Exception $e) {}

    }

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

    // if ($product = $request->input('product')) {
    //     $query->where('order_tours.tour_id', $product);
    // }

    // if ($product = $request->input('product')) {
    //         $product = array_filter((array)$product);
    //         if (!empty($product)) {
    //             $query->whereHas('orderTours', function ($q) use ($product) {
    //                 $q->whereIn('tour_id', $product);
    //             });
    //         }
    //     }

    if ($products = $request->input('product')) {

        $products = array_filter((array)$products);

        if (!empty($products)) {

            $query->whereIn('orders.id', function ($q) use ($products) {

                $q->select('order_id')
                  ->from('order_tours')
                  ->whereNull('deleted_at')
                  ->whereIn('tour_id', $products);

            });

        }
    }

    if ($excludeProducts = $request->input('exclude_product')) {

    $excludeProducts = array_filter((array)$excludeProducts);

    if (!empty($excludeProducts)) {

        $query->whereNotIn('orders.id', function ($q) use ($excludeProducts) {

            $q->select('order_id')
              ->from('order_tours')
              ->whereNull('deleted_at')
              ->whereIn('tour_id', $excludeProducts);

        });

    }
}

        // if ($excludeProducts = $request->input('exclude_product')) {

        //     $excludeProducts = array_filter((array)$excludeProducts);

        //     if (!empty($excludeProducts)) {

        //         $query->whereDoesntHave('orderTours', function ($q) use ($excludeProducts) {

        //             $q->whereIn('tour_id', $excludeProducts);

        //         });

        //     }

        // }



    // if ($request->filled('tour_start_date') && $request->filled('tour_end_date')) {
    //     $query->whereBetween('order_tours.tour_date', [
    //         $request->tour_start_date,
    //         $request->tour_end_dates,
    //     ]);
    // }

    if ($request->filled('tour_date')) {

        try {
            [$start, $end] = explode(' - ', $request->tour_date);

            $query->whereBetween('order_tours.tour_date', [
                $start,
                $end,
            ]);

        } catch (\Exception $e) {
            // fail silently
        }
    }
    apply_report_sorting($query, $request); 

    $query->select(
        'orders.id',
        'orders.order_number',
        'orders.payment_status',
        'orders.created_at',
        'orders.currency',
        'orders.booking_fee',
        'orders.source',

        'order_tours.tour_date',
        'order_tours.tour_pricing',
        'order_tours.tour_extra',
        'order_tours.discount',
        'order_tours.tour_fees',

        'order_customers.first_name',
        'order_customers.last_name',

        'tours.title as product_name'
    );

    /*
    |--------------------------------------------------------------------------
    | PAGINATION SWITCH
    |--------------------------------------------------------------------------
    */


    $orders = $paginate
        ? $query->paginate(20)->withQueryString()
        : $query->get();

    $orderCollection = $paginate ? $orders->getCollection() : $orders;

    $orderIds = $orderCollection->pluck('id');
    // $orderIds = collect($orders)->pluck('id');

    $payments = DB::table('order_payments')
        ->whereIn('order_id', $orderIds)
        ->whereNull('deleted_at')
        ->get()
        ->groupBy('order_id');

    

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

        $isExcludedFromPayment = in_array(
            strtolower($order->source ?? ''),
            $excludedPaymentSources
        );

        $productValue = 0;
        $extraValue = 0;
        $taxValue = 0;
        $discountAmount = 0;
        $subtotal = 0;
        $totalPaid = 0;
        $finalTotal = 0;

        $pricing = json_decode($order->tour_pricing, true) ?? [];
        $extras  = json_decode($order->tour_extra, true) ?? [];
        $discounts = json_decode($order->discount, true) ?? [];


        // foreach ($pricing as $p) {
        //     $qty = $p['quantity'] ?? 0;
        //     $price = $p['actual_price'] ?? $p['price'] ?? 0;

        //     if ($qty > 0) {
        //         $productValue += (isset($p['price_type']) && $p['price_type'] == 'FIXED')
        //             ? $price
        //             : $price * $qty;
        //     }
        // }
        $adult = 0;
        $child = 0;
        $infant = 0;
        $other = 0;
        // if (!$isExcludedFromPayment) {
        

            foreach ($pricing as $p) {
                $qty = (int) ($p['quantity'] ?? 0);
                $label = strtolower($p['label'] ?? '');
                $priceType = $p['price_type'] ?? '';

                // FIXED → treat as Adults
                if ($priceType === 'FIXED') {
                    $adult += $qty;
                    
                } else{
                    if (str_contains($label, 'adult')) {
                    $adult += $qty;
                    } elseif (str_contains($label, 'child')) {
                        $child += $qty;
                    } elseif (str_contains($label, 'infant')) {
                        $infant += $qty;
                    } else {
                        $other += $qty;
                    }
                }

                
                $price = $p['actual_price'] ?? $p['price'] ?? 0;

                if ($qty > 0) {
                    $productValue += (isset($p['price_type']) && $p['price_type'] == 'FIXED')
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



            $orderPayments = $payments[$order->id] ?? collect();


            // total successful payments
            $totalPaid = $orderPayments
                ->where('status', 'succeeded')
                ->sum('amount');
            $refunded = $orderPayments
                ->where('status', 'refunded')
                ->sum('amount');

            // remove promo payments (if applicable)
            $promoPayment = $orderPayments
                ->where('collection_type', 'Outside')
                ->where('payment_type', 'PROMO_CODE')
                ->sum('amount');

            
            $totalPaid = $totalPaid - $refunded;
        // }
        if ($isExcludedFromPayment) {
            $productValue = 0;
            $extraValue = 0;
            $taxValue = 0;
            $discountAmount = 0;
            $subtotal = 0;
            $totalPaid = 0;
            $finalTotal = 0;

        }

        $rows[] = [
            'id' => $order->id,
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
            'total_paid' => round(currencyConvertWithoutRound($totalPaid, $order->currency, 'CAD'), 2),
            
            // 'paid' => config('constants.payment_status')[$order->payment_status] ?? '-',

            'product_name' => $order->product_name,
            'adult' => $adult,
            'child' => $child,
            'infant' => $infant,
            'other' => $other,
            'source' => $order->source ?? null,
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


    $selectedProducts = Tour::whereIn(
            'id',
            (array)$request->product
        )->get(['id','title']);

        $excludedProducts = Tour::whereIn(
            'id',
            (array)$request->exclude_product
        )->get(['id','title']);
    
    return view('admin.reports.invoice_details', [
        'rows' => $data['rows'],
        'orders' => $data['pagination'],
        'partners' => Partner::get(),
        'selectedProducts' => $selectedProducts,
        'excludedProducts' => $excludedProducts,
        'addonKeys' => collect($data['rows'][0] ?? [])
        ->keys()
        ->filter(fn($key) => str_contains($key, '_desc'))
        ->map(fn($key) => str_replace('_desc', '', $key))
        ->values()
    ]);
}

    public function invoiceWithDetailsExport(Request $request)
    {
        ini_set('memory_limit', '1024M');
        $data = $this->getInvoiceWithDetailsData($request, false);

        return Excel::download(
            new \App\Exports\InvoiceWithDetailsExport($data),
            'invoice-details' . now()->format('Ymd_His') . '.xlsx'
        );
    }


    public function getInvoiceWithDetailsData($request, $paginate = false)
    {
            $excludedStatuses = [1, 2, 6, 7];
            $excludedPaymentSources = array_map('strtolower', excluded_payment_sources());

            $hasFilter = $request->filled('booking_date')
            || $request->filled('tour_date')
            || $request->filled('product')
            || $request->filled('order_status')
            || $request->filled('payment_status')
            || $request->filled('partner')
            || $request->filled('action_type')
            || $request->filled('exclude_product');

            $addonTotals = [
                'qty' => 0,
                'price' => 0,
                'total' => 0,
            ];

            $totals = [
                'product_price'      => 0,
                'extra_amount'       => 0,
                'tax_amount'         => 0,
                'discount_amount'    => 0,
                'customer_total'     => 0,
                'exclude_total'     => 0,
                'balance_amount'     => 0,
                'transport_cost'     => 0,
                'tour_selling_price' => 0,
                'tour_extra_included_price'=> 0,
                'tour_extra_excluded_price'=> 0,
                'tour_selling_tax'   => 0,
                'net_total'          => 0,
                'profit'             => 0,
                'addonTotals'        => $addonTotals,

            ];


            if (
                        !$request->filled('booking_date')
                        && !$request->filled('tour_date')
                    ) {
                        return $paginate
                    ? [
                        'rows' => [],
                        'pagination' => new LengthAwarePaginator([], 0, 20),
                        'totals'     => $totals,
                    ]
                    : [
                        'rows' => [],
                        'pagination' => null,
                        'totals'     => $totals,
                    ];
            }

            /*
            |--------------------------------------------------------------------------
            | MAPS
            |--------------------------------------------------------------------------
            */

            // tour_extra_id → addon_id
            $tourExtraMap = DB::table('addon_tour')
                ->pluck('addon_id','id')
                ->toArray();
            // dd(DB::table('addon_tour')->get(), $tourExtraMap);
            // Load all addons only once
            $addons = DB::table('addons')
                ->whereNull('deleted_at')
                ->get()
                ->keyBy('id');

            // addon_id → safe_key (dynamic)
            $addonColumnMap = DB::table('addons')
                ->get()
                ->mapWithKeys(function ($addon) {
                    return [
                        $addon->id => \Illuminate\Support\Str::slug($addon->name, '_')
                    ];
                })
                ->toArray();

            // freeze all addon keys (IMPORTANT)
            $allAddonKeys = array_values($addonColumnMap);

            // dd($allAddonKeys);

            /*
            |--------------------------------------------------------------------------
            | QUERY
            |--------------------------------------------------------------------------
            */

            

            
            $query = DB::table('orders')
                ->leftJoin('order_tours', function ($join) {
                    $join->on('orders.id', '=', 'order_tours.order_id')
                         ->whereNull('order_tours.deleted_at');
                })
                ->leftJoin('order_customers', 'orders.id', '=', 'order_customers.order_id')
                ->leftJoin('tours', 'order_tours.tour_id', '=', 'tours.id')
                ->whereNull('orders.deleted_at')
                ->whereNotIn('orders.order_status', $excludedStatuses)
                ->leftJoin('tour_pricings as tp', 'tp.tour_id', '=', 'tours.id')
                ->whereNull('tp.deleted_at')
                ->groupBy('orders.id');

            if ($request->filled('booking_date')) {
                try {
                    [$start, $end] = explode(' - ', $request->booking_date);

                    $startDate = Carbon::parse($start)->startOfDay();
                    $endDate   = Carbon::parse($end)->endOfDay();
                    if ($startDate && $endDate) {
                        $query->whereBetween('orders.created_at', [$startDate, $endDate]);
                    }
                } catch (\Exception $e) {}

            }

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
            // if ($product = $request->input('product')) {
            //     $query->where('order_tours.tour_id', $product);
            // }
              if ($products = $request->input('product')) {

                $products = array_filter((array)$products);

                if (!empty($products)) {

                    $query->whereIn('orders.id', function ($q) use ($products) {

                        $q->select('order_id')
                          ->from('order_tours')
                          ->whereNull('deleted_at')
                          ->whereIn('tour_id', $products);

                    });

                }
            }

            if ($excludeProducts = $request->input('exclude_product')) {

            $excludeProducts = array_filter((array)$excludeProducts);

            if (!empty($excludeProducts)) {

                $query->whereNotIn('orders.id', function ($q) use ($excludeProducts) {

                    $q->select('order_id')
                      ->from('order_tours')
                      ->whereNull('deleted_at')
                      ->whereIn('tour_id', $excludeProducts);

                });

            }
        }

        if ($request->filled('tour_date')) {

            try {
                [$start, $end] = explode(' - ', $request->tour_date);

                $query->whereBetween('order_tours.tour_date', [
                    $start,
                    $end,
                ]);

            } catch (\Exception $e) {
                // fail silently
            }
        }
        apply_report_sorting($query, $request); 


        $query->select(
            'orders.id',
            'orders.tour_id',
            'orders.order_number',
            'orders.payment_status',
            'orders.created_at',
            'orders.currency',
            'orders.source',
            'orders.balance_amount',
            'orders.total_amount',
            'orders.booked_amount',
            'order_tours.tour_date',
            'order_tours.tour_extra',
            'order_tours.tour_fees',
            'order_tours.tour_pricing',
            'order_tours.discount',

            'order_customers.first_name',
            'order_customers.last_name',

            'tours.title as product_name',
            'tours.transport_cost as transport_cost',
        );

        $orders = $paginate
            ? $query->paginate(10)->withQueryString()
            : $query->get();

        $collection = $paginate ? $orders->getCollection() : $orders;

        $rows = [];
        $index = $paginate
            ? ($orders->currentPage() - 1) * $orders->perPage() + 1
            : 1;

        $tourIds = $collection->pluck('tour_id')->unique();

        /*
        |--------------------------------------------------------------------------
        | 🔥 LOAD TOUR PRICINGS (ALL LABELS)
        |--------------------------------------------------------------------------
        */
        $tourPricing = DB::table('tour_pricings')
            ->whereIn('tour_id', $tourIds)
            ->whereNull('deleted_at')
            ->get()
            ->groupBy('tour_id');

        /*
        |--------------------------------------------------------------------------
        | 🔥 LOAD TAXES
        |--------------------------------------------------------------------------
        */
        $allTaxes = DB::table('taxes_fee_tour as tft')
            ->join('taxes_fees as tf', 'tft.taxes_fee_id', '=', 'tf.id')
            ->whereIn('tft.tour_id', $tourIds)
            ->whereNull('tft.deleted_at')
            ->select(
                'tft.tour_id',
                'tf.tax_fee_type',
                'tf.tax_fee_value',
                'tf.fee_type'
            )
            ->get()
            ->groupBy('tour_id');


        // $orderPayments = DB::table('order_payments')
        // ->whereIn('order_id', $collection->pluck('id'))
        // ->whereNull('deleted_at')
        // ->orderBy('id')
        // ->get()
        // ->groupBy('order_id');

        $orderPayments = OrderPayment::whereIn('order_id', $collection->pluck('id'))
                        ->orderBy('id')
                        ->get()
                        ->groupBy('order_id');

        foreach ($collection as $order) {

            $isExcludedFromPayment = in_array(
                strtolower($order->source ?? ''),
                $excludedPaymentSources
            );


            
            $extras = json_decode($order->tour_extra, true) ?? [];

            

            /*
            |--------------------------------------------------------------------------
            | INIT ALL ADDON COLUMNS (prevents undefined errors)
            |--------------------------------------------------------------------------
            */
            $extraColumns = [];

            foreach ($allAddonKeys as $key) {
                $extraColumns[$key] = [
                    'description' => '',
                    'price' => 0,
                    'tax' => 0,
                    'fee' => 0,
                    'total' => 0,
                    'quantity' => 0,
                ];
            }

            /*
            |--------------------------------------------------------------------------
            | MAP EXTRAS
            |--------------------------------------------------------------------------
            */
            $extra_amount = 0;
            $baseExtraExcludedBase = 0;
            
            foreach ($extras as $e) {

                if($e['quantity'] < 1){
                    continue;
                }

                $tourExtraId = $e['tour_extra_id'] ?? null;
                
                $key = $addonColumnMap[$tourExtraId];

                // dd($tourExtraMap, $addons);

                // $addonId = $tourExtraMap[$tourExtraId] ?? null;
                $addon   = $tourExtraId ? ($addons[$tourExtraId] ?? null) : null;

                $price = $e['price'] ?? 0;
                $qty   = $e['quantity'] ?? 1;
                $total = $e['total_price'] ?? ($price * $qty);
                $extra_amount += $total;

                if ($addon) {

                    $baseExtraExcludedBase += currencyConvertWithoutRound(
                        ($addon->selling_price ?? 0) * $qty,
                        'CAD',
                        'CAD'
                    );

                }

                $extraColumns[$key] = [
                    // 'con' => $key,
                    'description' => $e['label'] ?? '',
                    'quantity' => $qty,
                    'price' => round(currencyConvertWithoutRound($price, $order->currency, 'CAD'), 2),
                    'tax' => 0,
                    'fee' => 0,
                    'total' => round(currencyConvertWithoutRound($total, $order->currency, 'CAD'), 2),
                ];
            }

            // dd($extraColumns, $key);
            // dd($extraColumns);
            /*
            |--------------------------------------------------------------------------
            | CALCULATION
            |--------------------------------------------------------------------------
            */
            $extraValue = 0;
            $taxValue = 0;

            foreach ($extras as $e) {
                $extraValue += $e['total_price'] ?? 0;
            }

            $tax_amount = 0;
            if (!empty($order->tour_fees)) {
                $taxes = json_decode($order->tour_fees, true) ?? [];
                foreach ($taxes as $tax) {
                    $taxValue += $tax['price'] ?? 0;
                    $tax_amount += $taxValue;
                }
            }

            $pricing = json_decode($order->tour_pricing, true) ?? [];

            $adult = 0;
            $child = 0;
            $infant = 0;
            $senior = 0;
            $other = 0;
            $product_price = 0;
            $transportCost = 0;

            foreach ($pricing as $p) {
                $qty = (int) ($p['quantity'] ?? 0);
                $label = strtolower($p['label'] ?? '');
                $priceType = $p['price_type'] ?? '';
                // $product_price += $p['total_price'] ?? ($p['price'] * $qty) ?? 0;

                // FIXED → treat as Adults
                if ($priceType === 'FIXED') {
                    $adult += $qty;
                    // continue;
                } else{
                     if (str_contains($label, 'adult')) {
                        $adult += $qty;
                    } elseif (str_contains($label, 'child')) {
                        $child += $qty;
                    } elseif (str_contains($label, 'infant')) {
                        $infant += $qty;
                    } elseif (str_contains($label, 'senior')) {
                        $senior += $qty;
                    } else {
                        $other += $qty;
                    }
                }

               

                $price = $p['actual_price'] ?? $p['price'] ?? 0;

                if ($qty > 0) {
                    $product_price += (isset($p['price_type']) && $p['price_type'] == 'FIXED')
                        ? $price
                        : $price * $qty;
                }
            }

            $discount_amount = 0;
            $discounts = json_decode($order->discount, true) ?? [];
            foreach ($discounts as $d) {
                $discount_amount += $d['price'] ?? 0;
            }

            $taxesfees = json_decode($order->tour_fees, true) ?? [];


            $subtotal = ($product_price + $extraValue) - $discount_amount;
            if( $taxesfees ){

                                                
            foreach ($taxesfees as $key => $item)  {

                $price      = get_tax($subtotal, $item['type'], 13);
                $tax        = $price ?? 0;
                $subtotal   = $subtotal + $tax; 
                $tax_amount = $tax;
                }
            }
            // dd($tax_amount, currencyConvertWithoutRound($tax_amount, $order->currency, 'CAD'));
            // $tax_amount = currencyConvertWithoutRound($tax_amount, $order->currency, 'CAD');

            $excludedCommissionPayment = 0;
            $totalPaymentAmount = 0;
            $hideSuplierExcludeExtraCost = false;
            $excludedBalance = 0;


            $payments = $orderPayments[$order->id] ?? collect();

            $excludedCommissionPayment = $payments
                ->where('payment_type', 'EXCLUDED')
                ->sum('amount');

            $totalRefundAmount = $payments
                ->where('status', 'refunded')
                ->sum('amount');

            $totalPaymentAmount = $payments
                ->where('status', 'succeeded')
                ->sum('amount') - $excludedCommissionPayment-$totalRefundAmount;

            
            // $customerTotal = ($product_price + $extraValue + $tax_amount) - $discount_amount;

            if ($isExcludedFromPayment) {

                // $payments = $orderPayments[$order->id] ?? collect();

                // $excludedCommissionPayment = $payments
                //     ->where('payment_type', 'EXCLUDED')
                //     ->sum('amount');

                // $totalPaymentAmount = $payments
                //     ->where('status', 'succeeded')
                //     ->sum('amount') - $excludedCommissionPayment;


                $excludeTotal =  ($product_price + $extraValue + $tax_amount) - $discount_amount;
                $customerTotal = ($product_price + $extraValue + $tax_amount) - $discount_amount - $excludedCommissionPayment;

                // dd($totalPaymentAmount, $customerTotal, $extraValue);

                if(round($totalPaymentAmount, 2) < round($customerTotal, 2)){

                    $excludedBalance = $customerTotal - $totalPaymentAmount;
                    $customerTotal = $totalPaymentAmount;

                    $hideSuplierExcludeExtraCost = true;
                }

                // $tax_amount = 0;
                // $discount_amount = 0;

                // $costTotal = 0;
                // $costBase = 0;

                $sellingTotal = 0;
                $sellingPriceBase = 0;

                $profit = 0;
                

            } else {

                $customerTotal = ($product_price + $extraValue + $tax_amount) - $discount_amount;
            }
            /*
            |--------------------------------------------------------------------------
            | FINAL ROW (DYNAMIC SAFE)
            |--------------------------------------------------------------------------
            */

            $currency = $order->currency ?? 'CAD';

            $pricing = json_decode($order->tour_pricing, true) ?? [];

            $sellingPriceBase = 0;
            $costBase = 0;
            $baseExtraIncludedBase = 0;

            if (isset($tourPricing[$order->tour_id])) {

                foreach ($pricing as $p) {

                    $label = strtolower(trim($p['label'] ?? ''));

                    $tourPricingId = $p['tour_pricing_id'];
                    $qty   = (int) ($p['quantity'] ?? 0);

                    if ($qty <= 0) continue;

                    /*
                    |--------------------------------------------------------------------------
                    | 🔥 MATCH LABEL (SMART MATCH)
                    |--------------------------------------------------------------------------
                    */

                    // dd($tourPricing);
                    $matched = $tourPricing[$order->tour_id]->first(function ($tp) use ($label, $tourPricingId) {

                        // dd($label, $tp->label, $tourPricingId);

                        return str_contains($tourPricingId, strtolower($tp->id));
                    });

                    if (!$matched) continue;

                    $baseCost = currencyConvertWithoutRound($matched->price, $currency, 'CAD');
                    $baseSelling = currencyConvertWithoutRound($matched->selling_price, 'CAD', 'CAD');
                    $baseExtraIncluded = currencyConvertWithoutRound($matched->extra_included, 'CAD', 'CAD');

                    /*
                    |--------------------------------------------------------------------------
                    | 🔥 HANDLE FIXED VS PER PERSON
                    |--------------------------------------------------------------------------
                    */
                    if (($p['price_type'] ?? '') === 'FIXED') {
                        $costBase += $baseCost;
                        $sellingPriceBase += $baseSelling;
                        $baseExtraIncludedBase += $baseExtraIncluded * $qty;

                    } else {
                        $costBase += $baseCost * $qty;
                        $sellingPriceBase += $baseSelling * $qty;
                        $baseExtraIncludedBase += $baseExtraIncluded * $qty;

                    }
                }
            }
            $baseExtraExcludedBase = $hideSuplierExcludeExtraCost ? 0 : $baseExtraExcludedBase;


            // dd($baseExtraExcludedBase);
            $sellingTotal = $hideSuplierExcludeExtraCost ? 0 : $baseExtraExcludedBase;

            // $sellingTotal = $hideSuplierExcludeExtraCost ? 0 : $baseExtraExcludedBase;

            if(!$isExcludedFromPayment){
                $sellingTotal = $sellingPriceBase + $baseExtraIncludedBase + $sellingTotal;
            }
            
            
            $costTotal    = $costBase;
            
            if (isset($allTaxes[$order->tour_id])) {

                foreach ($allTaxes[$order->tour_id] as $tax) {

                    $taxValue = $tax->fee_type == 'FIXED_PER_ORDER'
                        ? currencyConvertWithoutRound($tax->tax_fee_value, $currency, 'CAD')
                        : $tax->tax_fee_value;

                    $sellingTax = get_tax($sellingTotal, $tax->fee_type, $taxValue);

                    $costTax    = get_tax($costTotal, $tax->fee_type, $taxValue);
                    
                    $sellingTotal += $sellingTax;
                    $costTotal    += $costTax;
                }
            }

            $transportCost = currencyConvertWithoutRound(
                                    $order->transport_cost ?? 0,
                                    $currency,
                                    'CAD'
                                );

            $displayTotals = $this->prepareDisplayTotals(
                                        $isExcludedFromPayment,
                                        $extraValue,
                                        $costBase,
                                        $costTotal,
                                        $sellingPriceBase,
                                        $baseExtraIncludedBase,
                                        $baseExtraExcludedBase,
                                        $sellingTax,
                                        $sellingTotal,
                                        $transportCost
                                    );
            // dd($excludedBalance, round(currencyConvertWithoutRound(($customerTotal + $excludedCommissionPayment) - $order->booked_amount, $order->currency, 'CAD'), 2));
            // dd($excludeTotal, $sellingTax, $baseExtraExcludedBase);
            // dd($sellingTotal, $costTotal, $customerTotal);
            // dd(($isExcludedFromPayment && $extraValue >= 0), $isExcludedFromPayment, $extraValue);

            // dd(($customerTotal == 0));
            $row = [
                'no' => $index++,
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'customer_name' => trim($order->first_name . ' ' . $order->last_name),
                'order_date' => $order->created_at,
                'fulfilment_date' => $order->tour_date,
                'payment_status' => $isExcludedFromPayment
                    ? '-'
                    : (
                        ((int) round($order->booked_amount * 100) === 0) ? 'No' : (
                            ((int) round($order->booked_amount * 100) === (int) round($customerTotal * 100)) ? 'Yes' : (
                                ((int) round($order->booked_amount * 100) < (int) round($customerTotal * 100)) ? 'Partial Paid' : 'Over Paid'
                            )
                        )
                    ),//$order->payment_status == 2 ? 'Yes' : 'No',
                'product_name' => $order->product_name,

                'adult' => $adult,
                'child' => $child,
                'infant' => $infant,
                'senior' => $senior,
                'other' => $other,

                'product_price' =>   round(currencyConvertWithoutRound($product_price, $order->currency, 'CAD'), 2),
                'extra_amount' =>   round(currencyConvertWithoutRound($extraValue, $order->currency, 'CAD'), 2),
                'tax_amount' =>   round(currencyConvertWithoutRound($tax_amount, $order->currency, 'CAD'), 2),
                'discount_amount' =>   round(currencyConvertWithoutRound($discount_amount, $order->currency, 'CAD'), 2),
                'customer_total' =>   round(currencyConvertWithoutRound($customerTotal, $order->currency, 'CAD'), 2),
                'exclude_total' =>  $isExcludedFromPayment ? round(currencyConvertWithoutRound($excludeTotal, $order->currency, 'CAD'), 2) :0,
                'excluded_commission_payment' => round(currencyConvertWithoutRound($excludedCommissionPayment, $order->currency, 'CAD'), 2),
                'balance_amount' => round(currencyConvertWithoutRound(round($customerTotal) - round($totalPaymentAmount), $order->currency, 'CAD'), 2),
                'excluded_balance_amount' => $hideSuplierExcludeExtraCost ? $excludedBalance : 0,

                /*
                |--------------------------------------------------------------------------
                | 🔥 TOUR PRICING BREAKDOWN
                |--------------------------------------------------------------------------
                */

                'tour_cost_price' => $isExcludedFromPayment ? 0 :  round($costBase, 2),
                'tour_cost_tax' => $isExcludedFromPayment ? 0 :  round($costTotal - $costBase, 2),
                'tour_cost_total' => $isExcludedFromPayment ? 0 :  round($costTotal, 2),

                'tour_selling_price' => $isExcludedFromPayment ? 0 :  round($sellingPriceBase, 2),
                'tour_extra_included_price' => $isExcludedFromPayment ? 0 :  round($baseExtraIncludedBase, 2),
                'tour_extra_excluded_price' => ($isExcludedFromPayment && $extraValue == 0) ? 0 :  round($baseExtraExcludedBase, 2),
                'tour_selling_tax' => ($isExcludedFromPayment && $extraValue == 0) ?  0 :  round($sellingTax, 2),
                'tour_selling_total' => ($isExcludedFromPayment && $extraValue == 0) ? 0 :  round($sellingTotal, 2),
                'transport_cost'    => $isExcludedFromPayment ? 0 :  round($transportCost, 2),

                /*
                |--------------------------------------------------------------------------
                | ✅ PROFIT (CORRECT)
                |--------------------------------------------------------------------------
                */

                'profit' => ($customerTotal == 0) ? 0 : round($sellingTotal - $costTotal, 2),


                /*
                |--------------------------------------------------------------------------
                | 🔥 TOUR PRICING BREAKDOWN
                |--------------------------------------------------------------------------
                */

                // 'tour_cost_price' => $displayTotals['tour_cost_price'],
                // 'tour_cost_tax' => $displayTotals['tour_cost_tax'],
                // 'tour_cost_total' => $displayTotals['tour_cost_total'],

                // 'tour_selling_price' => $displayTotals['tour_selling_price'],
                // 'tour_extra_included_price' => $displayTotals['tour_extra_included_price'],
                // 'tour_extra_excluded_price' => $displayTotals['tour_extra_excluded_price'],
                // 'tour_selling_tax' => $displayTotals['tour_selling_tax'],
                // 'tour_selling_total' => $displayTotals['tour_selling_total'],

                // 'transport_cost' => $displayTotals['transport_cost'],

                // /*
                // |--------------------------------------------------------------------------
                // | ✅ PROFIT
                // |--------------------------------------------------------------------------
                // */

                // 'profit' => $displayTotals['profit'],
            ];
            // dd($customerTotal,$excludedCommissionPayment, $totalPaymentAmount, $sellingTotal , $costTotal);

            // dd($row['customer_total'],$row['balance_amount'], $totalPaymentAmount);
            $totals['product_price']      += $row['product_price'];
            $totals['extra_amount']       += $row['extra_amount'];
            $totals['tax_amount']         += $row['tax_amount'];
            $totals['discount_amount']    += $row['discount_amount'];
            $totals['customer_total']     += $row['customer_total'];
            $totals['exclude_total']      +=  $row['exclude_total'];
            $totals['balance_amount']     += $row['balance_amount'];
            $totals['transport_cost']     += $row['transport_cost'];
            $totals['tour_selling_price'] += $row['tour_selling_price'];
            $totals['tour_extra_included_price'] += $row['tour_extra_included_price'];
            $totals['tour_extra_excluded_price'] += $row['tour_extra_excluded_price'];
            $totals['tour_selling_tax']   += $row['tour_selling_tax'];

            $netTotal = $row['tour_selling_total'] + $row['transport_cost'];
            $profit   = $row['customer_total'] - $row['tour_selling_total'] - $row['transport_cost'];

            $totals['net_total'] += $netTotal;
            $totals['profit']    += $profit;
            // $totals['profit']    += ($row['customer_total'] == 0) ? 0 : $profit;
            
            // attach all addon columns consistently
            // dd($allAddonKeys);
            foreach ($allAddonKeys as $key) {
                $row[$key.'_desc']  = $extraColumns[$key]['description'];
                $row[$key.'_quant'] = $extraColumns[$key]['quantity'];
                $row[$key.'_price'] = $extraColumns[$key]['price'];
                $row[$key.'_tax']   = $extraColumns[$key]['tax'];
                $row[$key.'_fee']   = $extraColumns[$key]['fee'];
                $row[$key.'_total'] = $extraColumns[$key]['total'];

                $addonTotals['qty'] += $extraColumns[$key]['quantity'] ?? 0;
                $addonTotals['price'] += $extraColumns[$key]['price'] ?? 0;
                $addonTotals['total'] += $extraColumns[$key]['total'] ?? 0;
            }
            $totals['addonTotals']    = $addonTotals;

            // dd($row);
            $rows[] = $row;
        }
        // dd($rows);
        return $paginate
            ? ['rows' => $rows, 'pagination' => $orders, 'totals' => $totals]
            : ['rows' => $rows, 'totals' => $totals];
    }



public function exportRevenue(Request $request)
{
    ini_set('memory_limit', '1024M');
    return Excel::download(
        new RevenueExport($request),
        'revenue_report_' . now()->format('Ymd_His') . '.xlsx'
    );
}

public function exportCustomer(Request $request)
{
    ini_set('memory_limit', '1024M');
    return Excel::download(
        new CustomerExport($request),
        'customer_report_' . now()->format('Ymd_His') . '.xlsx'
    );
}

//     public function schedulePricingReport(Request $request)
// {
//     $rows = $this->getSchedulePricingReportData($request);

//     $partners = Partner::get();

//     return view('admin.reports.schedule-pricing', compact('rows', 'partners'));
// }

    public function schedulePricingReport(Request $request)
    {
        $rows = $this->getSchedulePricingReportData($request, false);

        $partners = Partner::get();
        $selectedProducts = Tour::whereIn(
            'id',
            (array)$request->product
        )->get(['id','title']);

        $excludedProducts = Tour::whereIn(
            'id',
            (array)$request->exclude_product
        )->get(['id','title']);

        return view('admin.reports.schedule-pricing', compact('rows', 'partners', 'selectedProducts', 'excludedProducts'));
    }

    private function getSchedulePricingReportData($request, $export = false)
    {
        $query = Tour::query()
            ->onlyRoot()
            ->with(['categories', 'location', 'schedules', 'subTours'])
            ->join('tour_pricings as tp', 'tp.tour_id', '=', 'tours.id')
            ->whereNull('tp.deleted_at')
            ->select(
                'tours.*',
                'tp.label',
                'tp.price',
                'tp.selling_price'
            );

        /*
        |--------------------------------------------------------------------------
        | SAME FILTERS (UNCHANGED)
        |--------------------------------------------------------------------------
        */

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('tours.title', 'like', "%{$search}%")
                  ->orWhere('tours.unique_code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->whereHas('categories', fn($q) => $q->where('category_id', $request->category));
        }

        if ($request->filled('status')) {
            $query->where('tours.status', $request->status);
        }

        if ($request->filled('author')) {
            $query->where('tours.user_id', $request->author);
        }

        if ($request->filled('city')) {
            $query->whereHas('location', fn($q) => $q->where('city_id', $request->city));
        }

        if ($request->filled('schedule')) {
            $request->schedule === 'active'
                ? $query->whereHas('schedules')
                : $query->whereDoesntHave('schedules');
        }

        if ($request->filled('has_sub_tour')) {
            $request->has_sub_tour === 'yes'
                ? $query->whereHas('subTours')
                : $query->whereDoesntHave('subTours');
        }

        /*
        |--------------------------------------------------------------------------
        | PAGINATION
        |--------------------------------------------------------------------------
        */
        $perPage = $request->per_page == 'All'
            ? $query->count()
            : ($request->per_page ?? 20);

        $data = $export
            ? $query->get()
            : $query->paginate($perPage)->withQueryString();

        $collection = $export ? $data : $data->getCollection();

        /*
        |--------------------------------------------------------------------------
        | PRELOAD TAXES (IMPORTANT 🔥)
        |--------------------------------------------------------------------------
        */
        $tourIds = $collection->pluck('id')->unique();

        $taxes = DB::table('taxes_fee_tour as tft')
            ->join('taxes_fees as tf', 'tft.taxes_fee_id', '=', 'tf.id')
            ->whereIn('tft.tour_id', $tourIds)
            ->whereNull('tft.deleted_at')
            ->select(
                'tft.tour_id',
                'tf.tax_fee_type',
                'tf.tax_fee_value',
                'tf.fee_type'
            )
            ->get()
            ->groupBy('tour_id');
        
        /*
        |--------------------------------------------------------------------------
        | TRANSFORM (CORRECT TAX LOGIC)
        |--------------------------------------------------------------------------
        */
        $rows = $collection->map(function ($item) use ($taxes) {

            $currency = $item->currency ?? 'CAD';

            $revenue = round(currencyConvertWithoutRound($item->price, $currency, 'CAD'), 2);
            $cost = round(currencyConvertWithoutRound($item->selling_price, $currency, 'CAD'), 2);

            /*
            |--------------------------------------------------------------------------
            | APPLY TAX EXACTLY LIKE YOUR SYSTEM
            |--------------------------------------------------------------------------
            */
            $revenueTotal = $revenue;
            $costTotal = $cost;

            if (isset($taxes[$item->id])) {

                foreach ($taxes[$item->id] as $tax) {

                    $taxValue = $tax->fee_type == 'FIXED_PER_ORDER'
                        ? currencyConvertWithoutRound($tax->tax_fee_value, $currency, 'CAD')
                        : $tax->tax_fee_value;

                    $revTax = get_tax($revenueTotal, $tax->fee_type, $taxValue);
                    $costTax = get_tax($costTotal, $tax->fee_type, $taxValue);

                    $revenueTotal += $revTax;
                    $costTotal += $costTax;
                }
            }

            return [
                'tour_name' => $item->title,
                'label' => $item->label,

                'revenue_price' => $revenue ?? 0,
                'revenue_tax' => ($revenueTotal - $revenue)?? 0,
                'revenue_total' => $revenueTotal ?? 0,

                'cost_price' => $cost ?? 0,
                'cost_tax' => ($costTotal - $cost)?? 0,
                'cost_total' => $costTotal?? 0,

                'profit' => ($revenueTotal - $costTotal)?? 0,
            ];
        });

        if ($export) {
            return $rows;
        }

        $data->setCollection($rows);

        return $data;
    }
    public function schedulePricingExport(Request $request)
    {
        ini_set('memory_limit', '1024M');
        $rows = $this->getSchedulePricingReportData($request, true);

        return Excel::download(new PriceScheduleExport($rows), 'price_schedule_report.xlsx');
    }

    public function reportPriceSchedule32432(Request $request)
    {
        

        $paginated = $request->filled('pagination');
        $data = $this->getInvoiceWithDetailsData($request, $paginated);
        $selectedProducts = Tour::whereIn(
            'id',
            (array)$request->product
        )->get(['id','title']);

        $excludedProducts = Tour::whereIn(
            'id',
            (array)$request->exclude_product
        )->get(['id','title']);
        
        return view('admin.reports.price_schedule', [
            'rows' => $data['rows'],
            'orders' => $data['pagination'],
            'partners' => Partner::get(),
            'selectedProducts' => $selectedProducts,
            'excludedProducts' => $excludedProducts,
            'addonKeys' => collect($data['rows'][0] ?? [])
            ->keys()
            ->filter(fn($key) => str_contains($key, '_desc'))
            ->map(fn($key) => str_replace('_desc', '', $key))
            ->values()
        ]);
    }

    public function reportPriceSchedule(Request $request)
    {
        $paginated = $request->filled('pagination');

        $data = $this->getInvoiceWithDetailsData($request, $paginated);

        $businessExpense = $this->getBusinessExpenses($request);
        
        // Normalize the response
        if (!$paginated) {
            $data = [
                'rows' => isset($data['rows']) ?  $data['rows'] : [],
                'pagination' => null,
                'totals' => isset($data['totals']) ? $data['totals'] : [],
                'businessExpense' => $businessExpense
            ];
        }

        $selectedProducts = Tour::whereIn(
            'id',
            (array) $request->product
        )->get(['id', 'title']);

        $excludedProducts = Tour::whereIn(
            'id',
            (array) $request->exclude_product
        )->get(['id', 'title']);

        return view('admin.reports.price_schedule', [
            'rows'              => $data['rows'],
            'orders'            => $data['pagination'],
            'totals'            => $data['totals'],
            'partners'          => Partner::get(),
            'selectedProducts'  => $selectedProducts,
            'excludedProducts'  => $excludedProducts,
             'businessExpense' => $businessExpense,
            'addonKeys'         => collect($data['rows'][0] ?? [])
                ->keys()
                ->filter(fn($key) => str_contains($key, '_desc'))
                ->map(fn($key) => str_replace('_desc', '', $key))
                ->values(),
        ]);
    }
    public function exportPriceSchedule(Request $request)
    {
        // 🔥 SAME FILTER LOGIC

        ini_set('memory_limit', '1024M');
        $data = $this->getInvoiceWithDetailsData($request, false);

        return Excel::download(
            new OrderPriceScheduleExport($data['rows'], $data['totals']),
            'price_schedule_' . now()->format('Ymd_His') . '.xlsx'
        );
    }

    public function exportPriceSchedule324(Request $request)
    {
        return Excel::download(
            new OrderPriceScheduleExport($request), // 🔥 pass request instead of full data
            'price_schedule_' . now()->format('Ymd_His') . '.xlsx'
        );
    }
    public function transformInvoiceRow($order, $payments = [])
{
    $productValue = 0;
    $extraValue = 0;
    $taxValue = 0;
    $discountAmount = 0;
    $subtotal = 0;

    $pricing = json_decode($order->tour_pricing, true) ?? [];
    $extras  = json_decode($order->tour_extra, true) ?? [];
    $discounts = json_decode($order->discount, true) ?? [];

    $adult = $child = $infant = $other = $senior = 0;

    /*
    |--------------------------------------------------------------------------
    | 🔥 PRICING
    |--------------------------------------------------------------------------
    */
    foreach ($pricing as $p) {

        $qty = (int) ($p['quantity'] ?? 0);
        $label = strtolower($p['label'] ?? '');
        $price = $p['actual_price'] ?? $p['price'] ?? 0;

        if (str_contains($label, 'adult')) $adult += $qty;
        elseif (str_contains($label, 'child')) $child += $qty;
        elseif (str_contains($label, 'infant')) $infant += $qty;
        elseif (str_contains($label, 'senior')) $senior += $qty;
        else $other += $qty;

        if ($qty > 0) {
            $productValue += $price * $qty;
        }
    }

    $subtotal += $productValue;

    /*
    |--------------------------------------------------------------------------
    | 🔥 EXTRAS
    |--------------------------------------------------------------------------
    */
    foreach ($extras as $e) {
        $extraValue += $e['total_price'] ?? (($e['quantity'] ?? 0) * ($e['price'] ?? 0));
    }

    $subtotal += $extraValue;

    /*
    |--------------------------------------------------------------------------
    | 🔥 DISCOUNT
    |--------------------------------------------------------------------------
    */
    foreach ($discounts as $d) {
        $discountAmount += $d['price'] ?? 0;
    }

    $subtotal -= $discountAmount;

    /*
    |--------------------------------------------------------------------------
    | 🔥 TAX
    |--------------------------------------------------------------------------
    */
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

    /*
    |--------------------------------------------------------------------------
    | 🔥 PAYMENTS
    |--------------------------------------------------------------------------
    */
    $orderPayments = collect($payments);

    $totalPaid = $orderPayments
        ->where('status', 'succeeded')
        ->sum('amount');

    $refunded = $orderPayments
        ->where('status', 'refunded')
        ->sum('amount');

    $totalPaid = $totalPaid - $refunded;

    /*
    |--------------------------------------------------------------------------
    | 🔥 CONVERT TO CAD (NO ROUND FIRST)
    |--------------------------------------------------------------------------
    */
    $productCAD = currencyConvertWithoutRound($productValue, $order->currency, 'CAD');
    $extraCAD   = currencyConvertWithoutRound($extraValue, $order->currency, 'CAD');
    $taxCAD     = currencyConvertWithoutRound($taxValue, $order->currency, 'CAD');
    $discountCAD= currencyConvertWithoutRound($discountAmount, $order->currency, 'CAD');
    $totalCAD   = currencyConvertWithoutRound($finalTotal, $order->currency, 'CAD');
    $paidCAD    = currencyConvertWithoutRound($totalPaid, $order->currency, 'CAD');

    /*
    |--------------------------------------------------------------------------
    | 🔥 RETURN (EXPORT SAFE STRUCTURE)
    |--------------------------------------------------------------------------
    */
    return [

        'order_number' => $order->order_number,
        'customer_name' => $order->first_name . ' ' . $order->last_name,
        'order_date' => $order->created_at,
        'fulfilment_date' => $order->tour_date,

        // 🔥 PASSENGERS
        'adult' => $adult,
        'child' => $child,
        'infant' => $infant,
        'other' => $other,
        'senior' => $senior,

        // 🔥 REVENUE SIDE
        'product_price' => round($productCAD, 2),
        'extra_amount' => round($extraCAD, 2),
        'tax_amount' => round($taxCAD, 2),
        'discount_amount' => round($discountCAD, 2),
        'customer_total' => round($totalCAD, 2),

        // 🔥 PAYMENT
        'total_paid' => round($paidCAD, 2),
        'balance_amount' => round($totalCAD - $paidCAD, 2),

        // 🔥 COST SIDE (MANDATORY FOR EXPORT)
        'tour_selling_price' => round($productCAD, 2),
        'tour_selling_tax' => round($taxCAD, 2),
        'tour_selling_total' => round($productCAD + $taxCAD, 2),

        // 🔥 EXTRA COST (SAFE DEFAULT)
        'transport_cost' => 0,

        // 🔥 PRODUCT
        'product_name' => $order->product_name,
    ];
}

    private function getBusinessExpenses(Request $request)
    {
        $query = BusinessExpense::query();

         $expenses = [];
         $total = 0;

        // Date Filter
        if ($request->filled('booking_date')) {
            try {
                [$start, $end] = explode(' - ', $request->booking_date);

                $query->whereBetween('expense_date', [
                    Carbon::parse($start)->startOfDay(),
                    Carbon::parse($end)->endOfDay(),
                ]);
   

        // Product Filter
        if ($products = array_filter((array) $request->product)) {
            $query->whereIn('tour_id', $products);
        }

        // Partner Filter
        if ($partners = array_filter((array) $request->partner)) {
            $query->whereIn('partner_id', $partners);
        }

        $expenses = $query
            ->select('category', DB::raw('SUM(amount) as amount'))
            ->groupBy('category')
            ->orderBy('category')
            ->get();


        return [
                'expenses' => $expenses,
                'total' => $expenses->sum('amount'),
            ];
                 } catch (\Exception $e) {
                //
            }
        }
        return [
                'expenses' => $expenses,
                'total' => $total,
            ];
    }

    private function prepareDisplayTotals(
        bool $isExcludedFromPayment,
        float $extraValue,
        float $costBase,
        float $costTotal,
        float $sellingPriceBase,
        float $baseExtraIncludedBase,
        float $baseExtraExcludedBase,
        float $sellingTax,
        float $sellingTotal,
        float $transportCost
    ): array {

        $hideTourCost = $isExcludedFromPayment;
        $hideExtraSelling = $isExcludedFromPayment && $extraValue == 0;

        return [
            'tour_cost_price' => $hideTourCost ? 0 : round($costBase, 2),
            'tour_cost_tax' => $hideTourCost ? 0 : round($costTotal - $costBase, 2),
            'tour_cost_total' => $hideTourCost ? 0 : round($costTotal, 2),

            'tour_selling_price' => $hideTourCost ? 0 : round($sellingPriceBase, 2),
            'tour_extra_included_price' => $hideTourCost ? 0 : round($baseExtraIncludedBase, 2),

            'tour_extra_excluded_price' => $hideExtraSelling ? 0 : round($baseExtraExcludedBase, 2),
            'tour_selling_tax' => $hideExtraSelling ? 0 : round($sellingTax, 2),
            'tour_selling_total' => $hideExtraSelling ? 0 : round($sellingTotal, 2),

            'transport_cost' => $hideTourCost ? 0 : round($transportCost, 2),

            'profit' => round($sellingTotal - $costTotal, 2),
        ];
    }

    private function calculateReportTotals(
    bool $isExcludedFromPayment,
    Collection $payments,
    float $productPrice,
    float $extraValue,
    float $taxAmount,
    float $discountAmount,
    float $bookedAmount,
    float $costBase,
    float $costTotal,
    float $sellingPriceBase,
    float $baseExtraIncludedBase,
    float $baseExtraExcludedBase,
    float $sellingTax,
    float $sellingTotal,
    float $transportCost
): array {

    $grossTotal = ($productPrice + $extraValue + $taxAmount) - $discountAmount;

    $excludedCommissionPayment = $payments
        ->where('payment_type', 'EXCLUDED')
        ->sum('amount');

    /*
    |--------------------------------------------------------------------------
    | Customer / Balance
    |--------------------------------------------------------------------------
    |
    | Modify ONLY this section whenever business rules change.
    |
    */

    if ($isExcludedFromPayment) {

        // Current implementation.
        // Change these rules whenever required.

        $customerTotal = $grossTotal - $excludedCommissionPayment;

        $excludeTotal = $grossTotal;

        $balanceAmount = ($customerTotal + $excludedCommissionPayment) - $bookedAmount;

        $paymentStatus = '-';

    } else {

        $customerTotal = $grossTotal;

        $excludeTotal = 0;

        $balanceAmount = $customerTotal - $bookedAmount;

        if ((int) round($bookedAmount * 100) === 0) {
            $paymentStatus = 'No';
        } elseif ((int) round($bookedAmount * 100) === (int) round($customerTotal * 100)) {
            $paymentStatus = 'Yes';
        } elseif ((int) round($bookedAmount * 100) < (int) round($customerTotal * 100)) {
            $paymentStatus = 'Partial Paid';
        } else {
            $paymentStatus = 'Over Paid';
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Display Rules
    |--------------------------------------------------------------------------
    */

    $hideTourCost = $isExcludedFromPayment;

    $hideExtraSelling = $isExcludedFromPayment && $extraValue == 0;

    return [

        // Payment
        'customer_total' => $customerTotal,
        'exclude_total' => $excludeTotal,
        'excluded_commission_payment' => $excludedCommissionPayment,
        'balance_amount' => $balanceAmount,
        'payment_status' => $paymentStatus,

        // Tour Cost
        'tour_cost_price' => $hideTourCost ? 0 : round($costBase, 2),
        'tour_cost_tax' => $hideTourCost ? 0 : round($costTotal - $costBase, 2),
        'tour_cost_total' => $hideTourCost ? 0 : round($costTotal, 2),

        // Selling
        'tour_selling_price' => $hideTourCost ? 0 : round($sellingPriceBase, 2),
        'tour_extra_included_price' => $hideTourCost ? 0 : round($baseExtraIncludedBase, 2),
        'tour_extra_excluded_price' => $hideExtraSelling ? 0 : round($baseExtraExcludedBase, 2),
        'tour_selling_tax' => $hideExtraSelling ? 0 : round($sellingTax, 2),
        'tour_selling_total' => $hideExtraSelling ? 0 : round($sellingTotal, 2),

        'transport_cost' => $hideTourCost ? 0 : round($transportCost, 2),

        'profit' => round($sellingTotal - $costTotal, 2),
    ];
}


    }
