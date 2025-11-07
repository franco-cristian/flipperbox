<?php
namespace FlipperBox\Scheduling\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class SchedulingServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Route::middleware(['web', 'auth'])
            ->group(function () {
                Route::middleware('can:gestionar cupos')
                     ->prefix('admin/scheduling')
                     ->name('admin.scheduling.')
                     ->group(function () {
                         $this->loadRoutesFrom(__DIR__.'/../routes/admin.php');
                     });
            });
    }
}