<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('CREATE EXTENSION IF NOT EXISTS vector;');

        Schema::table('products', function (Blueprint $table) {
            // El modelo BAAI/bge-base-en-v1.5 genera vectores de 768 dimensiones
            $table->vector('embedding', 768)->nullable();
        });

        // índice HNSW para búsquedas rápidas
        DB::statement('CREATE INDEX products_embedding_index ON products USING hnsw (embedding vector_cosine_ops);');
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('embedding');
        });
    }
};
