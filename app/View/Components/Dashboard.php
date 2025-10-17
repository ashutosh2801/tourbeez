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

        // --- Performance Metrics ---
        $this->days = $days;

        $startDate = Carbon::now()->subDays($days)->startOfDay();
        $endDate   = Carbon::now()->endOfDay();

        // SupplierOrderScope auto-applies for suppliers
        $orders = Order::whereBetween('created_at', [$startDate, $endDate])
                       ->whereNull('deleted_at');

        // Cache sums to minimize query load
        $totalAmount   = $orders->sum('total_amount');
        $bookedAmount  = $orders->sum('booked_amount');
        $balanceAmount = $orders->sum('balance_amount');
        $refundAmount  = (clone $orders)->where('order_status', 4)->sum('total_amount');

        $this->performance = [
            'number_of_orders' => $orders->count(),
            'value_of_orders'  => $totalAmount,
            'total_paid'       => $totalAmount - $balanceAmount,
            'total_refund'     => $refundAmount,
            'total_discount'   => $bookedAmount > 0 ? ($totalAmount - $bookedAmount) : 0,
            'total_owed'       => $balanceAmount,
        ];
    }


    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.dashboard');
    }
}
