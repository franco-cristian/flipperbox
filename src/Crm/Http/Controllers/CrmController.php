<?php

namespace FlipperBox\Crm\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use FlipperBox\Crm\Actions\CreateUserAsClientAction;
use FlipperBox\Crm\Http\Requests\StoreUserAsClientRequest;
use FlipperBox\Crm\Http\Requests\UpdateUserAsClientRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class CrmController extends Controller
{
    public function index(Request $request): Response
    {
        $query = User::role('Cliente')->latest();

        // Lógica de Búsqueda Global
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('apellido', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('telefono', 'like', "%{$search}%")
                    ->orWhere('documento_valor', 'like', "%{$search}%");
            });
        }

        return Inertia::render('Crm/Clientes/Index', [
            'clientes' => $query->paginate(10)->withQueryString(),
            'filters' => $request->only(['search']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Crm/Clientes/Create');
    }

    public function store(StoreUserAsClientRequest $request, CreateUserAsClientAction $createUserAsClient): RedirectResponse
    {
        $createUserAsClient->execute($request->validated());

        return to_route('clientes.index')->with('success', 'Cliente creado exitosamente.');
    }

    public function show(User $user): Response
    {
        abort_if(! $user->hasRole('Cliente'), 404);
        $user->load('vehiculos');

        return Inertia::render('Crm/Clientes/Show', [
            'cliente' => $user,
        ]);
    }

    public function edit(User $user): Response
    {
        abort_if(! $user->hasRole('Cliente'), 404);

        return Inertia::render('Crm/Clientes/Edit', [
            'cliente' => $user,
        ]);
    }

    public function update(UpdateUserAsClientRequest $request, User $user): RedirectResponse
    {
        $user->update($request->validated());

        return to_route('clientes.index')->with('success', 'Cliente actualizado exitosamente.');
    }

    public function destroy(User $user): RedirectResponse
    {
        // 1. Verificación de Rol explícita
        if (! $user->hasRole('Cliente')) {
            return back()->with('error', 'La acción de eliminación solo es aplicable a usuarios con el rol de Cliente.');
        }

        // 2. Verificación de Regla de Negocio explícita
        if ($user->vehiculos()->whereHas('workOrders')->exists()) {
            return back()->with('error', 'No se puede eliminar este cliente porque tiene órdenes de trabajo asociadas. Por favor, gestione esas órdenes primero.');
        }

        // Si pasa las validaciones, procedemos a eliminar
        try {
            $user->delete();
        } catch (ValidationException $e) {
            // El catch se mantiene como una segunda capa de seguridad por si un Observer lanza una excepción
            return back()->with('error', $e->validator->errors()->first('error'));
        }

        return to_route('clientes.index')->with('success', 'Cliente eliminado exitosamente.');
    }
}
