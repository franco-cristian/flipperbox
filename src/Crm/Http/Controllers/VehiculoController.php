<?php

namespace FlipperBox\Crm\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use FlipperBox\Crm\Http\Requests\UpdateVehiculoRequest;
use FlipperBox\Crm\Models\Vehiculo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class VehiculoController extends Controller
{
    /**
     * Muestra el formulario para crear un nuevo vehículo para un cliente (User).
     */
    public function create(User $user): Response
    {
        abort_if(! $user->hasRole('Cliente'), 404);

        return Inertia::render('Crm/Vehiculos/Create', [
            'cliente' => $user,
        ]);
    }

    /**
     * Almacena un nuevo vehículo y lo asocia a un cliente (User).
     */
    public function store(Request $request, User $user): RedirectResponse
    {
        abort_if(! $user->hasRole('Cliente'), 404);

        $validated = $request->validate([
            'patente' => ['required', 'string', 'max:255', 'unique:vehiculos,patente'],
            'marca' => ['required', 'string', 'max:255'],
            'modelo' => ['required', 'string', 'max:255'],
            'anio' => ['required', 'integer', 'min:1950', 'max:'.date('Y')],
            'kilometraje' => ['nullable', 'integer', 'min:0'],
            'vin' => ['nullable', 'string', 'max:255', 'unique:vehiculos,vin'],
            'numero_motor' => ['nullable', 'string', 'max:255'],
            'observaciones' => ['nullable', 'string'],
        ]);

        // Creamos el vehículo a través de la relación definida en el modelo User
        $user->vehiculos()->create($validated);

        return to_route('clientes.show', $user->id)->with('success', 'Vehículo agregado exitosamente.');
    }

    /**
     * Muestra el formulario para editar un vehículo.
     * El Model-Route Binding se encarga de que $user y $vehiculo sean los correctos.
     */
    public function edit(User $user, Vehiculo $vehiculo): Response
    {
        abort_if(! $user->hasRole('Cliente'), 404);

        return Inertia::render('Crm/Vehiculos/Edit', [
            'cliente' => $user,
            'vehiculo' => $vehiculo,
        ]);
    }

    /**
     * Actualiza un vehículo existente.
     */
    public function update(UpdateVehiculoRequest $request, User $user, Vehiculo $vehiculo): RedirectResponse
    {
        $vehiculo->update($request->validated());

        return to_route('clientes.show', $user->id)->with('success', 'Vehículo actualizado exitosamente.');
    }

    /**
     * Elimina (Soft Delete) un vehículo.
     */
    public function destroy(User $user, Vehiculo $vehiculo): RedirectResponse
    {
        try {
            $vehiculo->delete();
        } catch (ValidationException $e) {
            return back()->with('error', $e->validator->errors()->first());
        }

        return to_route('clientes.show', $user->id)->with('success', 'Vehículo eliminado exitosamente.');
    }
}
