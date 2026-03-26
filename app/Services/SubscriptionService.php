<?php

namespace App\Services;

use App\Enums\Subscription\PlanCode;
use App\Enums\Subscription\SubscriptionStatus;
use App\Models\FeatureFlag;
use App\Models\HostSubscription;
use App\Models\Plan;
use App\Models\User;

class SubscriptionService
{
    public function getActivePlan(User $user): Plan
    {
        $subscription = $user->hostSubscription;

        if ($subscription && $subscription->isActive()) {
            return $subscription->plan;
        }

        return Plan::query()->where('code', PlanCode::Free->value)->firstOrFail();
    }

    public function getCommissionRate(User $user): int
    {
        $subscription = $user->hostSubscription;

        if (! $subscription || ! $subscription->isActive()) {
            return Plan::query()->where('code', PlanCode::Free->value)->first()?->commission_rate ?? 10;
        }

        return $subscription->commissionRate();
    }

    public function isCommissionFree(User $user): bool
    {
        $subscription = $user->hostSubscription;

        if (! $subscription || ! $subscription->isActive()) {
            return false;
        }

        return $subscription->isCommissionFree();
    }

    public function isEarlyHost(User $user): bool
    {
        $subscription = $user->hostSubscription;

        if (! $subscription) {
            return false;
        }

        return $subscription->isEarlyHostActive();
    }

    public function markAsEarlyHost(User $user): HostSubscription
    {
        $subscription = $user->hostSubscription;

        if (! $subscription) {
            $subscription = $this->assignFreePlan($user);
        }

        // early_host_expires_at is intentionally null here — it will be set to now()+6 months
        // automatically when the cold_start feature flag is turned off (see FeatureFlag::booted())
        $subscription->update([
            'is_early_host' => true,
            'early_host_expires_at' => null,
        ]);

        return $subscription->fresh();
    }

    public function canAddAccommodation(User $user): bool
    {
        $plan = $this->getActivePlan($user);
        $current = $user->accommodations()->count();

        return $plan->allowsMoreAccommodations($current);
    }

    public function isColdStartActive(): bool
    {
        return FeatureFlag::where('key', 'cold_start')->where('is_enabled', true)->exists();
    }

    public function assignFreePlan(User $user): HostSubscription
    {
        $freePlan = Plan::query()->where('code', PlanCode::Free->value)->firstOrFail();

        return HostSubscription::updateOrCreate(
            ['user_id' => $user->id],
            [
                'plan_id' => $freePlan->id,
                'status' => SubscriptionStatus::Active->value,
                'starts_at' => now(),
            ]
        );
    }
}
