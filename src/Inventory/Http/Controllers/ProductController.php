<?php

namespace FlipperBox\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use FlipperBox\Inventory\Actions\ActualizarProductoAction;
use FlipperBox\Inventory\Actions\CrearNuevoProductoAction;
use FlipperBox\Inventory\Http\Requests\StoreProductRequest;
use FlipperBox\Inventory\Http\Requests\UpdateProductRequest;
use FlipperBox\Inventory\Models\Product;
use FlipperBox\Inventory\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Inventory/Products/Index', [
            'products' => Product::with('suppliers')->latest()->paginate(10),
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

    public function edit(Product $product): Response
    {
        // Cargamos la relación para saber qué proveedor tiene asociado
        $product->load('suppliers');

        return Inertia::render('Inventory/Products/Edit', [
            'product' => $product,
            'suppliers' => Supplier::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product, ActualizarProductoAction $actualizarProducto): RedirectResponse
    {
        $actualizarProducto->execute($product, $request->validated());
        return to_route('inventario.products.index')->with('success', 'Producto actualizado exitosamente.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        try {
            // Esto disparará el Observer. Si falla, el catch lo manejará.
            $product->delete();
        } catch (ValidationException $e) {
            return back()->with('error', $e->validator->errors()->first('error'));
        }
        return to_route('inventario.products.index')->with('success', 'Producto eliminado exitosamente.');
    }
}
