<?php

use FlipperBox\Crm\Http\Controllers\ClienteController;
use Illuminate\Support\Facades\Route;

Route::get('/clientes', [ClienteController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('clientes.index');