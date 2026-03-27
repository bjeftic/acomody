<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Free',
                'code' => 'free',

                'price_eur' => 0,
                'commission_rate' => 12,
                'billing_period' => 'monthly',
                'is_active' => true,
                'features' => ['Unlimited listings', '12% platform commission', 'AI Receptionist (limited)'],
                'sort_order' => 1,
            ],
            [
                'name' => 'Club',
                'code' => 'club',

                'price_eur' => 2900,
                'commission_rate' => 6,
                'billing_period' => 'monthly',
                'is_active' => true,
                'features' => ['Unlimited listings', '6% platform commission', 'AI Receptionist (full)'],
                'sort_order' => 2,
            ],
        ];

        Plan::withoutAuthorization(function () use ($plans) {
            foreach ($plans as $plan) {
                Plan::updateOrCreate(['code' => $plan['code']], $plan);
            }

            // Deactivate removed plans
            Plan::whereNotIn('code', array_column($plans, 'code'))->update(['is_active' => false]);
        });
    }
}
