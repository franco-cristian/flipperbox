<?php
namespace FlipperBox\ClientPortal\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ClientPortalServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Route::middleware(['web', 'auth'])
            ->prefix('cliente')
            ->name('cliente.')
            ->group(function () {
                $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
            });
    }
}