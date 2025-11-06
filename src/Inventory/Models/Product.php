<?php

namespace FlipperBox\Inventory\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use FlipperBox\WorkManagement\Models\WorkOrder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'sku',
        'description',
        'cost',
        'iva_percentage',
        'profit_margin',
        'price',
        'current_stock',
        'min_threshold',
    ];

    protected $casts = [
        'cost' => 'decimal:2',
        'iva_percentage' => 'decimal:2',
        'profit_margin' => 'decimal:2',
        'price' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        // Calcular automáticamente el precio antes de guardar
        static::saving(function ($product) {
            if (empty($product->price) || $product->isDirty(['cost', 'iva_percentage', 'profit_margin'])) {
                $product->price = $product->calculatePrice();
            }
        });
    }

    /**
     * Calcular el precio basado en costo, IVA y margen
     * Fórmula: (Costo + IVA) × (1 + Margen%)
     */
    public function calculatePrice(): float
    {
        $costWithIva = $this->cost * (1 + ($this->iva_percentage / 100));
        $price = $costWithIva * (1 + ($this->profit_margin / 100));
        return round($price, 2);
    }

    // Relaciones
    public function suppliers()
    {
        return $this->belongsToMany(Supplier::class)
            ->withPivot('cost')
            ->withTimestamps();
    }

    public function inventoryMovements()
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function workOrders(): BelongsToMany
    {
        return $this->belongsToMany(WorkOrder::class, 'work_order_product')
            ->withPivot('quantity', 'unit_price')
            ->withTimestamps();
    }
}