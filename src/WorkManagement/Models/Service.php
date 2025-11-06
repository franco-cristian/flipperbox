<?php

namespace FlipperBox\WorkManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Service extends Model
{
    protected $fillable = ['name', 'description', 'price'];

    /**
     * Define la relación: Un servicio puede estar en muchas órdenes de trabajo.
     */
    public function workOrders(): BelongsToMany
    {
        return $this->belongsToMany(WorkOrder::class, 'work_order_service')
                    ->withPivot('price')
                    ->withTimestamps();
    }
}