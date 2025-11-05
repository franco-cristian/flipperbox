<?php

use FlipperBox\Inventory\Http\Controllers\ProductController;
use FlipperBox\Inventory\Http\Controllers\SupplierController;
use Illuminate\Support\Facades\Route;

// Rutas para Productos
Route::middleware('can:ver inventario')->prefix('inventario')->name('inventario.')->group(function () {
    Route::get('/productos', [ProductController::class, 'index'])->name('products.index');
    Route::get('/productos/crear', [ProductController::class, 'create'])->middleware('can:gestionar inventario')->name('products.create');
    Route::post('/productos', [ProductController::class, 'store'])->middleware('can:gestionar inventario')->name('products.store');
});

// Rutas para Proveedores
Route::middleware('can:ver proveedores')->prefix('inventario')->name('inventario.')->group(function () {
    Route::get('/proveedores', [SupplierController::class, 'index'])->name('suppliers.index');
    // Agregaremos más rutas aquí
});