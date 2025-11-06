<?php

use FlipperBox\Inventory\Http\Controllers\ProductController;
use FlipperBox\Inventory\Http\Controllers\SupplierController;
use Illuminate\Support\Facades\Route;

// Rutas para Productos
Route::middleware('can:ver inventario')->prefix('inventario')->name('inventario.')->group(function () {
    Route::get('/productos', [ProductController::class, 'index'])->name('products.index');
    
    // Rutas protegidas por el permiso de gestión
    Route::middleware('can:gestionar inventario')->group(function() {
        Route::get('/productos/crear', [ProductController::class, 'create'])->name('products.create');
        Route::post('/productos', [ProductController::class, 'store'])->name('products.store');
        Route::get('/productos/{product}/editar', [ProductController::class, 'edit'])->name('products.edit');
        Route::patch('/productos/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::delete('/productos/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
    });
});

// Rutas para Proveedores
Route::middleware('can:ver proveedores')->prefix('inventario')->name('inventario.')->group(function () {
    Route::get('/proveedores', [SupplierController::class, 'index'])->name('suppliers.index');
    // Agregaremos más rutas aquí
});