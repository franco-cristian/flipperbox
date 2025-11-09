<?php

namespace App\Observers;

use FlipperBox\Inventory\Models\Product;
use Illuminate\Validation\ValidationException;

class ProductObserver
{
    /**
     * Handle the Product "deleting" event.
     */
    public function deleting(Product $product): void
    {
        // Regla de negocio: No se puede eliminar (ni siquiera soft delete) un producto
        // si ya ha sido utilizado en CUALQUIER orden de trabajo.
        if ($product->workOrders()->exists()) {
            throw ValidationException::withMessages([
                'error' => 'No se puede eliminar este producto porque tiene un historial en órdenes de trabajo. Puede desactivarlo o archivarlo en una futura actualización.',
            ]);
        }
    }
}