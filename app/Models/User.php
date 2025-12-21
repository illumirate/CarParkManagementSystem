<?php
//  Author: Leo Chia Chuen

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'role',
        'user_type',
        'student_id',
        'staff_id',
        'faculty',
        'department',
        'course',
        'status',
        'credit_balance',
        'last_login_at',
        'google_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
            'credit_balance' => 'decimal:2',
        ];
    }

    // ==================== RELATIONSHIPS ====================

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function creditTransactions()
    {
        return $this->hasMany(CreditTransaction::class);
    }

    public function supportTeamMemberProfile()
    {
        return $this->hasOne(SupportTeamMember::class);
    }

    public function supportTickets()
    {
        return $this->hasMany(SupportTicket::class);
    }

    public function assignedSupportTickets()
    {
        return $this->hasMany(SupportTicket::class, 'assigned_to_user_id');
    }

    public function supportTicketMessages()
    {
        return $this->hasMany(SupportTicketMessage::class, 'sender_user_id');
    }

    // ==================== HELPER METHODS ====================

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    /**
     * Check if user is a TARUMT student (user_type).
     */
    public function isStudent(): bool
    {
        return $this->user_type === 'student';
    }

    /**
     * Check if user is a TARUMT staff member (user_type).
     * Note: This checks user_type, not role. For role-based checks, use isAdmin().
     */
    public function isTarumtStaff(): bool
    {
        return $this->user_type === 'staff';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function hasCredits(float $amount): bool
    {
        return $this->credit_balance >= $amount;
    }

    public function getPrimaryVehicle()
    {
        return $this->vehicles()->where('is_primary', true)->first()
            ?? $this->vehicles()->first();
    }
}
