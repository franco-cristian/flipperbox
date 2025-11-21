<?php

use FlipperBox\Crm\Http\Controllers\CrmController;
use FlipperBox\Crm\Http\Controllers\VehiculoController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    // Rutas de Clientes (gestionan el modelo User)
    Route::get('/clientes', [CrmController::class, 'index'])->middleware('can:ver clientes')->name('clientes.index');
    Route::get('/clientes/crear', [CrmController::class, 'create'])->middleware('can:crear clientes')->name('clientes.create');
    Route::post('/clientes', [CrmController::class, 'store'])->middleware('can:crear clientes')->name('clientes.store');
    Route::get('/clientes/{user}', [CrmController::class, 'show'])->middleware('can:ver clientes')->name('clientes.show');
    Route::get('/clientes/{user}/editar', [CrmController::class, 'edit'])->middleware('can:editar clientes')->name('clientes.edit');
    Route::patch('/clientes/{user}', [CrmController::class, 'update'])->middleware('can:editar clientes')->name('clientes.update');
    Route::delete('/clientes/{user}', [CrmController::class, 'destroy'])->middleware('can:eliminar clientes')->name('clientes.destroy');

    // Rutas Anidadas para Vehículos (refactorizadas)
    Route::prefix('/clientes/{user}/vehiculos')->name('clientes.vehiculos.')->group(function () {
        Route::get('/crear', [VehiculoController::class, 'create'])->middleware('can:crear vehiculos')->name('create');
        Route::post('/', [VehiculoController::class, 'store'])->middleware('can:crear vehiculos')->name('store');
        Route::get('/{vehiculo}/editar', [VehiculoController::class, 'edit'])->middleware('can:editar vehiculos')->name('edit');
        Route::patch('/{vehiculo}', [VehiculoController::class, 'update'])->middleware('can:editar vehiculos')->name('update');
        Route::delete('/{vehiculo}', [VehiculoController::class, 'destroy'])->middleware('can:eliminar vehiculos')->name('destroy');
    });
});
