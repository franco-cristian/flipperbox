<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehiculos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('patente')->unique();
            $table->string('marca');
            $table->string('modelo');
            $table->year('anio');
            $table->unsignedInteger('kilometraje')->nullable();
            $table->string('vin')->nullable()->unique()->comment('Número de chasis');
            $table->string('numero_motor')->nullable();
            $table->text('observaciones')->nullable()->comment('Notas del mecánico sobre el vehículo');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehiculos');
    }
};
