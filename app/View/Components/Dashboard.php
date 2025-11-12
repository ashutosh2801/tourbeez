<?php

namespace App\View\Components;

use App\Models\Category;
use App\Models\Collection;
use App\Models\Order;
use App\Models\Product;
use App\Models\Tour;
use App\Models\User;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Carbon\Carbon;

class Dashboard extends Component
{
    public $performance;
    public $days;
    /**
     * Create a new component instance.
     */


    public function __construct($days = 7)
    {

                // Default metrics
        $user_count = User::where('user_type', 'Member')->count();
        $tour_count = Tour::count();
        $category_count = Category::count();
        $staff_count = User::whereNotIn('user_type', ['Member', 'Super Admin'])->count();

        view()->share(compact('user_count', 'tour_count', 'category_count', 'staff_count'));

        $this->days = $days;

        $startDate = Carbon::now()->subDays($days)->startOfDay();
        $endDate   = Carbon::now()->endOfDay();

        // Fetch orders for the selected date range
        $orders = Order::whereBetween('created_at', [$startDate, $endDate])
                       ->whereNull('deleted_at')
                       ->get(['currency', 'total_amount', 'booked_amount', 'balance_amount', 'order_status']);

        // Initialize totals
        $totals = [
            'number_of_orders' => 0,
            'value_of_orders'  => 0,
            'total_paid'       => 0,
            'total_refund'     => 0,
            'total_discount'   => 0,
            'total_owed'       => 0,
        ];

        foreach ($orders as $order) {
            // Convert all values to USD
            $convertedTotal   = currencyConvert($order->total_amount ?? 0, $order->currency, 'USD');
            $convertedBooked  = currencyConvert($order->booked_amount ?? 0, $order->currency, 'USD');
            $convertedBalance = currencyConvert($order->balance_amount ?? 0, $order->currency, 'USD');

            $totals['number_of_orders']++;
            $totals['value_of_orders']  += $convertedTotal;
            $totals['total_paid']       += ($convertedTotal - $convertedBalance);
            $totals['total_owed']       += $convertedBalance;
            $totals['total_refund']     += ($order->order_status == 4) ? $convertedTotal : 0;
            $totals['total_discount']   += 0;
            // $totals['total_discount']   += $convertedBooked > 0 ? ($convertedTotal - $convertedBooked) : 0;
        }

        // Round all results to 2 decimals for clean display
        foreach ($totals as $key => $val) {
            if ($key !== 'number_of_orders') {
                $totals[$key] = round($val, 2);
            }
        }

        $this->performance = $totals;
    }



    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.dashboard');
    }
}
