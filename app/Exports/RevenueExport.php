<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RevenueExport implements FromCollection, WithHeadings
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function collection()
{
    $excludedStatuses = [1, 2, 6, 7];
    $request = $this->request;

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
        || $request->filled('action_type');

    /*
    |--------------------------------------------------------------------------
    | DEFAULT BOOKING DATE (LAST 7 DAYS)
    |--------------------------------------------------------------------------
    */
    if (!$hasFilter) {
        return collect();
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
        ->leftJoin('order_tours', 'orders.id', '=', 'order_tours.order_id')
        ->leftJoin('tours', 'order_tours.tour_id', '=', 'tours.id')
        ->leftJoin('order_customers', 'orders.id', '=', 'order_customers.order_id')
        ->whereNull('orders.deleted_at')
        ->whereNotIn('orders.order_status', $excludedStatuses)
        ->groupBy('orders.id');



    /*
    |--------------------------------------------------------------------------
    | APPLY FILTERS
    |--------------------------------------------------------------------------
    */

    // ✅ Booking Date
    if ($startDate && $endDate) {
        $query->whereBetween('orders.created_at', [$startDate, $endDate]);
    }

    // ✅ Product
    if ($product = $request->input('product')) {
        $query->where('order_tours.tour_id', $product);
    }

    // ✅ Order Status
    if ($request->filled('order_status')) {
        $query->where('orders.order_status', $request->order_status);
    }

    // ✅ Payment Status
    if ($request->filled('payment_status')) {
        $query->where('orders.payment_status', $request->payment_status);
    }

    // ✅ Partner
    if ($request->filled('partner')) {
        $query->where('orders.source', $request->partner);
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

    // ✅ Tour Date
    if ($request->filled('tour_date')) {
        try {
            [$start, $end] = explode(' - ', $request->tour_date);

            $query->whereBetween('order_tours.tour_date', [$start, $end]);
        } catch (\Exception $e) {}
    }

    $orders = $query->select(
        'orders.id',
        'orders.order_number',
        'orders.order_status',
        'orders.payment_status',
        'orders.source',
        'orders.created_by',
        'orders.created_at as booking_date',
        'order_tours.tour_date as fulfilment_date',
        'order_tours.tour_id',

        // ✅ IMPORTANT (same as controller)
        'order_tours.tour_pricing',
        'order_tours.tour_extra',
        'order_tours.discount',
        'order_tours.tour_fees',

        'order_customers.first_name',
        'order_customers.last_name',

        'orders.currency',
        'orders.booking_fee',

        'order_tours.number_of_guests as pax',

        'order_customers.promo_code',
        'order_customers.instructions',

        'orders.payment_method',
        'tours.title as product_name'
    )->orderByDesc('orders.id')->get();

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
    | FETCH PAYMENTS (same as controller)
    |--------------------------------------------------------------------------
    */
    $orderIds = $orders->pluck('id');

    $payments = DB::table('order_payments')
        ->whereIn('order_id', $orderIds)
        ->get()
        ->groupBy('order_id');

    /*
    |--------------------------------------------------------------------------
    | FINAL MAP (SAME LOGIC AS CONTROLLER)
    |--------------------------------------------------------------------------
    */
    return $orders->map(function ($order) use ($payments, $categoryMap) {

        $productValue = 0;
        $extraValue = 0;
        $totalTax = 0;

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

        $subtotal2 += $productValue;

        // ✅ Extras
        foreach ($extras as $e) {
            $qty = $e['quantity'] ?? 0;
            $price = $e['price'] ?? 0;

            if ($qty > 0) {
                $extraValue += ($e['total_price'] ?? ($qty * $price));
            }
        }

        $subtotal2 += $extraValue;

        // ✅ Discount
        $discountAmount = 0;
        foreach ($discounts as $d) {
            $discountAmount = $d['price'] ?? 0;
        }

        $subtotal2 -= $discountAmount;

        // ✅ Taxes
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
        $totalCAD = round(currencyConvertWithoutRound($finalTotal, $order->currency, 'CAD'), 2);
        $paidCAD  = round(currencyConvertWithoutRound($paid, $order->currency, 'CAD'), 2);
        $balanceCAD = round(currencyConvertWithoutRound($balance, $order->currency, 'CAD'), 2);
        $taxCAD = round(currencyConvertWithoutRound($totalTax, $order->currency, 'CAD'), 2);
        $productCAD = round(currencyConvertWithoutRound($productValue, $order->currency, 'CAD'), 2);
        $extraCAD = round(currencyConvertWithoutRound($extraValue, $order->currency, 'CAD'), 2);
        $discountCAD = round(currencyConvertWithoutRound($discountAmount, $order->currency, 'CAD'), 2);

        return [
            $order->order_number,
            config('constants.status_with_code')[$order->order_status],
            $order->source,
            'NA',

            $order->booking_date,
            $order->fulfilment_date,

            trim(($order->first_name ?? '') . ' ' . ($order->last_name ?? '')),

            number_format($totalCAD, 2),
            number_format($paidCAD, 2),
            number_format($balanceCAD, 2),

            0,
            0,
            0,
            0,
            0,

            0, // commission (not calculated yet)
            number_format($taxCAD, 2),

            number_format($totalCAD - $taxCAD, 2),

            $adult,
            $child,
            $infant,
            $other,

            $order->pax,
            number_format($productCAD, 2),
            0,
            number_format($extraCAD, 2),

            number_format($discountCAD, 2),

            0,
            0,
            number_format($discountCAD, 2),

            0,
            0,

            $order->payment_status,
            $balanceCAD <= 0 ? 'Yes' : 'No',

            $order->payment_method,
            'NA',
            'NA',

            $order->instructions,
            'NA',

            $order->product_name,
            $categoryMap[$order->tour_id] ?? '-',

            $order->created_by,
        ];
    });
}

    public function headings(): array
    {
        return [
            'Order Number',
            'Order Status',
            'Order Source',
            'Agent/Supplier',
            'Booking Date',
            'Fulfilment Date',
            'Customer Name',

            'Order Amount (CAD)',
            'Payment Received (CAD)',
            'Balance (CAD)',

            'Booking Fees',
            'Custom Fees',
            'Surge',
            'CC Surcharge',
            'Platform Fees',
            'Commission',
            'Tax',
            'Net Sales',


            'Adult',
            'Child',
            'Infant',
            'Other',
            'Pax',
            'Product Value',
            'Adjustment',
            'Extra Value',

            'Promo/Voucher',

            'Credit Card Payment',
            'Cash Payment',
            'Promo/Voucher Value',

            'Free of Charge',
            'Other Refund',

            'Payment Status',
            'All Paid',
            'Payment Type',
            'Gateway',
            'Gateway Type',

            'Internal Notes',
            'How Heard',

            'Product Name',
            'Category',
            'Agent Ref',
        ];
    }
}