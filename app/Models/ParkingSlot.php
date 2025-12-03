<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParkingSlot extends Model
{
    protected $fillable = ['slot_id', 
                            'zone_id', 
                            'level_id', 
                            'status'];
}

