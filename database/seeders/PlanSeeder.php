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
                'max_accommodations' => null,
                'price_eur' => 0,
                'commission_rate' => 10,
                'billing_period' => 'monthly',
                'is_active' => true,
                'features' => ['Unlimited listings', '10% platform commission', 'AI Receptionist (limited)'],
                'sort_order' => 1,
            ],
            [
                'name' => 'Club',
                'code' => 'club',
                'max_accommodations' => null,
                'price_eur' => 3000,
                'commission_rate' => 5,
                'billing_period' => 'monthly',
                'is_active' => true,
                'features' => ['Unlimited listings', '5% platform commission', 'AI Receptionist (full)'],
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
