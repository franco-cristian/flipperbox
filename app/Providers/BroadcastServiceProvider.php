<?php

namespace App\Providers;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Carga las rutas de autorización para los canales de broadcasting.
        Broadcast::routes();

        require base_path('routes/channels.php');
    }
}
