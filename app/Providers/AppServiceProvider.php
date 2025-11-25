<?php

namespace App\Providers;

use App\Models\User;
use App\Observers\ProductObserver;
use App\Observers\ServiceObserver;
use App\Observers\UserObserver;
use App\Observers\VehiculoObserver;
use App\Observers\WorkOrderObserver;
use FlipperBox\Crm\Models\Vehiculo;
use FlipperBox\Inventory\Models\Product;
use FlipperBox\WorkManagement\Models\Service;
use FlipperBox\WorkManagement\Models\WorkOrder;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

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
        if ($this->app->environment('production')) {
            URL::forceScheme('https');

            if ($appUrl = config('app.url')) {
                URL::forceRootUrl($appUrl);
            }
        }

        Vite::prefetch(concurrency: 3);

        Product::observe(ProductObserver::class);
        Service::observe(ServiceObserver::class);
        User::observe(UserObserver::class);
        Vehiculo::observe(VehiculoObserver::class);
        WorkOrder::observe(WorkOrderObserver::class);
    }
}
