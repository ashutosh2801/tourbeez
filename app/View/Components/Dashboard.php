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

class Dashboard extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        $user_count = User::where('user_type', 'Member')->count();
        view()->share('user_count',$user_count);
        
        $tour_count = Tour::count();
        view()->share('tour_count',$tour_count);
        
        $category_count = Category::count();
        view()->share('category_count',$category_count);
        
        
        $staff_count = User::where('user_type', '<>', 'Member')->where('user_type', '<>', 'Super Admin')->count();
        view()->share('staff_count',$staff_count);
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.dashboard');
    }
}
