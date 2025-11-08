<?php

use FlipperBox\ClientScheduling\Http\Controllers\BookingController;
use Illuminate\Support\Facades\Route;

Route::get('/', [BookingController::class, 'index'])->name('index');
Route::post('/', [BookingController::class, 'store'])->name('store');