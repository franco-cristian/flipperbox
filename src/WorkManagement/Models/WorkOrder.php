<?php

namespace FlipperBox\WorkManagement\Models;

use App\Models\User;
use FlipperBox\Crm\Models\Vehiculo;
use FlipperBox\Inventory\Models\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['vehicle_id', 'mechanic_id', 'status', 'description', 'notes', 'entry_date', 'completion_date', 'total'];

    protected $casts = ['entry_date' => 'datetime', 'completion_date' => 'datetime'];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehiculo::class, 'vehicle_id')->withTrashed();
    }

    public function mechanic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mechanic_id');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'work_order_product')
            ->withPivot('quantity', 'unit_price')
            ->withTimestamps()
            ->withTrashed();
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'work_order_service')
            ->withPivot('price')
            ->withTimestamps();
    }

    public function externalCosts(): HasMany
    {
        return $this->hasMany(ExternalCost::class);
    }
}
