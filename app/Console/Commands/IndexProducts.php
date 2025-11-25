<?php

namespace App\Console\Commands;

use App\Services\EmbeddingService;
use FlipperBox\Inventory\Models\Product;
use Illuminate\Console\Command;

class IndexProducts extends Command
{
    protected $signature = 'products:index {--all : Forzar re-indexado de todos los productos}';

    protected $description = 'Generar embeddings para productos del inventario';

    public function handle(EmbeddingService $embeddingService): int
    {
        $this->info('Iniciando indexación de productos...');

        $query = Product::query();

        if (! $this->option('all')) {
            $query->whereNull('embedding');
            $this->info('Modo: Solo productos sin embedding.');
        } else {
            $this->info('Modo: Re-indexado completo.');
        }

        $total = $query->count();
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $query->chunk(50, function ($products) use ($embeddingService, $bar) {
            foreach ($products as $product) {
                $text = "Producto: {$product->name}. SKU: {$product->sku}. Descripción: {$product->description}.";
                $embedding = $embeddingService->generate($text);

                if ($embedding) {
                    $product->embedding = $embedding;
                    $product->saveQuietly();
                } else {
                    $this->error("\nError generando embedding para ID: {$product->id}");
                }

                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();
        $this->info('Indexación completada.');

        return self::SUCCESS;
    }
}
