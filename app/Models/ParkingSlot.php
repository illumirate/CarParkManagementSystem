<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ParkingSlot extends Model
{
    protected $fillable = [
        'slot_id',
        'zone_id',
        'level_id',
        'slot_number',
        'status',
    ];

    // ==================== RELATIONSHIPS ====================

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function parkingLevel(): BelongsTo
    {
        return $this->belongsTo(ParkingLevel::class, 'level_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    // ==================== SCOPES ====================

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function scopeByZone($query, $zoneId)
    {
        return $query->where('zone_id', $zoneId);
    }

    public function scopeByLevel($query, $levelId)
    {
        return $query->where('level_id', $levelId);
    }

    // ==================== HELPER METHODS ====================

    /**
     * Check if slot is available for a specific date and time range
     */
    public function isAvailableFor(string $date, string $startTime, string $endTime): bool
    {
        if ($this->status !== 'available') {
            return false;
        }

        // Check for overlapping bookings
        $hasConflict = $this->bookings()
            ->where('booking_date', $date)
            ->whereIn('status', ['pending', 'confirmed', 'active'])
            ->where(function ($query) use ($startTime, $endTime) {
                $query->where(function ($q) use ($startTime, $endTime) {
                    // New booking starts during existing booking
                    $q->where('start_time', '<=', $startTime)
                      ->where('end_time', '>', $startTime);
                })->orWhere(function ($q) use ($startTime, $endTime) {
                    // New booking ends during existing booking
                    $q->where('start_time', '<', $endTime)
                      ->where('end_time', '>=', $endTime);
                })->orWhere(function ($q) use ($startTime, $endTime) {
                    // New booking completely contains existing booking
                    $q->where('start_time', '>=', $startTime)
                      ->where('end_time', '<=', $endTime);
                });
            })
            ->exists();

        return !$hasConflict;
    }

    public function isAvailable(): bool
    {
        return $this->status === 'available';
    }

    public function isOccupied(): bool
    {
        return $this->status === 'occupied';
    }

    public function isUnderMaintenance(): bool
    {
        return $this->status === 'maintenance';
    }
}
