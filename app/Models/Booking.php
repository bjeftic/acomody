<?php

namespace App\Models;

use App\Enums\Booking\BookingStatus;
use App\Enums\Booking\PaymentStatus;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    use HasUlids, SoftDeletes;

    protected $fillable = [
        'accommodation_id',
        'user_id',
        'host_user_id',
        'check_in',
        'check_out',
        'nights',
        'guests',
        'status',
        'booking_type',
        'currency',
        'subtotal',
        'fees_total',
        'taxes_total',
        'total_price',
        'price_breakdown',
        'optional_fee_ids',
        'payment_status',
        'availability_period_id',
        'confirmed_at',
        'cancelled_at',
        'cancelled_by_user_id',
        'cancellation_reason',
        'refund_amount',
        'declined_at',
        'decline_reason',
        'guest_notes',
    ];

    protected $casts = [
        'status'          => BookingStatus::class,
        'payment_status'  => PaymentStatus::class,
        'price_breakdown' => 'array',
        'optional_fee_ids' => 'array',
        'check_in'        => 'date',
        'check_out'       => 'date',
        'confirmed_at'    => 'datetime',
        'cancelled_at'    => 'datetime',
        'declined_at'     => 'datetime',
        'subtotal'        => 'float',
        'fees_total'      => 'float',
        'taxes_total'     => 'float',
        'total_price'     => 'float',
        'refund_amount'   => 'float',
    ];

    // ============================================
    // RELATIONSHIPS
    // ============================================

    public function accommodation(): BelongsTo
    {
        return $this->belongsTo(Accommodation::class);
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_user_id');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by_user_id');
    }

    public function availabilityPeriod(): BelongsTo
    {
        return $this->belongsTo(AvailabilityPeriod::class);
    }

    // ============================================
    // AUTHORIZATION
    // ============================================

    public function canBeReadBy($user): bool
    {
        return $user && ($user->id === $this->user_id || $user->id === $this->host_user_id);
    }

    public function canBeCreatedBy($user): bool
    {
        return $user !== null;
    }

    public function canBeUpdatedBy($user): bool
    {
        return $user && ($user->id === $this->user_id || $user->id === $this->host_user_id);
    }

    public function canBeDeletedBy($user): bool
    {
        return $user && $user->id === $this->host_user_id;
    }

    // ============================================
    // STATUS HELPERS
    // ============================================

    public function isPending(): bool
    {
        return $this->status === BookingStatus::PENDING;
    }

    public function isConfirmed(): bool
    {
        return $this->status === BookingStatus::CONFIRMED;
    }

    public function isCancelled(): bool
    {
        return $this->status === BookingStatus::CANCELLED;
    }

    public function isDeclined(): bool
    {
        return $this->status === BookingStatus::DECLINED;
    }

    public function isCompleted(): bool
    {
        return $this->status === BookingStatus::COMPLETED;
    }

    public function canBeCancelledBy(User $user): bool
    {
        return $this->status->isActive()
            && ($user->id === $this->user_id || $user->id === $this->host_user_id);
    }

    public function canBeConfirmedBy(User $user): bool
    {
        return $this->isPending()
            && $user->id === $this->host_user_id;
    }

    public function canBeDeclinedBy(User $user): bool
    {
        return $this->isPending()
            && $user->id === $this->host_user_id;
    }

    // ============================================
    // SCOPES
    // ============================================

    public function scopePending($query)
    {
        return $query->where('status', BookingStatus::PENDING->value);
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', BookingStatus::CONFIRMED->value);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            BookingStatus::PENDING->value,
            BookingStatus::CONFIRMED->value,
        ]);
    }

    public function scopeForHost($query, int $userId)
    {
        return $query->where('host_user_id', $userId);
    }

    public function scopeForGuest($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('check_in', '>=', now()->toDateString());
    }
}
