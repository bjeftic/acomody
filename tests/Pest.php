<?php

use App\Models\User;
use App\Models\Accommodation;
use App\Models\Booking;
use App\Enums\Booking\PaymentStatus;
use App\Enums\Accommodation\BookingType;
use App\Enums\Booking\BookingStatus;
use Illuminate\Support\Facades\Auth;

uses(
    Tests\TestCase::class,
    Illuminate\Foundation\Testing\RefreshDatabase::class,
)->in('Feature');

// ============================================================
// Global Helpers
// ============================================================

function authenticatedUser(array $attributes = []): User
{
    $user = User::factory()->create(array_merge([
        'email_verified_at' => now(),
        'status'            => 'active',
    ], $attributes));

    Auth::login($user);

    return $user;
}

function createAccommodation(User $user, array $attributes = []): Accommodation
{
    $superadmin = authenticatedUser(['is_superadmin' => true]);

    return Accommodation::factory()->create(array_merge([
        'user_id'     => $user->id,
        'approved_by' => $superadmin->id,
        'is_active'   => true,
    ], $attributes));
}

// ============================================================
// Guest Booking controller  — /api/bookings  (auth:sanctum)
// ============================================================

// Helper: create a booking without going through BookingService.
// Uses a different name to avoid conflict with BookingEmailsTest::createBooking().
function createBooking(User|null $guest, User|null $host, array $attributes = []): Booking
{
    if (!$guest) {
        $guest = authenticatedUser();
    }
    if (!$host) {
        $host = authenticatedUser();
    }

    $accommodation = createAccommodation($host);

    return Booking::create(array_merge([
        'accommodation_id' => $accommodation->id,
        'user_id' => $guest->id,
        'host_user_id' => $host->id,
        'check_in' => now()->addDays(10)->toDateString(),
        'check_out' => now()->addDays(13)->toDateString(),
        'nights' => 3,
        'guests' => 2,
        'status' => BookingStatus::PENDING,
        'booking_type' => BookingType::REQUEST_TO_BOOK->value,
        'currency' => 'EUR',
        'subtotal' => 150.00,
        'total_price' => 150.00,
        'payment_status' => PaymentStatus::UNPAID,
    ], $attributes));
}
