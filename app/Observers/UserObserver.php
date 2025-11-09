<?php

namespace App\Observers;

use App\Models\User;
use Illuminate\Validation\ValidationException;

class UserObserver
{
    /**
     * Handle the User "deleting" event.
     * Esta es una red de seguridad para cualquier intento de borrado.
     */
    public function deleting(User $user): void
    {
        // Regla de negocio universal: Un usuario no puede ser borrado si es dueño
        // de vehículos que tienen CUALQUIER orden de trabajo (historial).
        if ($user->vehiculos()->whereHas('workOrders')->exists()) {
            throw ValidationException::withMessages([
                'error' => 'No se puede eliminar este usuario porque tiene un historial de órdenes de trabajo asociadas.',
            ]);
        }
    }
}