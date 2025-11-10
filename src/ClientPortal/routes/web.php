<?php

use FlipperBox\ClientPortal\Http\Controllers\DashboardController;
use FlipperBox\ClientPortal\Http\Controllers\VehiculoController;
use Illuminate\Support\Facades\Route;

// Ruta del Dashboard del Cliente
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Grupo de Rutas para la Gestión de Vehículos del Cliente
Route::prefix('vehiculos')->name('vehiculos.')->group(function () {
    Route::get('/', [VehiculoController::class, 'index'])->name('index');
    Route::get('/crear', [VehiculoController::class, 'create'])->name('create');
    Route::post('/', [VehiculoController::class, 'store'])->name('store');
    Route::get('/{vehiculo}/editar', [VehiculoController::class, 'edit'])->name('edit');
    Route::patch('/{vehiculo}', [VehiculoController::class, 'update'])->name('update');
    Route::delete('/{vehiculo}', [VehiculoController::class, 'destroy'])->name('destroy');
});