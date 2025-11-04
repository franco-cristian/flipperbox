<?php
use FlipperBox\Inventory\Http\Controllers\ProductController;
use FlipperBox\Inventory\Http\Controllers\SupplierController;
use Illuminate\Support\Facades\Route;

Route::middleware('can:ver inventario')->prefix('inventario')->name('inventario.')->group(function () {
    Route::get('/productos', [ProductController::class, 'index'])->name('products.index');
    // Agregaremos más rutas aquí (create, store, edit, etc.)
});

Route::middleware('can:ver proveedores')->prefix('inventario')->name('inventario.')->group(function () {
    Route::get('/proveedores', [SupplierController::class, 'index'])->name('suppliers.index');
    // Agregaremos más rutas aquí
});