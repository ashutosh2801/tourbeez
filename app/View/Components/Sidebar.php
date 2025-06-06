<?php

namespace App\View\Components;

use App\Models\Addon;
use App\Models\Category;
use App\Models\City;
use App\Models\Collection;
use App\Models\Country;
use App\Models\Pickup;
use App\Models\Product;
use App\Models\State;
use App\Models\SubCategory;
use App\Models\TaxesFee;
use App\Models\Tour;
use App\Models\Tourtype;
use App\Models\User;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class Sidebar extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        $userCount = User::count();
        view()->share('userCount',$userCount);
        
        $RoleCount = Role::count();
        view()->share('RoleCount',$RoleCount);
        
        $PermissionCount = Permission::count();
        view()->share('PermissionCount',$PermissionCount);
        
        $CategoryCount = Category::count();
        view()->share('CategoryCount',$CategoryCount);

        $TourTypeCount = Tourtype::count();
        view()->share('TourTypeCount',$TourTypeCount);
        
        $AddonCount = Addon::count();
        view()->share('AddonCount',$AddonCount);
        
        $PickupCount = Pickup::count();
        view()->share('PickupCount',$PickupCount);
        
        // $ProductCount = Product::count();
        // view()->share('ProductCount',$ProductCount);

        $TourCount = Tour::count();
        view()->share('TourCount',$TourCount);

        $TaxesCount = TaxesFee::count();
        view()->share('TaxesCount',$TaxesCount);

        $CountryCount = Country::count();
        view()->share('CountryCount',$CountryCount);

        $StateCount = State::count();
        view()->share('StateCount',$StateCount);

        $CityCount = City::count();
        view()->share('CityCount',$CityCount);



    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.sidebar');
    }
}
