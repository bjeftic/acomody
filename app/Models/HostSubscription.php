<?php

namespace App\Models;

use App\Enums\Subscription\PlanCode;
use App\Enums\Subscription\SubscriptionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property SubscriptionStatus $status
 * @property \Illuminate\Support\Carbon|null $starts_at
 * @property \Illuminate\Support\Carbon|null $ends_at
 * @property \Illuminate\Support\Carbon|null $early_host_expires_at
 * @property bool $is_early_host
 */
class HostSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'plan_id',
        'status',
        'starts_at',
        'ends_at',
        'is_early_host',
        'early_host_expires_at',
        'stripe_subscription_id',
        'stripe_customer_id',
    ];

    public function casts(): array
    {
        return [
            'status' => SubscriptionStatus::class,
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'early_host_expires_at' => 'datetime',
            'is_early_host' => 'boolean',
        ];
    }

    public function canBeReadBy($user): bool
    {
        // null = system/service context (e.g. registration flow); API endpoints are protected by auth:sanctum
        return $user === null || $user->id === $this->user_id;
    }

    public function canBeCreatedBy($user): bool
    {
        return true; // system-managed: created during registration and by admin via service
    }

    public function canBeUpdatedBy($user): bool
    {
        return true; // system-managed: updated by admin via service (e.g. markAsEarlyHost)
    }

    public function canBeDeletedBy($user): bool
    {
        return $user !== null && $user->is_superadmin;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function isActive(): bool
    {
        return $this->status->isActive();
    }

    public function isEarlyHostActive(): bool
    {
        if (! $this->is_early_host) {
            return false;
        }

        // null means cold_start hasn't launched yet — early host benefit is still pending activation
        if ($this->early_host_expires_at === null) {
            return true;
        }

        return $this->early_host_expires_at->isFuture();
    }

    public function isCommissionFree(): bool
    {
        return $this->isActive() && $this->isEarlyHostActive();
    }

    public function commissionRate(): int
    {
        if ($this->isActive() && $this->isEarlyHostActive()) {
            return 0;
        }

        return $this->plan->commission_rate ?? PlanCode::Free->defaultCommissionRate();
    }
}
