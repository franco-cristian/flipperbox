<?php

namespace FlipperBox\Crm\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use FlipperBox\Crm\Actions\CreateUserAsClientAction;
use FlipperBox\Crm\Http\Requests\StoreUserAsClientRequest;
use FlipperBox\Crm\Http\Requests\UpdateUserAsClientRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CrmController extends Controller
{
    /**
     * Muestra una lista paginada de usuarios con el rol 'Cliente'.
     */
    public function index(): Response
    {
        return Inertia::render('Crm/Clientes/Index', [
            'clientes' => User::role('Cliente')->latest()->paginate(10),
        ]);
    }

    /**
     * Muestra el formulario para crear un nuevo cliente.
     */
    public function create(): Response
    {
        return Inertia::render('Crm/Clientes/Create');
    }

    /**
     * Almacena un nuevo usuario con el rol 'Cliente'.
     */
    public function store(StoreUserAsClientRequest $request, CreateUserAsClientAction $createUserAsClient): RedirectResponse
    {
        $createUserAsClient->execute($request->validated());
        return to_route('clientes.index')->with('success', 'Cliente creado exitosamente.');
    }

    /**
     * Muestra el detalle de un cliente, incluyendo sus vehículos.
     */
    public function show(User $user): Response
    {
        // Verificamos que el usuario solicitado sea realmente un cliente
        abort_if(!$user->hasRole('Cliente'), 404);

        $user->load('vehiculos');
        return Inertia::render('Crm/Clientes/Show', [
            'cliente' => $user,
        ]);
    }

    /**
     * Muestra el formulario para editar un cliente.
     */
    public function edit(User $user): Response
    {
        abort_if(!$user->hasRole('Cliente'), 404);

        return Inertia::render('Crm/Clientes/Edit', [
            'cliente' => $user,
        ]);
    }

    /**
     * Actualiza la información de un cliente.
     */
    public function update(UpdateUserAsClientRequest $request, User $user): RedirectResponse
    {
        $user->update($request->validated());
        return to_route('clientes.index')->with('success', 'Cliente actualizado exitosamente.');
    }

    /**
     * Elimina un cliente.
     * En este caso, eliminar a un cliente significa eliminar el usuario.
     * Soft Deletes.
     */
    public function destroy(User $user): RedirectResponse
    {
        // Podríamos añadir una validación para no permitir borrar usuarios con vehículos/órdenes activas
        $user->delete();
        return to_route('clientes.index')->with('success', 'Cliente eliminado exitosamente.');
    }
}