<?php

namespace FlipperBox\Crm\Http\Controllers;

use App\Http\Controllers\Controller;
use FlipperBox\Crm\Http\Requests\UpdateVehiculoRequest;
use FlipperBox\Crm\Models\Cliente;
use FlipperBox\Crm\Models\Vehiculo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VehiculoController extends Controller
{
    public function create(Cliente $cliente): Response
    {
        return Inertia::render('Crm/Vehiculos/Create', [
            'cliente' => $cliente,
        ]);
    }

    public function store(Request $request, Cliente $cliente): RedirectResponse
    {
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
        $cliente->vehiculos()->create($validated);
        return to_route('clientes.show', $cliente)->with('success', 'Vehículo agregado exitosamente.');
    }

    public function edit(Cliente $cliente, Vehiculo $vehiculo): Response
    {
        return Inertia::render('Crm/Vehiculos/Edit', [
            'cliente' => $cliente,
            'vehiculo' => $vehiculo,
        ]);
    }

    public function update(UpdateVehiculoRequest $request, Cliente $cliente, Vehiculo $vehiculo): RedirectResponse
    {
        $vehiculo->update($request->validated());
        return to_route('clientes.show', $cliente)->with('success', 'Vehículo actualizado exitosamente.');
    }

    public function destroy(Cliente $cliente, Vehiculo $vehiculo): RedirectResponse
    {
        $this->authorize('eliminar vehiculos');
        $vehiculo->delete();
        return to_route('clientes.show', $cliente)->with('success', 'Vehículo eliminado exitosamente.');
    }
}