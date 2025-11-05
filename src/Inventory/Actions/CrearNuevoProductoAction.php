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
            // 1. Crear el producto
            $product = Product::create([
                'name' => $data['name'],
                'sku' => $data['sku'],
                'description' => $data['description'] ?? null,
                'price' => $data['price'],
                'current_stock' => $data['current_stock'],
                'min_threshold' => $data['min_threshold'],
            ]);

            // 2. Si hay stock inicial, crear el primer movimiento de inventario
            if ($data['current_stock'] > 0) {
                InventoryMovement::create([
                    'product_id' => $product->id,
                    'user_id' => Auth::id(),
                    'type' => 'in',
                    'quantity' => $data['current_stock'],
                    'reason' => 'Stock Inicial',
                ]);
            }
            
            Log::info("Nuevo producto creado: ID {$product->id} - {$product->name}");

            return $product;
        });
    }
}