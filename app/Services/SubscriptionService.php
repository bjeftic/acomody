<?php

namespace App\Services;

use App\Enums\Subscription\PlanCode;
use App\Enums\Subscription\SubscriptionStatus;
use App\Models\FeatureFlag;
use App\Models\HostSubscription;
use App\Models\Plan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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
            return Plan::query()->where('code', PlanCode::Free->value)->first()?->commission_rate ?? PlanCode::Free->defaultCommissionRate();
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

    /**
     * Check if a host is commission-free by user ID, using a raw DB query to avoid
     * Eloquent authorization checks (needed when called in a guest booking context).
     */
    public function isCommissionFreeByUserId(string $userId): bool
    {
        $activeStatuses = [SubscriptionStatus::Active->value, SubscriptionStatus::Trial->value];

        $subscription = DB::table('host_subscriptions')
            ->where('user_id', $userId)
            ->whereIn('status', $activeStatuses)
            ->orderByDesc('created_at')
            ->first(['is_early_host', 'early_host_expires_at']);

        if (! $subscription || ! $subscription->is_early_host) {
            return false;
        }

        if ($subscription->early_host_expires_at === null) {
            return true;
        }

        return Carbon::parse($subscription->early_host_expires_at)->isFuture();
    }

    /**
     * Get the effective commission rate (as integer percentage) for a host by user ID,
     * using a raw DB query to avoid Eloquent authorization checks (needed in guest booking context).
     * Returns 0 if the host is commission-free, otherwise returns the plan's commission_rate.
     */
    public function getCommissionRateByUserId(string $userId): int
    {
        if ($this->isCommissionFreeByUserId($userId)) {
            return 0;
        }

        $activeStatuses = [SubscriptionStatus::Active->value, SubscriptionStatus::Trial->value];

        $rate = DB::table('host_subscriptions')
            ->join('plans', 'host_subscriptions.plan_id', '=', 'plans.id')
            ->where('host_subscriptions.user_id', $userId)
            ->whereIn('host_subscriptions.status', $activeStatuses)
            ->orderByDesc('host_subscriptions.created_at')
            ->value('plans.commission_rate');

        return $rate ?? Plan::query()->where('code', PlanCode::Free->value)->value('commission_rate') ?? PlanCode::Free->defaultCommissionRate();
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
