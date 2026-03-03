<?php

use App\Enums\Accommodation\BookingType;
use App\Enums\Booking\BookingStatus;
use App\Enums\Booking\PaymentStatus;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Support\Facades\Event;

// ============================================================
// Guest Booking controller  — /api/bookings  (auth:sanctum)
// ============================================================

// Helper: create a booking without going through BookingService.
// Uses a different name to avoid conflict with BookingEmailsTest::createBooking().
function createGuestBooking(array $attributes = []): Booking
{
    $guest = authenticatedUser();
    $host = authenticatedUser();
    $accommodation = createAccommodation($host);

    return Booking::withoutAuthorization(fn () => Booking::create(array_merge([
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
}

// ============================================================
// GET /api/bookings (index)
// ============================================================

describe('GET /api/bookings (guest index)', function () {

    it('returns 401 for unauthenticated requests', function () {
        $this->getJson(route('api.bookings.index'))
            ->assertUnauthorized();
    });

    it('returns 200 with correct JSON structure', function () {
        $user = authenticatedUser();

        $this->actingAs($user, 'sanctum')
            ->getJson(route('api.bookings.index'))
            ->assertSuccessful()
            ->assertJson(['success' => true, 'message' => 'Bookings retrieved successfully'])
            ->assertJsonStructure(['success', 'message', 'data', 'meta']);
    });

    it('returns an empty data array when user has no bookings', function () {
        $user = authenticatedUser();

        $this->actingAs($user, 'sanctum')
            ->getJson(route('api.bookings.index'))
            ->assertSuccessful()
            ->assertJson(['data' => []]);
    });

    it('returns only the authenticated guest\'s bookings', function () {
        $booking = createGuestBooking();
        $guest = Booking::withoutAuthorization(fn () => $booking->fresh()->guest);

        $this->actingAs($guest, 'sanctum')
            ->getJson(route('api.bookings.index'))
            ->assertSuccessful()
            ->assertJsonCount(1, 'data');
    });

    it('returns pagination meta', function () {
        $user = authenticatedUser();

        $this->actingAs($user, 'sanctum')
            ->getJson(route('api.bookings.index'))
            ->assertSuccessful()
            ->assertJsonStructure(['meta' => ['current_page', 'last_page', 'per_page', 'total']]);
    });

});

// ============================================================
// GET /api/bookings/{booking} (show)
// ============================================================

describe('GET /api/bookings/{booking} (guest show)', function () {

    it('returns 401 for unauthenticated requests', function () {
        $booking = createGuestBooking();

        $this->getJson(route('api.bookings.show', $booking))
            ->assertUnauthorized();
    });

    it('returns 200 when the guest owns the booking', function () {
        $booking = createGuestBooking();
        $guest = Booking::withoutAuthorization(fn () => $booking->fresh()->guest);

        $this->actingAs($guest, 'sanctum')
            ->getJson(route('api.bookings.show', $booking))
            ->assertSuccessful()
            ->assertJson(['success' => true, 'message' => 'Booking retrieved successfully']);
    });

    it('returns 403 when another user tries to access the booking', function () {
        $booking = createGuestBooking();
        $otherGuest = authenticatedUser();

        $this->actingAs($otherGuest, 'sanctum')
            ->getJson(route('api.bookings.show', $booking))
            ->assertForbidden();
    });

    it('returns correct JSON structure on success', function () {
        $booking = createGuestBooking();
        $guest = Booking::withoutAuthorization(fn () => $booking->fresh()->guest);

        $this->actingAs($guest, 'sanctum')
            ->getJson(route('api.bookings.show', $booking))
            ->assertSuccessful()
            ->assertJsonStructure(['success', 'message', 'data']);
    });

});

// ============================================================
// POST /api/bookings (store)
// ============================================================

describe('POST /api/bookings (store)', function () {

    it('returns 401 for unauthenticated requests', function () {
        $this->postJson(route('api.bookings.store'), [])
            ->assertUnauthorized();
    });

    it('returns 422 when accommodation_id is missing', function () {
        $user = authenticatedUser();

        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.bookings.store'), [
                'check_in' => now()->addDays(5)->toDateString(),
                'check_out' => now()->addDays(8)->toDateString(),
                'guests' => 2,
            ])
            ->assertUnprocessable()
            ->assertJsonFragment(['field' => 'accommodation_id']);
    });

    it('returns 422 when check_in is missing', function () {
        $user = authenticatedUser();
        $accommodation = createAccommodation($user);

        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.bookings.store'), [
                'accommodation_id' => $accommodation->id,
                'check_out' => now()->addDays(8)->toDateString(),
                'guests' => 2,
            ])
            ->assertUnprocessable()
            ->assertJsonFragment(['field' => 'check_in']);
    });

    it('returns 422 when check_out is missing', function () {
        $user = authenticatedUser();
        $accommodation = createAccommodation($user);

        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.bookings.store'), [
                'accommodation_id' => $accommodation->id,
                'check_in' => now()->addDays(5)->toDateString(),
                'guests' => 2,
            ])
            ->assertUnprocessable()
            ->assertJsonFragment(['field' => 'check_out']);
    });

    it('returns 422 when check_in is in the past', function () {
        $user = authenticatedUser();
        $accommodation = createAccommodation($user);

        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.bookings.store'), [
                'accommodation_id' => $accommodation->id,
                'check_in' => now()->subDay()->toDateString(),
                'check_out' => now()->addDays(3)->toDateString(),
                'guests' => 2,
            ])
            ->assertUnprocessable()
            ->assertJsonFragment(['field' => 'check_in']);
    });

    it('returns 422 when check_out is before check_in', function () {
        $user = authenticatedUser();
        $accommodation = createAccommodation($user);

        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.bookings.store'), [
                'accommodation_id' => $accommodation->id,
                'check_in' => now()->addDays(8)->toDateString(),
                'check_out' => now()->addDays(5)->toDateString(),
                'guests' => 2,
            ])
            ->assertUnprocessable()
            ->assertJsonFragment(['field' => 'check_out']);
    });

    it('returns 422 when guests is missing', function () {
        $user = authenticatedUser();
        $accommodation = createAccommodation($user);

        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.bookings.store'), [
                'accommodation_id' => $accommodation->id,
                'check_in' => now()->addDays(5)->toDateString(),
                'check_out' => now()->addDays(8)->toDateString(),
            ])
            ->assertUnprocessable()
            ->assertJsonFragment(['field' => 'guests']);
    });

    it('returns 201 when BookingService creates the booking successfully', function () {
        $guest = authenticatedUser();
        $host = authenticatedUser();
        $accommodation = createAccommodation($host);
        $booking = createGuestBooking([
            'user_id' => $guest->id,
            'host_user_id' => $host->id,
            'accommodation_id' => $accommodation->id,
        ]);

        Booking::withoutAuthorization(fn () => $booking->load(['accommodation', 'guest', 'host']));

        $mock = Mockery::mock(BookingService::class);
        $mock->shouldReceive('createBooking')
            ->once()
            ->andReturn($booking);

        $this->app->instance(BookingService::class, $mock);

        $this->actingAs($guest, 'sanctum')
            ->postJson(route('api.bookings.store'), [
                'accommodation_id' => $accommodation->id,
                'check_in' => now()->addDays(5)->toDateString(),
                'check_out' => now()->addDays(8)->toDateString(),
                'guests' => 2,
            ])
            ->assertCreated()
            ->assertJson(['success' => true]);
    });

});

// ============================================================
// POST /api/bookings/{booking}/cancel (cancel)
// ============================================================

describe('POST /api/bookings/{booking}/cancel (guest cancel)', function () {

    it('returns 401 for unauthenticated requests', function () {
        $booking = createGuestBooking();

        $this->postJson(route('api.bookings.cancel', $booking))
            ->assertUnauthorized();
    });

    it('returns 200 when the guest cancels their own pending booking', function () {
        Event::fake();

        $booking = createGuestBooking();
        $guest = Booking::withoutAuthorization(fn () => $booking->fresh()->guest);

        $this->actingAs($guest, 'sanctum')
            ->postJson(route('api.bookings.cancel', $booking), [])
            ->assertSuccessful()
            ->assertJson(['success' => true, 'message' => 'Booking cancelled successfully']);
    });

    it('returns 409 when the booking cannot be cancelled', function () {
        Event::fake();

        $booking = createGuestBooking(['status' => BookingStatus::CANCELLED]);
        $guest = Booking::withoutAuthorization(fn () => $booking->fresh()->guest);

        $this->actingAs($guest, 'sanctum')
            ->postJson(route('api.bookings.cancel', $booking), [])
            ->assertStatus(409);
    });

});
