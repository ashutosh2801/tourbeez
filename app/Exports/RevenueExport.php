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
        $request = $this->request;

        $startDate = $request->start_date
            ? Carbon::parse($request->start_date)->startOfDay()
            : Carbon::today()->startOfDay();

        $endDate = $request->end_date
            ? Carbon::parse($request->end_date)->endOfDay()
            : Carbon::today()->endOfDay();

        $query = DB::table('orders')
            ->leftJoin('order_tours', 'orders.id', '=', 'order_tours.order_id')
            ->leftJoin('tours', 'order_tours.tour_id', '=', 'tours.id')
            ->leftJoin('order_customers', 'orders.id', '=', 'order_customers.order_id')
            ->whereNull('orders.deleted_at')
            ->where('orders.order_status', '!=', 1)
            ->whereBetween('orders.created_at', [$startDate, $endDate]);

        // Filters
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

        $orders = $query->select(
            'orders.id',
            'orders.order_number',
            'orders.order_status',
            'orders.payment_status',
            'orders.source',
            'orders.created_by',
            'orders.created_at as booking_date',
            'order_tours.tour_date as fulfilment_date',

            'order_customers.first_name',
            'order_customers.last_name',

            'orders.total_amount',
            'orders.booked_amount',
            'orders.currency',

            // 'orders.booking_fee',
            // 'orders.commission',
            // 'orders.tax',

            'order_tours.number_of_guests as pax',

            'order_customers.promo_code',
            'orders.payment_method',

            'order_customers.instructions',
            'tours.title as product_name',

            'order_tours.tour_id'
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

        return $orders->map(function ($order) use ($categoryMap) {

            // ✅ Convert to CAD
            $amount = currencyConvertWithoutRound($order->total_amount, $order->currency, 'CAD');
            $paid   = currencyConvertWithoutRound($order->booked_amount, $order->currency, 'CAD');

            $bookingFee = currencyConvertWithoutRound($order->booking_fee ?? 0, $order->currency, 'CAD');
            $commission = currencyConvertWithoutRound($order->commission ?? 0, $order->currency, 'CAD');
            $tax        = currencyConvertWithoutRound($order->tax ?? 0, $order->currency, 'CAD');

            $balance = $amount - $paid;

            $netSales = $amount - $commission - $tax;

            return [
                $order->order_number,
                ucfirst($order->order_status),
                $order->source,
                'NA', // Agent/Supplier

                $order->booking_date,
                $order->fulfilment_date,

                $order->first_name . ' ' . $order->last_name,

                round($amount, 2),
                round($paid, 2),
                round($balance, 2),

                round($bookingFee, 2),
                0, // Custom Fees
                0, // Surge
                0, // CC Surcharge
                0, // Platform Fees
                0,
                0,
                round($netSales, 2),

                $order->pax,
                round($amount, 2), // Product Value
                0, // Adjustment
                0, // Extra Value

                $order->promo_code,

                0, // CC Payment
                0, // Cash Payment
                0, // Voucher Value

                0, // Free Payment
                0, // Other Refund

                $order->payment_status,
                $balance <= 0 ? 'Yes' : 'No',

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