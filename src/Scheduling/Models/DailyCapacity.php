<?php

namespace FlipperBox\Scheduling\Models;

use Illuminate\Database\Eloquent\Model;

class DailyCapacity extends Model
{
    protected $fillable = ['date', 'total_slots', 'booked_slots'];

    protected $casts = ['date' => 'date'];
}
