<?php

namespace App\Providers;

use App\Models\Order;
use App\Observers\OrderObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Order::observe(OrderObserver::class);
        Paginator::useBootstrap();
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION'");
        }
    }
}
