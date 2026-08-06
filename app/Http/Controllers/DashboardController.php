<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Partner;
use App\Models\Tour;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function dashboard(Request $request)
    {

        $excludedStatuses = [1, 2, 6, 7];
        $excludedPaymentSources = array_map('strtolower', excluded_payment_sources());

        /*
        |--------------------------------------------------------------------------
        | 🔥 CHECK FILTER
        |--------------------------------------------------------------------------
        */
        $partners = Partner::get();
        $hasFilter = $request->filled('booking_date')
            || $request->filled('tour_date')
            || $request->filled('product')
            || $request->filled('order_status')
            || $request->filled('payment_status')
            || $request->filled('partner')
            || $request->filled('action_type');

        if (!$hasFilter) {
            if ($request->expectsJson()) {
                return response()->json([
                    'tourAnalytics' => [],
                    'totalRevenue' => 0,
                    'totalBookings' => 0,
                    'avgBookingValue' => 0,
                    'totalTours' => 0,
                    'partners' => $partners
                ]);
            }

            return view('dashboard-tour-wise', [
                'tourAnalytics' => [],
                'tours' => [],
                'totalRevenue' => 0,
                'totalBookings' => 0,
                'avgBookingValue' => 0,
                'totalTours' => 0,
                'partners' => $partners,
            ]);
        }

        if (
            !$request->filled('booking_date') &&
            !$request->filled('tour_date') &&
            !$request->filled('product') &&
            !$request->filled('order_status') &&
            !$request->filled('payment_status') &&
            !$request->filled('partner') &&
            !$request->filled('action_type')
        ) {
            // $request->merge([
            //     'booking_date' => now()->startOfMonth()->format('Y-m-d') 
            //         . ' - ' . now()->format('Y-m-d')
            // ]);
        }

        /*
        |--------------------------------------------------------------------------
        | 🔥 BASE QUERY (SAME AS INVOICE)
        |--------------------------------------------------------------------------
        */
        $query = DB::table('orders')
            ->leftJoin('order_tours', 'orders.id', '=', 'order_tours.order_id')
            ->leftJoin('tours', 'order_tours.tour_id', '=', 'tours.id')
            ->whereNull('orders.deleted_at')
            ->whereNotIn('orders.order_status', $excludedStatuses);

        /*
        |--------------------------------------------------------------------------
        | 🔥 FILTERS (EXACT COPY)
        |--------------------------------------------------------------------------
        */
        if ($request->filled('booking_date')) {
            try {
                [$start, $end] = explode(' - ', $request->booking_date);
                $query->whereBetween('orders.created_at', [
                    \Carbon\Carbon::parse($start)->startOfDay(),
                    \Carbon\Carbon::parse($end)->endOfDay()
                ]);
            } catch (\Exception $e) {}
        }

        if ($request->filled('tour_date')) {
            try {
                [$start, $end] = explode(' - ', $request->tour_date);
                $query->whereBetween('order_tours.tour_date', [$start, $end]);
            } catch (\Exception $e) {}
        }

        if ($request->filled('product')) {
            $query->where('order_tours.tour_id', $request->product);
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

        /*
        |--------------------------------------------------------------------------
        | 🔥 SELECT RAW DATA
        |--------------------------------------------------------------------------
        */
        $orders = $query->select(
                'orders.id',
                'orders.currency',
                'orders.total_amount',
                'orders.source',
                'tours.id as tour_id',
                'tours.title'
            )
            ->get();

        /*
        |--------------------------------------------------------------------------
        | 🔥 GROUP BY TOUR + CONVERT TO CAD
        |--------------------------------------------------------------------------
        */



        $tourAnalytics = $orders
            ->groupBy('tour_id')
            ->map(function ($items) use ($excludedPaymentSources) {

                $revenue = 0;

                foreach ($items as $order) {

                    $isExcluded = in_array(
                        strtolower($order->source ?? ''),
                        $excludedPaymentSources
                    );

                    if ($isExcluded) {
                        continue;
                    }
                    $revenue += currencyConvertWithoutRound(
                        $order->total_amount,
                        $order->currency,
                        'CAD'
                    );
                }

                return [
                    'id' => $items->first()->tour_id,
                    'title' => $items->first()->title,
                    'bookings' => $items->count(),
                    'revenue' => round($revenue, 2),
                ];
            })
            ->sortByDesc('revenue')
            ->take(10)
            ->values();

        /*
        |--------------------------------------------------------------------------
        | 🔥 TOTALS
        |--------------------------------------------------------------------------
        */
        $totalRevenue = $orders->sum(function ($order) use ($excludedPaymentSources) {

            $isExcluded = in_array(
                strtolower($order->source ?? ''),
                $excludedPaymentSources
            );

            if ($isExcluded) {
                return 0;
            }
            return currencyConvertWithoutRound(
                $order->total_amount,
                $order->currency,
                'CAD'
            );
        });

        $totalBookings = $orders->count();

        // $avgBookingValue = $totalBookings > 0
        //     ? round($totalRevenue / $totalBookings, 2)
        //     : 0;

        $validBookings = $orders->filter(function ($order) use ($excludedPaymentSources) {
            return !in_array(
                strtolower($order->source ?? ''),
                $excludedPaymentSources
            );
        });

        $validRevenue = $totalRevenue;

        $avgBookingValue = $validBookings->count() > 0
            ? round($validRevenue / $validBookings->count(), 2)
            : 0;

        $totalTours = $orders->pluck('tour_id')->unique()->count();

        /*
        |--------------------------------------------------------------------------
        | 🔥 TOUR DROPDOWN
        |--------------------------------------------------------------------------
        */
        $tours = DB::table('tours')->select('id', 'title')->limit(20)->get();

        /*
        |--------------------------------------------------------------------------
        | 🔥 RESPONSE
        |--------------------------------------------------------------------------
        */
        
        if ($request->expectsJson()) {
            return response()->json([
                'tourAnalytics' => $tourAnalytics,
                'totalRevenue' => round($totalRevenue, 2),
                'totalBookings' => $totalBookings,
                'avgBookingValue' => $avgBookingValue,
                'totalTours' => $totalTours,
                'partners' => $partners
            ]);
        }

        return view('dashboard-tour-wise', compact(
            'tourAnalytics',
            'tours',
            'totalRevenue',
            'totalBookings',
            'avgBookingValue',
            'totalTours',
            'partners'

        ));
    }
    public function comparisonView(Request $request)
    {
        $partners = Partner::get();
        return view('admin.reports.comparison', compact('partners'));
    }
    public function comparisonData(Request $request)
    {
        $excludedPaymentSources = array_map('strtolower', excluded_payment_sources());
        $date1 = $request->date1;
        $date2 = $request->date2;

        if (!$date1 || !$date2) {
            return response()->json([]);
        }

        $req1 = clone $request;
        $req2 = clone $request;

        $req1->merge(['booking_date' => $date1 . ' - ' . $date1]);
        $req2->merge(['booking_date' => $date2 . ' - ' . $date2]);

        $reportController = app(\App\Http\Controllers\ReportController::class);

        $rows1 = $reportController->getInvoiceData($req1);
        $rows2 = $reportController->getInvoiceData($req2);

        // 🔥 GROUP BY PRODUCT
        $groupByProduct = function ($rows) use ($excludedPaymentSources) {
            return collect($rows)
                ->groupBy('product_name')
                ->map(function ($items) use ($excludedPaymentSources) {
                    return [
                        'revenue' => $items->sum(function ($r) use ($excludedPaymentSources) {

                                    $isExcluded = in_array(
                                        strtolower($r['source'] ?? ''),
                                        $excludedPaymentSources
                                    );

                                    if ($isExcluded) {
                                        return 0;
                                    }

                                    return $r['customer_total'] ?? 0;
                                }),
                        'passengers' => $items->sum(fn($r) => $r['adult'] + $r['child'] + $r['infant']),
                    ];
                });
        };

        $p1 = $groupByProduct($rows1);
        $p2 = $groupByProduct($rows2);

        $allProducts = $p1->keys()->merge($p2->keys())->unique();

        $products = [];

        foreach ($allProducts as $product) {

            $v1 = $p1[$product]['revenue'] ?? 0;
            $v2 = $p2[$product]['revenue'] ?? 0;

            $change = $v2 - $v1;
            $percent = $v1 ? ($change / $v1) * 100 : 0;

            $products[] = [
                'product' => $product,
                'date1' => round($v1, 2),
                'date2' => round($v2, 2),
                'change' => round($percent, 1),
            ];
        }

        // 🔥 SUMMARY
        $calc = function ($rows) use ($excludedPaymentSources) {
            $revenue = collect($rows)->sum(function ($r) use ($excludedPaymentSources) {

                $isExcluded = in_array(
                    strtolower($r['source'] ?? ''),
                    $excludedPaymentSources
                );

                if ($isExcluded) {
                    return 0;
                }

                return $r['customer_total'] ?? 0;
            });
            $bookings = count($rows);
            $passengers = collect($rows)->sum(fn($r) => $r['adult'] + $r['child'] + $r['infant']);
            $avg = $passengers > 0 ? $revenue / $passengers : 0;

            return [
                'revenue' => round($revenue, 2),
                'bookings' => $bookings,
                'passengers' => $passengers,
                'avg' => round($avg, 2),
            ];
        };

        return response()->json([
            'date1' => $calc($rows1),
            'date2' => $calc($rows2),
            'products' => $products, // 🔥 IMPORTANT
        ]);
    }

}
