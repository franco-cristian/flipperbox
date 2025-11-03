<?php

use FlipperBox\Crm\Http\Controllers\ClienteController;
use Illuminate\Support\Facades\Route;

// Rutas para la gestión de Clientes
Route::get('/clientes', [ClienteController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('clientes.index');

Route::get('/clientes/crear', [ClienteController::class, 'create'])
    ->middleware(['auth', 'verified'])
    ->name('clientes.create');

Route::post('/clientes', [ClienteController::class, 'store'])
    ->middleware(['auth', 'verified'])
    ->name('clientes.store');