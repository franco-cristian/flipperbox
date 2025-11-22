<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Verificar si la conexión es PostgreSQL
        if (DB::connection()->getDriverName() === 'pgsql') {
            // 1. Habilitar la extensión pgvector
            DB::statement('CREATE EXTENSION IF NOT EXISTS vector;');

            // 2. Añadir la columna de embedding
            Schema::table('products', function (Blueprint $table) {
                $table->vector('embedding', 768)->nullable();
            });

            // 3. Crear índice HNSW para búsquedas rápidas (solo en PostgreSQL)
            DB::statement('CREATE INDEX products_embedding_index ON products USING hnsw (embedding vector_cosine_ops);');
        } else {
            // Para otras bases de datos (MySQL, SQLite), agregar una columna normal
            Schema::table('products', function (Blueprint $table) {
                $table->text('embedding')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            // Para PostgreSQL: eliminar índice y columna
            DB::statement('DROP INDEX IF EXISTS products_embedding_index;');
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('embedding');
            });
        } else {
            // Para otras bases de datos: solo eliminar la columna
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('embedding');
            });
        }
    }
};
