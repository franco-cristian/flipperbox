<?php

use FlipperBox\Crm\Http\Controllers\ClienteController;
use Illuminate\Support\Facades\Route;

// Agrupamos todas las rutas de CRM para que requieran autenticación
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/clientes', [ClienteController::class, 'index'])
        ->name('clientes.index');

    // Protegemos las acciones de creación solo para quienes tengan el permiso
    Route::get('/clientes/crear', [ClienteController::class, 'create'])
        ->middleware('can:crear clientes')
        ->name('clientes.create');

    Route::post('/clientes', [ClienteController::class, 'store'])
        ->middleware('can:crear clientes')
        ->name('clientes.store');
});