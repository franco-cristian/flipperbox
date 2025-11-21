<?php

namespace App\Providers;

use App\Policies\VehiculoPolicy;
use FlipperBox\Crm\Models\Vehiculo;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class PolicyServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // Dejamos este array vacío a propósito. Usaremos el método boot() para registrar.
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        Gate::policy(Vehiculo::class, VehiculoPolicy::class);

    }
}
