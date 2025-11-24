<?php

namespace App\Observers;

use FlipperBox\WorkManagement\Models\Service;
use Illuminate\Validation\ValidationException;

class ServiceObserver
{
    /**
     * Handle the Service "deleting" event.
     */
    public function deleting(Service $service): void
    {
        // Regla de Integridad: No se puede eliminar un servicio si ha sido usado en alguna orden de trabajo.
        if ($service->workOrders()->exists()) {
            throw ValidationException::withMessages([
                'error' => "No se puede eliminar el servicio '{$service->name}' porque forma parte del historial de Órdenes de Trabajo. Considere editarlo o cambiar su nombre a 'OBSOLETO'.",
            ]);
        }
    }
}
