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
        // The session mode is a production MySQL setting. Avoid opening the
        // configured local database while booting tests, artisan config
        // commands, or environments that intentionally use SQLite.
        if (app()->environment('production') && DB::connection()->getDriverName() === 'mysql') {
            DB::statement("SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION'");
        }
    }
}
