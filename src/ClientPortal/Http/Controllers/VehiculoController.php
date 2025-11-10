<?php

namespace FlipperBox\ClientPortal\Http\Controllers;

use App\Http\Controllers\Controller;
use FlipperBox\Crm\Models\Vehiculo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class VehiculoController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', Vehiculo::class);

        $vehiculos = Auth::user()->vehiculos()->latest()->paginate(10);
        return Inertia::render('ClientPortal/Vehicles/Index', [
            'vehiculos' => $vehiculos,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Vehiculo::class);
        return Inertia::render('ClientPortal/Vehicles/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Vehiculo::class);

        $validated = $request->validate([
            'patente' => ['required', 'string', 'max:255', 'unique:vehiculos,patente'],
            'marca' => ['required', 'string', 'max:255'],
            'modelo' => ['required', 'string', 'max:255'],
            'anio' => ['required', 'integer', 'min:1950', 'max:'.date('Y')],
            'kilometraje' => ['nullable', 'integer', 'min:0'],
        ]);

        Auth::user()->vehiculos()->create($validated);

        return to_route('cliente.vehiculos.index')->with('success', 'Vehículo agregado exitosamente.');
    }

    public function edit(Vehiculo $vehiculo): Response
    {
        $this->authorize('update', $vehiculo);

        return Inertia::render('ClientPortal/Vehicles/Edit', [
            'vehiculo' => $vehiculo,
        ]);
    }

    public function update(Request $request, Vehiculo $vehiculo): RedirectResponse
    {
        $this->authorize('update', $vehiculo);

        $validated = $request->validate([
            'patente' => ['required', 'string', 'max:255', Rule::unique('vehiculos', 'patente')->ignore($vehiculo->id)],
            'marca' => ['required', 'string', 'max:255'],
            'modelo' => ['required', 'string', 'max:255'],
            'anio' => ['required', 'integer', 'min:1950', 'max:'.date('Y')],
            'kilometraje' => ['nullable', 'integer', 'min:0'],
        ]);

        $vehiculo->update($validated);
        return to_route('cliente.vehiculos.index')->with('success', 'Vehículo actualizado exitosamente.');
    }

    public function destroy(Vehiculo $vehiculo): RedirectResponse
    {
        $this->authorize('delete', $vehiculo);

        $vehiculo->delete();
        return to_route('cliente.vehiculos.index')->with('success', 'Vehículo eliminado exitosamente.');
    }
}