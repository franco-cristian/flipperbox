<?php

namespace FlipperBox\ClientPortal\Http\Controllers;

use App\Http\Controllers\Controller;
use FlipperBox\Crm\Models\Vehiculo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class VehiculoController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Vehiculo::class, 'vehiculo');
    }

    public function index(): Response
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $vehiculos = $user->vehiculos()->latest()->paginate(10);

        return Inertia::render('ClientPortal/Vehicles/Index', [
            'vehiculos' => $vehiculos,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('ClientPortal/Vehicles/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'patente' => ['required', 'string', 'max:255', 'unique:vehiculos,patente'],
            'marca' => ['required', 'string', 'max:255'],
            'modelo' => ['required', 'string', 'max:255'],
            'anio' => ['required', 'integer', 'min:1950', 'max:'.date('Y')],
            'kilometraje' => ['nullable', 'integer', 'min:0'],
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $user->vehiculos()->create($validated);

        return to_route('cliente.vehiculos.index')->with('success', 'Vehículo agregado exitosamente.');
    }

    public function edit(Vehiculo $vehiculo): Response
    {
        return Inertia::render('ClientPortal/Vehicles/Edit', [
            'vehiculo' => $vehiculo,
        ]);
    }

    public function update(Request $request, Vehiculo $vehiculo): RedirectResponse
    {
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

    /**
     * Elimina (Soft Delete) un vehículo.
     */
    public function destroy(Vehiculo $vehiculo): RedirectResponse
    {
        $this->authorize('delete', $vehiculo);

        try {
            $vehiculo->delete();
        } catch (ValidationException $e) {
            // Atrapamos la excepción del observer y la convertimos en un mensaje de error flash.
            return back()->with('error', $e->validator->errors()->first());
        }

        return to_route('cliente.vehiculos.index')->with('success', 'Vehículo eliminado exitosamente.');
    }
}
