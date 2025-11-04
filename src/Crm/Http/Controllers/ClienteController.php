<?php

namespace FlipperBox\Crm\Http\Controllers;

use App\Http\Controllers\Controller;
use FlipperBox\Crm\Actions\CrearNuevoClienteAction;
use FlipperBox\Crm\Http\Requests\StoreClienteRequest;
use FlipperBox\Crm\Http\Requests\UpdateClienteRequest;
use FlipperBox\Crm\Models\Cliente;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ClienteController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Crm/Clientes/Index', [
            'clientes' => Cliente::latest()->paginate(10),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Crm/Clientes/Create');
    }

    public function store(StoreClienteRequest $request, CrearNuevoClienteAction $crearNuevoCliente): RedirectResponse
    {
        $crearNuevoCliente->execute($request->validated());
        return to_route('clientes.index')->with('success', 'Cliente creado exitosamente.');
    }

    public function show(Cliente $cliente): Response
    {
        $cliente->load('vehiculos');
        return Inertia::render('Crm/Clientes/Show', [
            'cliente' => $cliente,
        ]);
    }

    public function edit(Cliente $cliente): Response
    {
        return Inertia::render('Crm/Clientes/Edit', [
            'cliente' => $cliente,
        ]);
    }

    public function update(UpdateClienteRequest $request, Cliente $cliente): RedirectResponse
    {
        $cliente->update($request->validated());
        return to_route('clientes.index')->with('success', 'Cliente actualizado exitosamente.');
    }

    public function destroy(Cliente $cliente): RedirectResponse
    {
        $cliente->delete(); // Usamos Soft Delete
        return to_route('clientes.index')->with('success', 'Cliente eliminado exitosamente.');
    }
}