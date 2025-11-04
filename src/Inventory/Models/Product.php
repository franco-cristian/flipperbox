<?php
namespace FlipperBox\Inventory\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['name', 'sku', 'description', 'price', 'current_stock', 'min_threshold'];

    public function suppliers(): BelongsToMany
    {
        return $this->belongsToMany(Supplier::class)
                    ->withPivot('cost')
                    ->withTimestamps();
    }
    
    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    protected static function newFactory()
    {
        return ProductFactory::new();
    }
}