<?php

namespace FlipperBox\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use FlipperBox\Inventory\Models\Supplier;
use Inertia\Inertia;
use Inertia\Response;

class SupplierController extends Controller
{
    /**
     * Muestra una lista paginada de proveedores.
     */
    public function index(): Response
    {
        return Inertia::render('Inventory/Suppliers/Index', [
            'suppliers' => Supplier::latest()->paginate(10),
        ]);
    }
}
