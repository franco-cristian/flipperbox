<?php

namespace FlipperBox\Scheduling\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DailyCapacity extends Model
{
    protected $fillable = ['date', 'total_slots', 'booked_slots'];

    protected $casts = ['date' => 'date'];

    /**
     * Relación con Reservas basada en la fecha.
     */
    public function reservations(): HasMany
    {
        // hasMany(Modelo, ClaveForaneaEnReservas, ClaveLocalEnCapacities)
        return $this->hasMany(Reservation::class, 'reservation_date', 'date');
    }
}
