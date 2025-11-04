<?php

use FlipperBox\Crm\Http\controllers\ClienteController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/clientes', [ClienteController::class, 'index'])
        ->middleware('can:ver clientes')
        ->name('clientes.index');

    Route::get('/clientes/crear', [ClienteController::class, 'create'])
        ->middleware('can:crear clientes')
        ->name('clientes.create');

    Route::post('/clientes', [ClienteController::class, 'store'])
        ->middleware('can:crear clientes')
        ->name('clientes.store');
});