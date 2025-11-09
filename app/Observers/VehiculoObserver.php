<?php

namespace App\Observers;
use FlipperBox\Crm\Models\Vehiculo;
use Illuminate\Validation\ValidationException;

class VehiculoObserver
{
    /**
     * Handle the Vehiculo "deleting" event.
     */
    public function deleting(Vehiculo $vehiculo): void
    {
        // Regla de Negocio: No se puede eliminar un vehículo si tiene
        // órdenes de trabajo que están 'Pendiente' o 'En Progreso'.
        if ($vehiculo->workOrders()->whereIn('status', ['Pendiente', 'En Progreso'])->exists()) {
            throw ValidationException::withMessages([
                'error' => 'No se puede eliminar este vehículo porque tiene órdenes de trabajo activas. Por favor, complete o cancele esas órdenes primero.',
            ]);
        }

        // Si llegamos aquí, significa que todas las órdenes asociadas (si las hay)
        // ya están 'Completada' o 'Cancelada', por lo que es seguro hacer un Soft Delete.
    }
}