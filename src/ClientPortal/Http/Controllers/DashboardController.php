<?php

namespace FlipperBox\ClientPortal\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Muestra el dashboard del cliente, incluyendo su flota de vehículos y el historial de servicios.
     */
    public function index(): Response
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Eager Loading anidado: Cargamos los vehículos del usuario,
        // y para cada vehículo, cargamos sus órdenes de trabajo ordenadas por la más reciente
        $user->load(['vehiculos.workOrders' => function ($query) {
            $query->latest('entry_date');
        }]);

        return Inertia::render('ClientPortal/Dashboard', [
            'user' => $user,
        ]);
    }
}
