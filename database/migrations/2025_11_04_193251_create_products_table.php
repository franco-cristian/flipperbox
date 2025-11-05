<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sku')->unique()->comment('Stock Keeping Unit');
            $table->text('description')->nullable();
            $table->decimal('cost', 10, 2)->comment('Precio de compra');
            $table->decimal('iva_percentage', 5, 2)->default(21.00)->comment('Porcentaje de IVA');
            $table->decimal('profit_margin', 5, 2)->default(40.00)->comment('Margen de ganancia');
            $table->decimal('price', 10, 2)->comment('Precio de venta (calculado)');
            $table->integer('current_stock')->default(0);
            $table->integer('min_threshold')->default(5)->comment('Umbral de stock bajo');
            $table->timestamps();
            $table->softDeletes();
            $table->index('name');
            $table->index('sku');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};