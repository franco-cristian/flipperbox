<?php

namespace FlipperBox\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use FlipperBox\Inventory\Models\Product;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    /**
     * Muestra una lista paginada de productos.
     */
    public function index(): Response
    {
        return Inertia::render('Inventory/Products/Index', [
            'products' => Product::latest()->paginate(10),
        ]);
    }
}
