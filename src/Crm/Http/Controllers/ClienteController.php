<?php

namespace FlipperBox\Crm\Http\Controllers;

use App\Http\Controllers\Controller;
use FlipperBox\Crm\Actions\CrearNuevoClienteAction;
use FlipperBox\Crm\Http\Requests\StoreClienteRequest;
use FlipperBox\Crm\Models\Cliente;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ClienteController extends Controller
{
    /**
     * Muestra una lista paginada de clientes.
     */
    public function index(): Response
    {
        return Inertia::render('Crm/Clientes/Index', [
            'clientes' => Cliente::latest()->paginate(10),
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
     * Almacena un nuevo cliente en la base de datos.
     */
    public function store(StoreClienteRequest $request, CrearNuevoClienteAction $crearNuevoCliente): RedirectResponse
    {
        $crearNuevoCliente->execute($request->validated());

        return to_route('clientes.index')->with('success', 'Cliente creado exitosamente.');
    }

    public function show(Cliente $cliente): Response
    {
        // Cargamos la relación 'vehiculos' para pasarla al frontend
        $cliente->load('vehiculos');

        return Inertia::render('Crm/Clientes/Show', [
            'cliente' => $cliente,
        ]);
    }
}
