<?php

namespace FlipperBox\Scheduling\Models;

use App\Models\User;
use FlipperBox\Crm\Models\Vehiculo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    protected $fillable = ['user_id', 'vehicle_id', 'reservation_date', 'status', 'notes'];

    protected $casts = ['reservation_date' => 'date'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehiculo::class);
    }
}
