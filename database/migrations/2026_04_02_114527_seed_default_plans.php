<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $plans = [
            [
                'name' => 'Free',
                'code' => 'free',
                'price_eur' => 0,
                'commission_rate' => 12,
                'billing_period' => 'monthly',
                'is_active' => true,
                'features' => json_encode(['Unlimited listings', '12% platform commission', 'AI Receptionist (limited)']),
                'sort_order' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Club',
                'code' => 'club',
                'price_eur' => 2900,
                'commission_rate' => 6,
                'billing_period' => 'monthly',
                'is_active' => true,
                'features' => json_encode(['Unlimited listings', '6% platform commission', 'AI Receptionist (full)']),
                'sort_order' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        foreach ($plans as $plan) {
            DB::table('plans')->updateOrInsert(['code' => $plan['code']], $plan);
        }
    }

    public function down(): void
    {
        DB::table('plans')->whereIn('code', ['free', 'club'])->delete();
    }
};
