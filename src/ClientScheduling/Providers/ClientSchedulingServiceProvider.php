<?php

namespace FlipperBox\ClientScheduling\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ClientSchedulingServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Route::middleware(['web', 'auth'])
            ->prefix('cliente/reservas')
            ->name('cliente.reservations.')
            ->group(function () {
                $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
            });
    }
}