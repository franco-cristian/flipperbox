<?php

use FlipperBox\Inventory\Http\Controllers\ProductController;
use FlipperBox\Inventory\Http\Controllers\SupplierController;
use Illuminate\Support\Facades\Route;

// Rutas para Productos
Route::middleware('can:ver inventario')->prefix('inventario')->name('inventario.')->group(function () {
    Route::get('/productos', [ProductController::class, 'index'])->name('products.index');

    Route::middleware('can:gestionar inventario')->group(function () {
        Route::get('/productos/crear', [ProductController::class, 'create'])->name('products.create');
        Route::post('/productos', [ProductController::class, 'store'])->name('products.store');
        Route::get('/productos/{product}/editar', [ProductController::class, 'edit'])->name('products.edit');
        Route::patch('/productos/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::delete('/productos/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
    });
});

// Rutas para Proveedores
Route::prefix('inventario')->name('inventario.')->group(function () {
    Route::get('/proveedores', [SupplierController::class, 'index'])->middleware('can:ver proveedores')->name('suppliers.index');

    Route::middleware('can:gestionar proveedores')->group(function () {
        Route::get('/proveedores/crear', [SupplierController::class, 'create'])->name('suppliers.create');
        Route::post('/proveedores', [SupplierController::class, 'store'])->name('suppliers.store');
        Route::get('/proveedores/{supplier}/editar', [SupplierController::class, 'edit'])->name('suppliers.edit');
        Route::patch('/proveedores/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update');
        Route::delete('/proveedores/{supplier}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');
    });
});
