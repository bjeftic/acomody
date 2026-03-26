<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function __construct(
        protected SubscriptionService $subscriptionService,
    ) {}

    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        $plan = $this->subscriptionService->getActivePlan($user);
        $subscription = $user->hostSubscription;

        return response()->json([
            'success' => true,
            'message' => 'Subscription data retrieved.',
            'data' => [
                'plan_code' => $plan->code->value,
                'plan_name' => $plan->name,
                'price_eur' => $plan->price_eur,
                'max_accommodations' => $plan->max_accommodations,
                'features' => $plan->features,
                'is_active' => $subscription?->isActive() ?? false,
                'ends_at' => $subscription?->ends_at?->toISOString(),
                'commission_rate' => $this->subscriptionService->getCommissionRate($user),
                'is_commission_free' => $this->subscriptionService->isCommissionFree($user),
                'is_early_host' => $this->subscriptionService->isEarlyHost($user),
                'early_host_expires_at' => $subscription?->early_host_expires_at?->toISOString(),
                'can_add_accommodation' => $this->subscriptionService->canAddAccommodation($user),
            ],
        ]);
    }

    public function plans(): JsonResponse
    {
        $plans = Plan::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn (Plan $plan) => [
                'code' => $plan->code->value,
                'name' => $plan->name,
                'price_eur' => $plan->price_eur,
                'billing_period' => $plan->billing_period->value,
                'max_accommodations' => $plan->max_accommodations,
                'commission_rate' => $plan->commission_rate,
                'features' => $plan->features,
                'is_commission_free' => $plan->isCommissionFree(),
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Plans retrieved.',
            'data' => $plans,
        ]);
    }
}
