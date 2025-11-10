<?php

namespace App\Policies;

use App\Models\User;
use FlipperBox\Crm\Models\Vehiculo;

class VehiculoPolicy
{
    /**
     * Determine whether the user can view any models.
     * Un usuario puede ver la lista si es un cliente.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('Cliente');
    }

    /**
     * Determine whether the user can view the model.
     * Un usuario puede ver un vehículo si es SU vehículo.
     */
    public function view(User $user, Vehiculo $vehiculo): bool
    {
        return $user->id === $vehiculo->user_id;
    }

    /**
     * Determine whether the user can create models.
     * Un usuario puede crear vehículos si es un cliente.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('Cliente');
    }

    /**
     * Determine whether the user can update the model.
     * Un usuario puede actualizar un vehículo si es SU vehículo.
     */
    public function update(User $user, Vehiculo $vehiculo): bool
    {
        return $user->id === $vehiculo->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     * Un usuario puede eliminar un vehículo si es SU vehículo.
     */
    public function delete(User $user, Vehiculo $vehiculo): bool
    {
        return $user->id === $vehiculo->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Vehiculo $vehiculo): bool
    {
        return $user->id === $vehiculo->user_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Vehiculo $vehiculo): bool
    {
        return false; // Nadie puede borrar permanentemente por ahora.
    }
}