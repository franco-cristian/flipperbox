<?php

namespace App\Policies;

use App\Models\User;
use FlipperBox\Crm\Models\Vehiculo;

class VehiculoPolicy
{
    /**
     * Realiza una verificación "antes" de cualquier otro método.
     * Si el usuario es un Admin, se le concede acceso total inmediatamente.
     */
    public function before(User $user, string $ability): bool|null
    {
        if ($user->hasRole('Admin')) {
            return true;
        }
        return null; // Dejar que las otras reglas decidan
    }

    /**
     * Determina si el usuario puede ver la lista de sus vehículos.
     */
    public function viewAny(User $user): bool
    {
        // Verificamos el permiso explícito que creamos para el rol 'Cliente'.
        return $user->can('ver mis vehiculos');
    }

    /**
     * Determina si el usuario puede ver un vehículo específico.
     */
    public function view(User $user, Vehiculo $vehiculo): bool
    {
        // Un usuario puede ver un vehículo si es SU vehículo.
        return $user->id === $vehiculo->user_id;
    }

    /**
     * Determina si el usuario puede crear vehículos.
     */
    public function create(User $user): bool
    {
        // Cualquier usuario con permiso para ver la lista, puede crear.
        return $user->can('ver mis vehiculos');
    }

    /**
     * Determina si el usuario puede actualizar un vehículo.
     */
    public function update(User $user, Vehiculo $vehiculo): bool
    {
        // Un usuario puede actualizar un vehículo si es SU vehículo.
        return $user->id === $vehiculo->user_id;
    }

    /**
     * Determina si el usuario puede eliminar un vehículo.
     */
    public function delete(User $user, Vehiculo $vehiculo): bool
    {
        // Un usuario puede eliminar un vehículo si es SU vehículo.
        return $user->id === $vehiculo->user_id;
    }
}