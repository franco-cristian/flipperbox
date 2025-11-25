<?php

namespace FlipperBox\MechanicPanel\Http\Controllers;

use App\Http\Controllers\Controller;
use FlipperBox\WorkManagement\Models\WorkOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = Auth::user();

        // Buscamos las órdenes donde el mecánico es el usuario logueado
        // Solo mostramos Pendientes y En Progreso (lo que tiene que hacer)
        $misTareas = WorkOrder::with(['vehicle.cliente'])
            ->where('mechanic_id', $user->id)
            ->whereIn('status', ['Pendiente', 'En Progreso'])
            ->orderBy('entry_date', 'asc') // Las más viejas primero (urgentes)
            ->get();

        return Inertia::render('MechanicPanel/Dashboard', [
            'tasks' => $misTareas,
        ]);
    }
}
