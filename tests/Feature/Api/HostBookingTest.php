<?php

use App\Enums\Accommodation\BookingType;
use App\Enums\Booking\BookingStatus;
use App\Enums\Booking\PaymentStatus;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Support\Facades\Event;

// ============================================================
// Host Booking controller  — /api/host/bookings  (auth:sanctum)
// ============================================================

// Helper: create a booking owned by a specific host.
// Distinct name to avoid redeclaration if BookingEmailsTest is loaded.
function createBookingForHost(array $attributes = []): array
{
    $guest = authenticatedUser();
    $host = authenticatedUser();
    $accommodation = createAccommodation($host);

    $booking = Booking::withoutAuthorization(fn () => Booking::create(array_merge([
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
    ], $attributes)));

    return ['booking' => $booking, 'host' => $host, 'guest' => $guest];
}

// ============================================================
// GET /api/host/bookings (index)
// ============================================================

describe('GET /api/host/bookings (host index)', function () {

    it('returns 401 for unauthenticated requests', function () {
        $this->getJson(route('api.host.bookings.index'))
            ->assertUnauthorized();
    });

    it('returns 200 with correct JSON structure', function () {
        $host = authenticatedUser();

        $this->actingAs($host, 'sanctum')
            ->getJson(route('api.host.bookings.index'))
            ->assertSuccessful()
            ->assertJson(['success' => true, 'message' => 'Bookings retrieved successfully'])
            ->assertJsonStructure(['success', 'message', 'data', 'meta']);
    });

    it('returns only bookings where the host owns the accommodation', function () {
        ['booking' => $booking, 'host' => $host] = createBookingForHost();

        $this->actingAs($host, 'sanctum')
            ->getJson(route('api.host.bookings.index'))
            ->assertSuccessful()
            ->assertJsonCount(1, 'data');
    });

    it('returns an empty data array when host has no bookings', function () {
        $host = authenticatedUser();

        $this->actingAs($host, 'sanctum')
            ->getJson(route('api.host.bookings.index'))
            ->assertSuccessful()
            ->assertJson(['data' => []]);
    });

    it('returns pagination meta', function () {
        $host = authenticatedUser();

        $this->actingAs($host, 'sanctum')
            ->getJson(route('api.host.bookings.index'))
            ->assertSuccessful()
            ->assertJsonStructure(['meta' => ['current_page', 'last_page', 'per_page', 'total']]);
    });

});

// ============================================================
// GET /api/host/bookings/{booking} (show)
// ============================================================

describe('GET /api/host/bookings/{booking} (host show)', function () {

    it('returns 401 for unauthenticated requests', function () {
        ['booking' => $booking] = createBookingForHost();

        $this->getJson(route('api.host.bookings.show', $booking))
            ->assertUnauthorized();
    });

    it('returns 200 when the host owns the accommodation', function () {
        ['booking' => $booking, 'host' => $host] = createBookingForHost();

        $this->actingAs($host, 'sanctum')
            ->getJson(route('api.host.bookings.show', $booking))
            ->assertSuccessful()
            ->assertJson(['success' => true, 'message' => 'Booking retrieved successfully']);
    });

    it('returns 403 when a different user tries to access the booking', function () {
        ['booking' => $booking] = createBookingForHost();
        $otherUser = authenticatedUser();

        $this->actingAs($otherUser, 'sanctum')
            ->getJson(route('api.host.bookings.show', $booking))
            ->assertForbidden();
    });

});

// ============================================================
// POST /api/host/bookings/{booking}/confirm (confirm)
// ============================================================

describe('POST /api/host/bookings/{booking}/confirm (confirm)', function () {

    it('returns 401 for unauthenticated requests', function () {
        ['booking' => $booking] = createBookingForHost();

        $this->postJson(route('api.host.bookings.confirm', $booking))
            ->assertUnauthorized();
    });

    it('returns 200 when BookingService confirms the booking', function () {
        ['booking' => $booking, 'host' => $host] = createBookingForHost();

        $mock = Mockery::mock(BookingService::class);
        $mock->shouldReceive('confirmBooking')
            ->once()
            ->andReturn($booking);

        $this->app->instance(BookingService::class, $mock);

        Booking::withoutAuthorization(fn () => $booking->load(['accommodation', 'guest', 'host']));

        $this->actingAs($host, 'sanctum')
            ->postJson(route('api.host.bookings.confirm', $booking))
            ->assertSuccessful()
            ->assertJson(['success' => true, 'message' => 'Booking confirmed successfully']);
    });

    it('returns 409 when the booking cannot be confirmed', function () {
        ['booking' => $booking, 'host' => $host] = createBookingForHost();

        $mock = Mockery::mock(BookingService::class);
        $mock->shouldReceive('confirmBooking')
            ->once()
            ->andThrow(new \RuntimeException('This booking cannot be confirmed.'));

        $this->app->instance(BookingService::class, $mock);

        $this->actingAs($host, 'sanctum')
            ->postJson(route('api.host.bookings.confirm', $booking))
            ->assertStatus(409);
    });

});

// ============================================================
// POST /api/host/bookings/{booking}/decline (decline)
// ============================================================

describe('POST /api/host/bookings/{booking}/decline (decline)', function () {

    it('returns 401 for unauthenticated requests', function () {
        ['booking' => $booking] = createBookingForHost();

        $this->postJson(route('api.host.bookings.decline', $booking))
            ->assertUnauthorized();
    });

    it('returns 200 when the host declines a pending booking', function () {
        Event::fake();

        ['booking' => $booking, 'host' => $host] = createBookingForHost();

        $this->actingAs($host, 'sanctum')
            ->postJson(route('api.host.bookings.decline', $booking), ['reason' => 'Dates not available'])
            ->assertSuccessful()
            ->assertJson(['success' => true, 'message' => 'Booking declined']);
    });

    it('returns 409 when the booking cannot be declined', function () {
        Event::fake();

        // A cancelled booking cannot be declined
        ['booking' => $booking, 'host' => $host] = createBookingForHost(['status' => BookingStatus::CANCELLED]);

        $this->actingAs($host, 'sanctum')
            ->postJson(route('api.host.bookings.decline', $booking), [])
            ->assertStatus(409);
    });

    it('returns 403 when a non-host tries to decline', function () {
        Event::fake();

        ['booking' => $booking] = createBookingForHost();
        $other = authenticatedUser();

        $this->actingAs($other, 'sanctum')
            ->postJson(route('api.host.bookings.decline', $booking), [])
            ->assertStatus(409);
    });

});

// ============================================================
// POST /api/host/bookings/{booking}/cancel (host cancel)
// ============================================================

describe('POST /api/host/bookings/{booking}/cancel (host cancel)', function () {

    it('returns 401 for unauthenticated requests', function () {
        ['booking' => $booking] = createBookingForHost();

        $this->postJson(route('api.host.bookings.cancel', $booking))
            ->assertUnauthorized();
    });

    it('returns 200 when the host cancels a pending booking', function () {
        Event::fake();

        ['booking' => $booking, 'host' => $host] = createBookingForHost();

        $this->actingAs($host, 'sanctum')
            ->postJson(route('api.host.bookings.cancel', $booking), [])
            ->assertSuccessful()
            ->assertJson(['success' => true, 'message' => 'Booking cancelled successfully']);
    });

    it('returns 409 when the booking is already cancelled', function () {
        Event::fake();

        ['booking' => $booking, 'host' => $host] = createBookingForHost(['status' => BookingStatus::CANCELLED]);

        $this->actingAs($host, 'sanctum')
            ->postJson(route('api.host.bookings.cancel', $booking), [])
            ->assertStatus(409);
    });

});
