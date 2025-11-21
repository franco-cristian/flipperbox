<?php

namespace FlipperBox\Inventory\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class InventoryServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Route::middleware(['web', 'auth'])
            ->group(function () {
                $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
            });
    }
}
