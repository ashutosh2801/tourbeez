<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Tour;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function dashboard( Request $request )
    {
        $fromDate = $request->from_date ?? now()->startOfMonth()->format('Y-m-d');
        $toDate   = $request->to_date ?? now()->format('Y-m-d');
        $tourId   = $request->tour_id;

        $query = Order::selectRaw("
                    tours.id,
                    tours.title,
                    COUNT(orders.id) as bookings,
                    SUM(orders.total_amount) as revenue
                ")
                ->join('tours', 'tours.id', '=', 'orders.tour_id')
                ->where('orders.payment_status', 'paid')
                ->groupBy(
                    'tours.id',
                    'tours.title'
                )
                ->orderByDesc('revenue')
                ->limit(10);

        if (!empty($tourId)) {
            $query->where('tours.id', $tourId);
        }

        $tourAnalytics = $query->get();

        $tours = Tour::select('id', 'title')->limit(10)->get();

        // echo '<pre>'; print_r($tours);exit;
        $totalRevenue = Order::where('payment_status', 'paid')
            ->whereDate('created_at', '>=', $fromDate)
            ->whereDate('created_at', '<=', $toDate)
            ->sum('total_amount');

        $totalBookings = Order::where('payment_status', 'paid')
            ->whereDate('created_at', '>=', $fromDate)
            ->whereDate('created_at', '<=', $toDate)
            ->count();

        $avgBookingValue = $totalBookings > 0
            ? round($totalRevenue / $totalBookings, 2)
            : 0;

        $totalTours = Tour::count();

        return view('dashboard', compact( 
                'tourAnalytics', 
                'tours', 
                'fromDate', 
                'toDate',
                'totalRevenue',
                'totalBookings',
                'avgBookingValue',
                'totalTours' 
            ));
    }

    public function dashboardProductWise( Request $request )
    {
        $fromDate = $request->from_date ?? now()->startOfMonth()->format('Y-m-d');
        $toDate   = $request->to_date ?? now()->format('Y-m-d');
        $tourId   = $request->tour_id;

        $query = Order::selectRaw("
                    tours.id,
                    tours.title,
                    COUNT(orders.id) as bookings,
                    SUM(orders.total_amount) as revenue
                ")
                ->join('tours', 'tours.id', '=', 'orders.tour_id')
                ->where('orders.payment_status', 'paid')
                ->groupBy(
                    'tours.id',
                    'tours.title'
                )
                ->orderByDesc('revenue')
                ->limit(10);

        if (!empty($tourId)) {
            $query->where('tours.id', $tourId);
        }

        $tourAnalytics = $query->get();

        $tours = Tour::select('id', 'title')->limit(10)->get();

        // echo '<pre>'; print_r($tours);exit;
        $totalRevenue = Order::where('payment_status', 'paid')
            ->whereDate('created_at', '>=', $fromDate)
            ->whereDate('created_at', '<=', $toDate)
            ->sum('total_amount');

        $totalBookings = Order::where('payment_status', 'paid')
            ->whereDate('created_at', '>=', $fromDate)
            ->whereDate('created_at', '<=', $toDate)
            ->count();

        $avgBookingValue = $totalBookings > 0
            ? round($totalRevenue / $totalBookings, 2)
            : 0;

        $totalTours = Tour::count();

        return view('dashboard_product_wise', compact( 
                'tourAnalytics', 
                'tours', 
                'fromDate', 
                'toDate',
                'totalRevenue',
                'totalBookings',
                'avgBookingValue',
                'totalTours' 
            ));
    }

    public function dashboardDateWise( Request $request )
    {
        $fromDate = $request->from_date ?? now()->startOfMonth()->format('Y-m-d');
        $toDate   = $request->to_date ?? now()->format('Y-m-d');
        $tourId   = $request->tour_id;

        $query = Order::selectRaw("
                    tours.id,
                    tours.title,
                    COUNT(orders.id) as bookings,
                    SUM(orders.total_amount) as revenue
                ")
                ->join('tours', 'tours.id', '=', 'orders.tour_id')
                ->where('orders.payment_status', 'paid')
                ->groupBy(
                    'tours.id',
                    'tours.title'
                )
                ->orderByDesc('revenue')
                ->limit(10);

        if (!empty($tourId)) {
            $query->where('tours.id', $tourId);
        }

        $tourAnalytics = $query->get();

        $tours = Tour::select('id', 'title')->limit(10)->get();

        // echo '<pre>'; print_r($tours);exit;
        $totalRevenue = Order::where('payment_status', 'paid')
            ->whereDate('created_at', '>=', $fromDate)
            ->whereDate('created_at', '<=', $toDate)
            ->sum('total_amount');

        $totalBookings = Order::where('payment_status', 'paid')
            ->whereDate('created_at', '>=', $fromDate)
            ->whereDate('created_at', '<=', $toDate)
            ->count();

        $avgBookingValue = $totalBookings > 0
            ? round($totalRevenue / $totalBookings, 2)
            : 0;

        $totalTours = Tour::count();

        return view('admin.dashboard.date_wise', compact( 
                'tourAnalytics', 
                'tours', 
                'fromDate', 
                'toDate',
                'totalRevenue',
                'totalBookings',
                'avgBookingValue',
                'totalTours' 
            ));
    }

}
