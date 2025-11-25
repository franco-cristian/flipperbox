<?php

use FlipperBox\Scheduling\Http\Controllers\CapacityController;
use FlipperBox\Scheduling\Http\Controllers\ReservationController;
use Illuminate\Support\Facades\Route;

Route::get('/capacidades', [CapacityController::class, 'index'])->name('capacities.index');
Route::post('/capacidades', [CapacityController::class, 'store'])->name('capacities.store');

Route::middleware('can:gestionar reservas')->prefix('reservas')->name('reservations.')->group(function () {
    Route::get('/', [ReservationController::class, 'index'])->name('index');
    Route::patch('/{reservation}', [ReservationController::class, 'update'])->name('update');
});
