<?php

namespace FlipperBox\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use FlipperBox\Inventory\Actions\CrearNuevoProductoAction;
use FlipperBox\Inventory\Http\Requests\StoreProductRequest;
use FlipperBox\Inventory\Models\Product;
use FlipperBox\Inventory\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Inventory/Products/Index', [
            'products' => Product::latest()->paginate(10),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Inventory/Products/Create', [
            'suppliers' => Supplier::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(StoreProductRequest $request, CrearNuevoProductoAction $crearNuevoProducto): RedirectResponse
    {
        $crearNuevoProducto->execute($request->validated());
        return to_route('inventario.products.index')->with('success', 'Producto creado exitosamente.');
    }
}