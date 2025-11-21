<?php

namespace App\Observers;

use App\Services\EmbeddingService;
use FlipperBox\Inventory\Models\Product;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Validation\ValidationException;

class ProductObserver implements ShouldHandleEventsAfterCommit
{
    public function __construct(
        protected EmbeddingService $embeddingService
    ) {}

    /**
     * Handle the Product "saved" event.
     * Se ejecuta después de created y updated, solo si la transacción fue exitosa.
     */
    public function saved(Product $product): void
    {
        // Solo generamos embedding si cambió información relevante o es nuevo
        if ($product->wasRecentlyCreated || $product->isDirty(['name', 'description', 'sku'])) {
            $this->updateEmbedding($product);
        }
    }

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

    private function updateEmbedding(Product $product): void
    {
        // Construimos un texto rico semánticamente para el embedding
        $textToEmbed = "Producto: {$product->name}. SKU: {$product->sku}. Descripción: {$product->description}. Precio: {$product->price}.";

        $embedding = $this->embeddingService->generate($textToEmbed);

        if ($embedding) {
            // Usamos saveQuietly para evitar un bucle infinito de eventos 'saved'
            $product->embedding = $embedding;
            $product->saveQuietly();
        }
    }
}
