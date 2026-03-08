<?php

use App\Enums\Booking\BookingStatus;
use App\Enums\Booking\PaymentStatus;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Support\Facades\Event;

// ============================================================
// Helper: create a confirmed, paid booking with a given cancellation policy.
// ============================================================

function makePaidConfirmedBooking(string $policy, int $daysUntilCheckIn, float $total = 200.0): Booking
{
    $host = authenticatedUser();
    $guest = authenticatedUser();
    $accommodation = createAccommodation($host, ['cancellation_policy' => $policy]);

    return Booking::create([
        'accommodation_id' => $accommodation->id,
        'user_id' => $guest->id,
        'host_user_id' => $host->id,
        'check_in' => now()->addDays($daysUntilCheckIn)->toDateString(),
        'check_out' => now()->addDays($daysUntilCheckIn + 3)->toDateString(),
        'nights' => 3,
        'guests' => 2,
        'status' => BookingStatus::CONFIRMED,
        'booking_type' => 'request_to_book',
        'currency' => 'EUR',
        'subtotal' => $total,
        'total_price' => $total,
        'payment_status' => PaymentStatus::PAID,
    ])->load('accommodation');
}

// ============================================================
// Preconditions — no refund regardless of policy
// ============================================================

describe('calculateRefundAmount — preconditions', function () {

    it('returns 0 when the booking is unpaid', function () {
        $service = app(BookingService::class);
        $host = authenticatedUser();
        $guest = authenticatedUser();
        $accommodation = createAccommodation($host, ['cancellation_policy' => 'flexible']);

        $booking = Booking::create([
            'accommodation_id' => $accommodation->id,
            'user_id' => $guest->id,
            'host_user_id' => $host->id,
            'check_in' => now()->addDays(10)->toDateString(),
            'check_out' => now()->addDays(13)->toDateString(),
            'nights' => 3,
            'guests' => 2,
            'status' => BookingStatus::CONFIRMED,
            'booking_type' => 'request_to_book',
            'currency' => 'EUR',
            'subtotal' => 200.0,
            'total_price' => 200.0,
            'payment_status' => PaymentStatus::UNPAID,
        ])->load('accommodation');

        expect($service->calculateRefundAmount($booking))->toBe(0.0);
    });

    it('returns 0 for a pending (not yet confirmed) booking even if paid', function () {
        $service = app(BookingService::class);
        $host = authenticatedUser();
        $guest = authenticatedUser();
        $accommodation = createAccommodation($host, ['cancellation_policy' => 'flexible']);

        $booking = Booking::create([
            'accommodation_id' => $accommodation->id,
            'user_id' => $guest->id,
            'host_user_id' => $host->id,
            'check_in' => now()->addDays(10)->toDateString(),
            'check_out' => now()->addDays(13)->toDateString(),
            'nights' => 3,
            'guests' => 2,
            'status' => BookingStatus::PENDING,
            'booking_type' => 'request_to_book',
            'currency' => 'EUR',
            'subtotal' => 200.0,
            'total_price' => 200.0,
            'payment_status' => PaymentStatus::PAID,
        ])->load('accommodation');

        expect($service->calculateRefundAmount($booking))->toBe(0.0);
    });

});

// ============================================================
// Flexible policy: full refund ≥1 day, no refund on check-in day
// ============================================================

describe('calculateRefundAmount — flexible policy', function () {

    it('returns full refund when check-in is more than 1 day away', function () {
        $service = app(BookingService::class);
        $booking = makePaidConfirmedBooking('flexible', 5, 200.0);

        expect($service->calculateRefundAmount($booking))->toBe(200.0);
    });

    it('returns full refund when check-in is exactly 1 day away', function () {
        $service = app(BookingService::class);
        $booking = makePaidConfirmedBooking('flexible', 1, 200.0);

        expect($service->calculateRefundAmount($booking))->toBe(200.0);
    });

    it('returns no refund when check-in is today', function () {
        $service = app(BookingService::class);
        $booking = makePaidConfirmedBooking('flexible', 0, 200.0);

        expect($service->calculateRefundAmount($booking))->toBe(0.0);
    });

});

// ============================================================
// Moderate policy: full ≥5 days, 50% 0-4 days
// ============================================================

describe('calculateRefundAmount — moderate policy', function () {

    it('returns full refund when check-in is 5 or more days away', function () {
        $service = app(BookingService::class);
        $booking = makePaidConfirmedBooking('moderate', 5, 200.0);

        expect($service->calculateRefundAmount($booking))->toBe(200.0);
    });

    it('returns 50% refund when check-in is 1 to 4 days away', function () {
        $service = app(BookingService::class);
        $booking = makePaidConfirmedBooking('moderate', 3, 200.0);

        expect($service->calculateRefundAmount($booking))->toBe(100.0);
    });

    it('returns 50% refund when check-in is today', function () {
        $service = app(BookingService::class);
        $booking = makePaidConfirmedBooking('moderate', 0, 200.0);

        expect($service->calculateRefundAmount($booking))->toBe(100.0);
    });

});

// ============================================================
// Firm policy: 50% ≥30 days, no refund otherwise
// ============================================================

describe('calculateRefundAmount — firm policy', function () {

    it('returns 50% refund when check-in is 30 or more days away', function () {
        $service = app(BookingService::class);
        $booking = makePaidConfirmedBooking('firm', 30, 200.0);

        expect($service->calculateRefundAmount($booking))->toBe(100.0);
    });

    it('returns no refund when check-in is less than 30 days away', function () {
        $service = app(BookingService::class);
        $booking = makePaidConfirmedBooking('firm', 29, 200.0);

        expect($service->calculateRefundAmount($booking))->toBe(0.0);
    });

    it('returns no refund when check-in is today', function () {
        $service = app(BookingService::class);
        $booking = makePaidConfirmedBooking('firm', 0, 200.0);

        expect($service->calculateRefundAmount($booking))->toBe(0.0);
    });

});

// ============================================================
// Strict policy: 50% ≥60 days, no refund otherwise
// ============================================================

describe('calculateRefundAmount — strict policy', function () {

    it('returns 50% refund when check-in is 60 or more days away', function () {
        $service = app(BookingService::class);
        $booking = makePaidConfirmedBooking('strict', 60, 200.0);

        expect($service->calculateRefundAmount($booking))->toBe(100.0);
    });

    it('returns no refund when check-in is less than 60 days away', function () {
        $service = app(BookingService::class);
        $booking = makePaidConfirmedBooking('strict', 59, 200.0);

        expect($service->calculateRefundAmount($booking))->toBe(0.0);
    });

    it('returns no refund when check-in is today', function () {
        $service = app(BookingService::class);
        $booking = makePaidConfirmedBooking('strict', 0, 200.0);

        expect($service->calculateRefundAmount($booking))->toBe(0.0);
    });

});

// ============================================================
// Non-refundable policy: always 0
// ============================================================

describe('calculateRefundAmount — non_refundable policy', function () {

    it('returns no refund regardless of days until check-in', function () {
        $service = app(BookingService::class);

        foreach ([0, 1, 30, 90] as $days) {
            $booking = makePaidConfirmedBooking('non_refundable', $days, 200.0);
            expect($service->calculateRefundAmount($booking))->toBe(0.0);
        }
    });

});

// ============================================================
// HTTP flow — refund_amount reflected in cancellation response
// ============================================================

describe('POST /api/bookings/{booking}/cancel — refund_amount in response', function () {

    it('includes refund_amount of 0 in response when booking is unpaid', function () {
        Event::fake();

        $guest = authenticatedUser();
        $booking = createBooking($guest, authenticatedUser());

        $this->actingAs($guest, 'sanctum')
            ->postJson(route('api.bookings.cancel', $booking), [])
            ->assertSuccessful()
            ->assertJsonPath('data.refund_amount', 0);
    });

});
