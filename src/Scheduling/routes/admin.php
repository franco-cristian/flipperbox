<?php

use FlipperBox\Scheduling\Http\Controllers\CapacityController;
use Illuminate\Support\Facades\Route;

Route::get('/capacidades', [CapacityController::class, 'index'])->name('capacities.index');
Route::post('/capacidades', [CapacityController::class, 'store'])->name('capacities.store');