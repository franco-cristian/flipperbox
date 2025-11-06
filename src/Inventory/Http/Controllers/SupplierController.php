<?php

namespace FlipperBox\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use FlipperBox\Inventory\Http\Requests\StoreSupplierRequest;
use FlipperBox\Inventory\Http\Requests\UpdateSupplierRequest;
use FlipperBox\Inventory\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SupplierController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Inventory/Suppliers/Index', [
            'suppliers' => Supplier::latest()->paginate(10),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Inventory/Suppliers/Create');
    }

    public function store(StoreSupplierRequest $request): RedirectResponse
    {
        Supplier::create($request->validated());
        return to_route('inventario.suppliers.index')->with('success', 'Proveedor creado exitosamente.');
    }

    public function edit(Supplier $supplier): Response
    {
        return Inertia::render('Inventory/Suppliers/Edit', [
            'supplier' => $supplier,
        ]);
    }

    public function update(UpdateSupplierRequest $request, Supplier $supplier): RedirectResponse
    {
        $supplier->update($request->validated());
        return to_route('inventario.suppliers.index')->with('success', 'Proveedor actualizado exitosamente.');
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        $supplier->delete(); // Usamos Soft Delete
        return to_route('inventario.suppliers.index')->with('success', 'Proveedor eliminado exitosamente.');
    }
}