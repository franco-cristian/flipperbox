<?php

use FlipperBox\WorkManagement\Http\Controllers\WorkOrderController;
use Illuminate\Support\Facades\Route;

Route::middleware('can:ver ordenes de trabajo')->prefix('ordenes-trabajo')->name('work-orders.')->group(function () {
    Route::get('/', [WorkOrderController::class, 'index'])->name('index');
});