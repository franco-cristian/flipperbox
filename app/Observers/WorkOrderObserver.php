<?php

namespace App\Observers;

use FlipperBox\Inventory\Models\Product;
use FlipperBox\WorkManagement\Models\WorkOrder;
use Illuminate\Support\Facades\DB;

class WorkOrderObserver
{
    /**
     * Handle the WorkOrder "deleting" event.
     * Red de seguridad final para devolver el stock.
     */
    public function deleting(WorkOrder $workOrder): void
    {
        // Solo devolvemos stock si la orden no estaba 'Completada'.
        // No se devuelve stock de trabajos finalizados y pagados.
        if ($workOrder->status !== 'Completada' && $workOrder->products()->exists()) {
            DB::transaction(function () use ($workOrder) {
                foreach ($workOrder->products as $product) {
                    // Usamos lockForUpdate para prevenir condiciones de carrera
                    $productInStock = Product::lockForUpdate()->find($product->id);
                    if ($productInStock) {
                        $productInStock->increment('current_stock', $product->pivot->quantity);
                    }
                }
            });
        }
    }
}
