<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SlotMaintenance extends Model
{
    protected $fillable = [
        'slot_id',
        'start_time',
        'end_time'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];
}
