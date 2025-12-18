<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParkingSlot extends Model
{
    protected $fillable = [
        'slot_id',
        'zone_id',
        'level_id',
        'status',
    ];

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function parkingLevel(): BelongsTo
    {
        return $this->belongsTo(ParkingLevel::class, 'level_id');
    }

    public function maintenance()
    {
        return $this->hasOne(SlotMaintenance::class, 'slot_id')
            ->where('end_time', '>=', now());
    }
    public function bookings()
    {
        return $this->hasMany(Booking::class, 'parking_slot_id');
    }
}

