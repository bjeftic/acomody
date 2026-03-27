<?php

use App\Enums\Subscription\PlanCode;
use App\Enums\Subscription\SubscriptionStatus;
use App\Models\FeatureFlag;
use App\Models\HostSubscription;
use App\Models\Plan;
use App\Services\SubscriptionService;

// ============================================
// GET /api/plans (public)
// ============================================

describe('GET /api/plans', function () {
    beforeEach(fn () => seedPlans());

    it('returns all active plans', function () {
        $response = $this->getJson(route('api.plans'));

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment(['code' => 'free'])
            ->assertJsonFragment(['code' => 'club']);
    });

    it('returns plan fields including commission rate', function () {
        $response = $this->getJson(route('api.plans'));

        $free = collect($response->json('data'))->firstWhere('code', 'free');
        expect($free['commission_rate'])->toBe(10);
        expect($free['is_commission_free'])->toBeFalse();
        expect($free['price_eur'])->toBe(0);

        $club = collect($response->json('data'))->firstWhere('code', 'club');
        expect($club['commission_rate'])->toBe(5);
        expect($club['is_commission_free'])->toBeFalse();
        expect($club['price_eur'])->toBe(3000);
    });

    it('does not require authentication', function () {
        $this->getJson(route('api.plans'))->assertOk();
    });
});

// ============================================
// GET /api/host/subscription (protected)
// ============================================

describe('GET /api/host/subscription', function () {
    beforeEach(fn () => seedPlans());

    it('returns 401 for unauthenticated requests', function () {
        $this->getJson(route('api.host.subscription'))->assertUnauthorized();
    });

    it('returns free plan data for user without subscription', function () {
        $user = authenticatedUser();

        $response = $this->actingAs($user)->getJson(route('api.host.subscription'));

        $response->assertOk()
            ->assertJsonFragment(['plan_code' => 'free'])
            ->assertJsonFragment(['is_commission_free' => false])
            ->assertJsonFragment(['commission_rate' => 10]);
    });

    it('returns correct data when user has active free subscription', function () {
        $user = authenticatedUser();
        $freePlan = Plan::withoutAuthorization(fn () => Plan::where('code', 'free')->first());

        HostSubscription::withoutAuthorization(fn () => HostSubscription::create([
            'user_id' => $user->id,
            'plan_id' => $freePlan->id,
            'status' => SubscriptionStatus::Active->value,
            'starts_at' => now(),
        ]));

        $response = $this->actingAs($user)->getJson(route('api.host.subscription'));

        $response->assertOk()
            ->assertJsonFragment(['plan_code' => PlanCode::Free->value])
            ->assertJsonFragment(['is_commission_free' => false])
            ->assertJsonFragment(['commission_rate' => 10])
            ->assertJsonFragment(['is_active' => true]);
    });

    it('returns 5 percent commission rate for club plan', function () {
        $user = authenticatedUser();
        $plan = Plan::withoutAuthorization(fn () => Plan::where('code', 'club')->first());

        HostSubscription::withoutAuthorization(fn () => HostSubscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => SubscriptionStatus::Active->value,
            'starts_at' => now(),
        ]));

        $response = $this->actingAs($user)->getJson(route('api.host.subscription'));

        $response->assertOk()
            ->assertJsonFragment(['commission_rate' => 5])
            ->assertJsonFragment(['is_commission_free' => false]);
    });

    it('returns early host status and 0 percent commission when active', function () {
        $user = authenticatedUser();
        $freePlan = Plan::withoutAuthorization(fn () => Plan::where('code', 'free')->first());

        HostSubscription::withoutAuthorization(fn () => HostSubscription::create([
            'user_id' => $user->id,
            'plan_id' => $freePlan->id,
            'status' => SubscriptionStatus::Active->value,
            'starts_at' => now(),
            'is_early_host' => true,
            'early_host_expires_at' => now()->addMonths(6),
        ]));

        $response = $this->actingAs($user)->getJson(route('api.host.subscription'));

        $response->assertOk()
            ->assertJsonFragment(['is_early_host' => true])
            ->assertJsonFragment(['is_commission_free' => true])
            ->assertJsonFragment(['commission_rate' => 0]);
    });
});

// ============================================
// SubscriptionService
// ============================================

describe('SubscriptionService', function () {
    beforeEach(fn () => seedPlans());

    it('assignFreePlan creates a host subscription on registration', function () {
        $user = authenticatedUser();
        $service = app(SubscriptionService::class);

        $subscription = $service->assignFreePlan($user);

        expect($subscription->user_id)->toBe($user->id);
        expect($subscription->plan->code)->toBe(PlanCode::Free);
        expect($subscription->isActive())->toBeTrue();
    });

    it('isCommissionFree returns false for free plan', function () {
        $user = authenticatedUser();
        $service = app(SubscriptionService::class);
        $service->assignFreePlan($user);

        expect($service->isCommissionFree($user))->toBeFalse();
    });

    it('isCommissionFree returns false for club plan', function () {
        $user = authenticatedUser();
        $plan = Plan::withoutAuthorization(fn () => Plan::where('code', 'club')->first());

        HostSubscription::withoutAuthorization(fn () => HostSubscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => SubscriptionStatus::Active->value,
            'starts_at' => now(),
        ]));

        expect(app(SubscriptionService::class)->isCommissionFree($user))->toBeFalse();
    });

    it('getCommissionRate returns 10 for free plan', function () {
        $user = authenticatedUser();
        $service = app(SubscriptionService::class);
        $service->assignFreePlan($user);

        expect($service->getCommissionRate($user))->toBe(10);
    });

    it('getCommissionRate returns 5 for club plan', function () {
        $user = authenticatedUser();
        $plan = Plan::withoutAuthorization(fn () => Plan::where('code', 'club')->first());

        HostSubscription::withoutAuthorization(fn () => HostSubscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => SubscriptionStatus::Active->value,
            'starts_at' => now(),
        ]));

        expect(app(SubscriptionService::class)->getCommissionRate($user))->toBe(5);
    });

    it('markAsEarlyHost sets early host flag with null expiry', function () {
        $user = authenticatedUser();
        $service = app(SubscriptionService::class);
        $service->assignFreePlan($user);

        $subscription = $service->markAsEarlyHost($user);

        expect($subscription->is_early_host)->toBeTrue();
        expect($subscription->early_host_expires_at)->toBeNull();
    });

    it('isCommissionFree returns true for early host', function () {
        $user = authenticatedUser();
        $service = app(SubscriptionService::class);
        $service->assignFreePlan($user);
        $service->markAsEarlyHost($user);

        expect($service->isCommissionFree($user))->toBeTrue();
    });

    it('getCommissionRate returns 0 for early host', function () {
        $user = authenticatedUser();
        $service = app(SubscriptionService::class);
        $service->assignFreePlan($user);
        $service->markAsEarlyHost($user);

        expect($service->getCommissionRate($user))->toBe(0);
    });

    it('isColdStartActive returns true when cold_start flag is enabled', function () {
        FeatureFlag::create([
            'key' => 'cold_start',
            'name' => 'Cold Start',
            'is_enabled' => true,
        ]);

        expect(app(SubscriptionService::class)->isColdStartActive())->toBeTrue();
    });

    it('isColdStartActive returns false when cold_start flag is disabled', function () {
        expect(app(SubscriptionService::class)->isColdStartActive())->toBeFalse();
    });

    it('sets early host expiry when cold_start flag is turned off', function () {
        $user = authenticatedUser();
        $service = app(SubscriptionService::class);
        $service->assignFreePlan($user);
        $service->markAsEarlyHost($user);

        $flag = FeatureFlag::create([
            'key' => 'cold_start',
            'name' => 'Cold Start',
            'is_enabled' => true,
        ]);

        // Turn off cold_start
        $flag->update(['is_enabled' => false]);

        $subscription = $user->hostSubscription()->first();
        expect($subscription->early_host_expires_at)->not->toBeNull();
        expect($subscription->early_host_expires_at->isFuture())->toBeTrue();
    });
});

// ============================================
// Registration — cold start auto early host
// ============================================

describe('POST /api/sign-up cold start early host', function () {
    beforeEach(function () {
        seedPlans();
        Http::fake();
        Mail::fake();
    });

    it('marks new user as early host when cold_start flag is active', function () {
        FeatureFlag::create([
            'key' => 'cold_start',
            'name' => 'Cold Start',
            'is_enabled' => true,
        ]);

        $this->postJson(route('api.signup'), [
            'email' => 'earlyhost@gmail.com',
            'password' => 'SecureP@ss2024!',
            'confirm_password' => 'SecureP@ss2024!',
        ])->assertStatus(201);

        $subscription = HostSubscription::withoutAuthorization(
            fn () => HostSubscription::whereHas('user', fn ($q) => $q->where('email', 'earlyhost@gmail.com'))->first()
        );

        expect($subscription->is_early_host)->toBeTrue();
        // expiry is null until cold_start flag is turned off
        expect($subscription->early_host_expires_at)->toBeNull();
    });

    it('does not mark new user as early host when cold_start flag is inactive', function () {
        $this->postJson(route('api.signup'), [
            'email' => 'normaluser@gmail.com',
            'password' => 'SecureP@ss2024!',
            'confirm_password' => 'SecureP@ss2024!',
        ])->assertStatus(201);

        $subscription = HostSubscription::withoutAuthorization(
            fn () => HostSubscription::whereHas('user', fn ($q) => $q->where('email', 'normaluser@gmail.com'))->first()
        );

        expect($subscription->is_early_host)->toBeFalse();
    });
});
