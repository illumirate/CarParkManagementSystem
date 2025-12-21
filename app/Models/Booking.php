<?php
//  Author: Ng Ian Kai

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    protected $fillable = [
        'booking_number',
        'user_id',
        'vehicle_id',
        'parking_slot_id',
        'booking_date',
        'start_time',
        'end_time',
        'total_fee',
        'status',
        'cancellation_reason',
        'confirmed_at',
        'checked_in_at',
        'checked_out_at',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'booking_date' => 'date',
            'start_time' => 'datetime:H:i',
            'end_time' => 'datetime:H:i',
            'total_fee' => 'decimal:2',
            'confirmed_at' => 'datetime',
            'checked_in_at' => 'datetime',
            'checked_out_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    // ==================== BOOT ====================

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($booking) {
            if (empty($booking->booking_number)) {
                $booking->booking_number = self::generateBookingNumber();
            }
        });
    }

    /**
     * Generate unique booking number: BK-YYYYMMDD-XXXX
     */
    public static function generateBookingNumber(): string
    {
        $date = now()->format('Ymd');
        $prefix = "BK-{$date}-";

        $lastBooking = self::where('booking_number', 'like', "{$prefix}%")
            ->orderBy('booking_number', 'desc')
            ->first();

        if ($lastBooking) {
            $lastNumber = (int) substr($lastBooking->booking_number, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return $prefix . $newNumber;
    }

    // ==================== RELATIONSHIPS ====================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function parkingSlot(): BelongsTo
    {
        return $this->belongsTo(ParkingSlot::class);
    }

    // ==================== SCOPES ====================

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['confirmed', 'active']);
    }

    public function scopeUpcoming($query)
    {
        $today = now()->toDateString();
        $currentTime = now()->format('H:i');

        return $query->where('status', 'confirmed')
            ->where(function ($q) use ($today, $currentTime) {
                $q->where('booking_date', '>', $today)
                    ->orWhere(function ($q2) use ($today, $currentTime) {
                        $q2->where('booking_date', '=', $today)
                            ->where('start_time', '>=', $currentTime);
                    });
            });
    }

    public function scopePast($query)
    {
        return $query->whereIn('status', ['completed', 'cancelled', 'expired']);
    }

    public function scopeForDate($query, $date)
    {
        return $query->where('booking_date', $date);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // ==================== HELPER METHODS ====================

    /**
     * Calculate duration in hours
     */
    public function calculateDuration(): float
    {
        $start = Carbon::parse($this->start_time);
        $end = Carbon::parse($this->end_time);

        return $start->diffInMinutes($end) / 60;
    }

    /**
     * Get formatted duration as "X hour(s) Y minute(s)"
     */
    public function getFormattedDuration(): string
    {
        $start = Carbon::parse($this->start_time);
        $end = Carbon::parse($this->end_time);
        $totalMinutes = $start->diffInMinutes($end);

        $hours = intdiv($totalMinutes, 60);
        $minutes = $totalMinutes % 60;

        $parts = [];
        if ($hours > 0) {
            $parts[] = $hours . ' hour' . ($hours !== 1 ? 's' : '');
        }
        if ($minutes > 0) {
            $parts[] = $minutes . ' minute' . ($minutes !== 1 ? 's' : '');
        }

        return implode(' ', $parts) ?: '0 minutes';
    }

    /**
     * Check if booking can be modified (before start time and not cancelled/completed)
     */
    public function canBeModified(): bool
    {
        if (!in_array($this->status, ['pending', 'confirmed'])) {
            return false;
        }

        $bookingStart = Carbon::parse($this->booking_date->format('Y-m-d') . ' ' . $this->start_time->format('H:i:s'));

        return now()->lt($bookingStart);
    }

    /**
     * Calculate fee: RM 2/hour, capped at RM 5
     */
    public static function calculateFee(string $startTime, string $endTime): float
    {
        $start = Carbon::parse($startTime);
        $end = Carbon::parse($endTime);

        $hours = $start->diffInMinutes($end) / 60;
        $fee = $hours * 2.00;

        return min($fee, 5.00);
    }

    /**
     * Check if booking can be cancelled (2+ hours before start)
     */
    public function canBeCancelled(): bool
    {
        if (!in_array($this->status, ['pending', 'confirmed'])) {
            return false;
        }

        $bookingStart = Carbon::parse($this->booking_date->format('Y-m-d') . ' ' . $this->start_time->format('H:i:s'));

        return now()->diffInHours($bookingStart, false) >= 2;
    }

    /**
     * Get refund amount based on cancellation timing
     * - 2+ hours before: 100% refund
     * - Within 2 hours: 50% refund
     * - After start time: 0% refund
     */
    public function getRefundAmount(): float
    {
        $bookingStart = Carbon::parse($this->booking_date->format('Y-m-d') . ' ' . $this->start_time->format('H:i:s'));
        $hoursUntilStart = now()->diffInHours($bookingStart, false);

        if ($hoursUntilStart >= 2) {
            return $this->total_fee;
        } elseif ($hoursUntilStart > 0) {
            return $this->total_fee * 0.5;
        }

        return 0;
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }
}
