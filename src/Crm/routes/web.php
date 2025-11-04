<?php

use FlipperBox\Crm\Http\Controllers\ClienteController;
use FlipperBox\Crm\Http\Controllers\VehiculoController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/clientes', [ClienteController::class, 'index'])->middleware('can:ver clientes')->name('clientes.index');
    Route::get('/clientes/crear', [ClienteController::class, 'create'])->middleware('can:crear clientes')->name('clientes.create');
    Route::post('/clientes', [ClienteController::class, 'store'])->middleware('can:crear clientes')->name('clientes.store');
    
    Route::get('/clientes/{cliente}', [ClienteController::class, 'show'])->middleware('can:ver clientes')->name('clientes.show');
    
    Route::prefix('/clientes/{cliente}/vehiculos')->name('clientes.vehiculos.')->group(function () {
        Route::get('/crear', [VehiculoController::class, 'create'])->middleware('can:crear vehiculos')->name('create');
        Route::post('/', [VehiculoController::class, 'store'])->middleware('can:crear vehiculos')->name('store');
    });
});