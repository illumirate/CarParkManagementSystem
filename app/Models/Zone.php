<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Zone extends Model
{
    protected $fillable = [
        'zone_code',
        'zone_name',
        'total_slots',
        'available_slots',
    ];
}
