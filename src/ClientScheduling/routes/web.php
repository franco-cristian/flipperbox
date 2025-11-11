<?php

use FlipperBox\ClientScheduling\Http\Controllers\BookingController;
use Illuminate\Support\Facades\Route;

Route::middleware('can:solicitar reserva')->group(function () {
    Route::get('/', [BookingController::class, 'index'])->name('index');
    Route::post('/', [BookingController::class, 'store'])->name('store');
    Route::delete('/reservations/{reservation}', [BookingController::class, 'destroy'])
    ->name('destroy');
});