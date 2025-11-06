<?php

namespace FlipperBox\Crm\Models;

use Database\Factories\VehiculoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use FlipperBox\WorkManagement\Models\WorkOrder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehiculo extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'vehiculos';

    protected $fillable = [
        'cliente_id',
        'patente',
        'marca',
        'modelo',
        'anio',
        'kilometraje',
        'vin', // Número de chasis
        'numero_motor',
        'observaciones',
    ];

    /**
     * Define la relación: Un vehículo pertenece a un cliente.
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    /**
     * Crea una nueva instancia de la factory para el modelo.
     */
    protected static function newFactory()
    {
        return VehiculoFactory::new();
    }

    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class);
    }
}
