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
        Schema::create('work_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehiculos')->onDelete('cascade');
            $table->foreignId('mechanic_id')->nullable()->constrained('users')->onDelete('set null')->comment('Mecánico asignado');
            $table->enum('status', ['Pendiente', 'En Progreso', 'Completada', 'Cancelada'])->default('Pendiente');
            $table->text('description')->comment('Descripción inicial del problema o servicio solicitado');
            $table->text('notes')->nullable()->comment('Notas internas del mecánico');
            $table->timestamp('entry_date')->useCurrent();
            $table->timestamp('completion_date')->nullable();
            $table->decimal('total', 10, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_orders');
    }
};
