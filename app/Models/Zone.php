<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Zone extends Model
{
    protected $fillable = [
        'zone_code',
        'zone_name',
        'type',
        'total_slots',
        'available_slots',
    ];

    public function parkingLevels()
    {
        return $this->hasMany(ParkingLevel::class, 'zone_id');
    }


    public function parkingSlots(): HasMany
    {
        return $this->hasMany(ParkingSlot::class);
    }
}
