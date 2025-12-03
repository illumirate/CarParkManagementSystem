<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParkingLevel extends Model
{
    protected $fillable = [
        'zone_id',
        'level_name',
        'total_slots',
        'available_slots',
    ];
}
