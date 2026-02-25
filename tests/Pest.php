<?php

use App\Models\User;
use App\Models\Accommodation;

uses(
    Tests\TestCase::class,
    Illuminate\Foundation\Testing\RefreshDatabase::class,
)->in('Feature');

// ============================================================
// Global Helpers
// ============================================================

function authenticatedUser(array $attributes = []): User
{
    return User::factory()->create(array_merge([
        'email_verified_at' => now(),
        'status'            => 'active',
    ], $attributes));
}

function createAccommodation(User $user, array $attributes = []): Accommodation
{
    return Accommodation::withoutAuthorization(fn () => Accommodation::factory()->create(array_merge([
        'user_id'     => $user->id,
        'approved_by' => $user->id,
        'is_active'   => true,
    ], $attributes)));
}
