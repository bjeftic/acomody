<?php

use App\Enums\Accommodation\BookingType;
use App\Events\Booking\BookingCancelled;
use App\Events\Booking\BookingConfirmed;
use App\Events\Booking\BookingCreated;
use App\Events\Booking\BookingDeclined;
use App\Listeners\Booking\SendBookingCancelledNotifications;
use App\Listeners\Booking\SendBookingConfirmedNotifications;
use App\Listeners\Booking\SendBookingCreatedNotifications;
use App\Listeners\Booking\SendBookingDeclinedNotification;
use App\Mail\Booking\BookingCancelledMail;
use App\Mail\Booking\BookingConfirmedMail;
use App\Mail\Booking\BookingDeclinedMail;
use App\Mail\Booking\BookingRequestedMail;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;


// ============================================================
// Event → Listener Registration
// ============================================================

describe('event listener registration', function () {

    it('registers SendBookingCreatedNotifications for BookingCreated', function () {
        Event::fake();
        Event::assertListening(BookingCreated::class, SendBookingCreatedNotifications::class);
    });

    it('registers SendBookingConfirmedNotifications for BookingConfirmed', function () {
        Event::fake();
        Event::assertListening(BookingConfirmed::class, SendBookingConfirmedNotifications::class);
    });

    it('registers SendBookingDeclinedNotification for BookingDeclined', function () {
        Event::fake();
        Event::assertListening(BookingDeclined::class, SendBookingDeclinedNotification::class);
    });

    it('registers SendBookingCancelledNotifications for BookingCancelled', function () {
        Event::fake();
        Event::assertListening(BookingCancelled::class, SendBookingCancelledNotifications::class);
    });

});

// ============================================================
// Booking Created
// ============================================================

describe('SendBookingCreatedNotifications', function () {

    it('queues BookingConfirmedMail to guest and host for instant bookings', function () {
        Mail::fake();

        $guest = authenticatedUser();
        $host = authenticatedUser();
        $booking = createBooking($guest, $host, ['booking_type' => BookingType::INSTANT_BOOKING->value]);

        (new SendBookingCreatedNotifications)->handle(new BookingCreated($booking));

        Mail::assertQueued(BookingConfirmedMail::class, 2);
        Mail::assertQueued(BookingConfirmedMail::class, fn ($mail) => $mail->hasTo($booking->guest->email));
        Mail::assertQueued(BookingConfirmedMail::class, fn ($mail) => $mail->hasTo($booking->host->email));
    });

    it('sends the host a forHost copy for instant bookings', function () {
        Mail::fake();

        $guest = authenticatedUser();
        $host = authenticatedUser();
        $booking = createBooking($guest, $host, ['booking_type' => BookingType::INSTANT_BOOKING->value]);

        (new SendBookingCreatedNotifications)->handle(new BookingCreated($booking));

        Mail::assertQueued(BookingConfirmedMail::class, fn ($mail) => $mail->forHost === true);
        Mail::assertQueued(BookingConfirmedMail::class, fn ($mail) => $mail->forHost === false);
    });

    it('queues BookingRequestedMail to guest and host for request-to-book', function () {
        Mail::fake();

        $guest = authenticatedUser();
        $host = authenticatedUser();
        $booking = createBooking($guest, $host, ['booking_type' => BookingType::REQUEST_TO_BOOK->value]);

        (new SendBookingCreatedNotifications)->handle(new BookingCreated($booking));

        Mail::assertQueued(BookingRequestedMail::class, 2);
        Mail::assertQueued(BookingRequestedMail::class, fn ($mail) => $mail->hasTo($booking->guest->email));
        Mail::assertQueued(BookingRequestedMail::class, fn ($mail) => $mail->hasTo($booking->host->email));
    });

    it('sends the host a forHost copy for request-to-book', function () {
        Mail::fake();

        $guest = authenticatedUser();
        $host = authenticatedUser();
        $booking = createBooking($guest, $host, ['booking_type' => BookingType::REQUEST_TO_BOOK->value]);

        (new SendBookingCreatedNotifications)->handle(new BookingCreated($booking));

        Mail::assertQueued(BookingRequestedMail::class, fn ($mail) => $mail->forHost === true);
        Mail::assertQueued(BookingRequestedMail::class, fn ($mail) => $mail->forHost === false);
    });

    it('does not queue BookingRequestedMail for instant bookings', function () {
        Mail::fake();

        $guest = authenticatedUser();
        $host = authenticatedUser();
        $booking = createBooking($guest, $host, ['booking_type' => BookingType::INSTANT_BOOKING->value]);

        (new SendBookingCreatedNotifications)->handle(new BookingCreated($booking));

        Mail::assertNotQueued(BookingRequestedMail::class);
    });

    it('does not queue BookingConfirmedMail for request-to-book', function () {
        Mail::fake();

        $guest = authenticatedUser();
        $host = authenticatedUser();
        $booking = createBooking($guest, $host, ['booking_type' => BookingType::REQUEST_TO_BOOK->value]);

        (new SendBookingCreatedNotifications)->handle(new BookingCreated($booking));

        Mail::assertNotQueued(BookingConfirmedMail::class);
    });

});

// ============================================================
// Booking Confirmed
// ============================================================

describe('SendBookingConfirmedNotifications', function () {

    it('queues BookingConfirmedMail to guest and host', function () {
        Mail::fake();

        $guest = authenticatedUser();
        $host = authenticatedUser();
        $booking = createBooking($guest, $host, ['booking_type' => BookingType::REQUEST_TO_BOOK->value]);

        (new SendBookingConfirmedNotifications)->handle(new BookingConfirmed($booking));

        Mail::assertQueued(BookingConfirmedMail::class, 2);
        Mail::assertQueued(BookingConfirmedMail::class, fn ($mail) => $mail->hasTo($booking->guest->email));
        Mail::assertQueued(BookingConfirmedMail::class, fn ($mail) => $mail->hasTo($booking->host->email));
    });

    it('sends the host a forHost copy', function () {
        Mail::fake();

        $guest = authenticatedUser();
        $host = authenticatedUser();
        $booking = createBooking($guest, $host, ['booking_type' => BookingType::REQUEST_TO_BOOK->value]);

        (new SendBookingConfirmedNotifications)->handle(new BookingConfirmed($booking));

        Mail::assertQueued(BookingConfirmedMail::class, fn ($mail) => $mail->forHost === true);
        Mail::assertQueued(BookingConfirmedMail::class, fn ($mail) => $mail->forHost === false);
    });

});

// ============================================================
// Booking Declined
// ============================================================

describe('SendBookingDeclinedNotification', function () {

    it('queues BookingDeclinedMail to guest only', function () {
        Mail::fake();

        $guest = authenticatedUser();
        $host = authenticatedUser();
        $booking = createBooking($guest, $host, ['booking_type' => BookingType::REQUEST_TO_BOOK->value]);

        (new SendBookingDeclinedNotification)->handle(new BookingDeclined($booking));

        Mail::assertQueued(BookingDeclinedMail::class, 1);
        Mail::assertQueued(BookingDeclinedMail::class, fn ($mail) => $mail->hasTo($booking->guest->email));
    });

    it('does not queue BookingDeclinedMail to host', function () {
        Mail::fake();

        $guest = authenticatedUser();
        $host = authenticatedUser();
        $booking = createBooking($guest, $host, ['booking_type' => BookingType::REQUEST_TO_BOOK->value]);

        (new SendBookingDeclinedNotification)->handle(new BookingDeclined($booking));

        Mail::assertNotQueued(BookingDeclinedMail::class, fn ($mail) => $mail->hasTo($booking->host->email));
    });

});

// ============================================================
// Booking Cancelled
// ============================================================

describe('SendBookingCancelledNotifications', function () {

    it('queues BookingCancelledMail to both guest and host when guest cancels', function () {
        Mail::fake();

        $guest = authenticatedUser();
        $host = authenticatedUser();
        $booking = createBooking($guest, $host);

        (new SendBookingCancelledNotifications)->handle(
            new BookingCancelled($booking, $booking->user_id)
        );

        Mail::assertQueued(BookingCancelledMail::class, 2);
        Mail::assertQueued(BookingCancelledMail::class, fn ($mail) => $mail->hasTo($booking->guest->email));
        Mail::assertQueued(BookingCancelledMail::class, fn ($mail) => $mail->hasTo($booking->host->email));
    });

    it('sends the host a forHost copy when guest cancels', function () {
        Mail::fake();

        $guest = authenticatedUser();
        $host = authenticatedUser();
        $booking = createBooking($guest, $host);

        (new SendBookingCancelledNotifications)->handle(
            new BookingCancelled($booking, $booking->user_id)
        );

        Mail::assertQueued(BookingCancelledMail::class, fn ($mail) => $mail->forHost === true);
        Mail::assertQueued(BookingCancelledMail::class, fn ($mail) => $mail->forHost === false);
    });

    it('queues BookingCancelledMail to guest only when host cancels', function () {
        Mail::fake();

        $guest = authenticatedUser();
        $host = authenticatedUser();
        $booking = createBooking($guest, $host);

        (new SendBookingCancelledNotifications)->handle(
            new BookingCancelled($booking, $booking->host_user_id)
        );

        Mail::assertQueued(BookingCancelledMail::class, 1);
        Mail::assertQueued(BookingCancelledMail::class, fn ($mail) => $mail->hasTo($booking->guest->email));
    });

    it('does not notify the host when they cancel', function () {
        Mail::fake();

        $guest = authenticatedUser();
        $host = authenticatedUser();
        $booking = createBooking($guest, $host);

        (new SendBookingCancelledNotifications)->handle(
            new BookingCancelled($booking, $booking->host_user_id)
        );

        Mail::assertNotQueued(BookingCancelledMail::class, fn ($mail) => $mail->hasTo($booking->host->email));
    });

});
