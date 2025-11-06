<?php

namespace FlipperBox\WorkManagement\Models;

use Illuminate\Database\Eloquent\Model;
    
class ExternalCost extends Model
{
    protected $fillable = ['work_order_id', 'description', 'cost', 'price'];
}