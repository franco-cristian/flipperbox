<?php

namespace FlipperBox\Inventory\Actions;

use FlipperBox\Inventory\Models\Product;
use FlipperBox\Inventory\Models\InventoryMovement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CrearNuevoProductoAction
{
    public function execute(array $data): Product
    {
        return DB::transaction(function () use ($data) {
            // 1. Instanciamos el modelo y asignamos propiedades
            $product = new Product();
            $product->fill($data);
            $product->save();

            // 2. Vinculamos el producto con el proveedor en la tabla pivot
            $product->suppliers()->attach($data['supplier_id'], ['cost' => $data['cost']]);

            // 3. Si hay stock inicial, crear el primer movimiento de inventario
            if ($product->current_stock > 0) {
                InventoryMovement::create([
                    'product_id' => $product->id,
                    'user_id' => Auth::id(),
                    'type' => 'in',
                    'quantity' => $product->current_stock,
                    'reason' => 'Stock Inicial',
                ]);
            }
            
            Log::info("Nuevo producto creado: ID {$product->id} - {$product->name}");

            return $product;
        });
    }
}