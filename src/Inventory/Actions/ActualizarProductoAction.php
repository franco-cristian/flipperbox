<?php
namespace FlipperBox\Inventory\Actions;

use FlipperBox\Inventory\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ActualizarProductoAction
{
    public function execute(Product $product, array $data): Product
    {
        return DB::transaction(function () use ($product, $data) {
            // 1. Actualizar los datos principales del producto.
            // El evento 'saving' en el modelo se encargará de recalcular el 'price'.
            $product->update($data);

            // 2. Actualizar el costo en la tabla pivot para el proveedor seleccionado.
            // updateExistingPivot asume que la relación ya existe.
            $product->suppliers()->updateExistingPivot($data['supplier_id'], [
                'cost' => $data['cost'],
            ]);
            
            Log::info("Producto actualizado: ID {$product->id} - {$product->name}");

            return $product;
        });
    }
}