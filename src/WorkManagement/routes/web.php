<?php

use FlipperBox\WorkManagement\Http\Controllers\WorkOrderController;
use Illuminate\Support\Facades\Route;

Route::middleware('can:ver ordenes de trabajo')->prefix('ordenes-trabajo')->name('work-orders.')->group(function () {
    Route::get('/', [WorkOrderController::class, 'index'])->name('index');

    Route::middleware('can:gestionar ordenes de trabajo')->group(function () {
        Route::get('/crear/vehiculo/{vehiculo}', [WorkOrderController::class, 'create'])->name('create');
        Route::post('/', [WorkOrderController::class, 'store'])->name('store');
        Route::get('/{workOrder}', [WorkOrderController::class, 'show'])->name('show');
        Route::patch('/{workOrder}', [WorkOrderController::class, 'update'])->name('update');
        Route::delete('/{workOrder}', [WorkOrderController::class, 'destroy'])->name('destroy');

        // Rutas para gestionar los ítems de una orden de trabajo
        Route::post('/{workOrder}/products', [WorkOrderController::class, 'addProduct'])->name('products.add');
        Route::delete('/{workOrder}/products/{product}', [WorkOrderController::class, 'removeProduct'])->name('products.remove');

        Route::post('/{workOrder}/services', [WorkOrderController::class, 'addService'])->name('services.add');
        Route::delete('/{workOrder}/services/{service}', [WorkOrderController::class, 'removeService'])->name('services.remove');

        Route::post('/{workOrder}/external-costs', [WorkOrderController::class, 'addExternalCost'])->name('external-costs.add');
        Route::delete('/external-costs/{externalCost}', [WorkOrderController::class, 'removeExternalCost'])->name('external-costs.remove');

        Route::patch('/{workOrder}/assign-mechanic', [WorkOrderController::class, 'assignMechanic'])->name('assign-mechanic');
    });
});
