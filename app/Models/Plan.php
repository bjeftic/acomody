<?php

namespace App\Models;

use App\Enums\Subscription\BillingPeriod;
use App\Enums\Subscription\PlanCode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'price_eur',
        'commission_rate',
        'billing_period',
        'is_active',
        'features',
        'sort_order',
    ];

    public function casts(): array
    {
        return [
            'code' => PlanCode::class,
            'billing_period' => BillingPeriod::class,
            'is_active' => 'boolean',
            'features' => 'array',
            'price_eur' => 'integer',
            'commission_rate' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function canBeReadBy($user): bool
    {
        return true;
    }

    public function canBeCreatedBy($user): bool
    {
        return $user !== null && $user->is_superadmin;
    }

    public function canBeUpdatedBy($user): bool
    {
        return $user !== null && $user->is_superadmin;
    }

    public function canBeDeletedBy($user): bool
    {
        return $user !== null && $user->is_superadmin;
    }

    public function hostSubscriptions(): HasMany
    {
        return $this->hasMany(HostSubscription::class);
    }

    public function isCommissionFree(): bool
    {
        return $this->commission_rate === 0;
    }
}
