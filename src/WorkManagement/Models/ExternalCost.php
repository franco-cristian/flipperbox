<?php

namespace FlipperBox\WorkManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExternalCost extends Model
{
    protected $fillable = ['work_order_id', 'description', 'cost', 'price'];

    /**
     * Define la relación inversa: Un costo externo pertenece a una orden de trabajo.
     */
    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }
}
