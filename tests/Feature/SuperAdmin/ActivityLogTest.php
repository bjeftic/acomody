<?php

use App\Enums\Activity\ActivityEvent;
use App\Enums\Booking\PaymentStatus;
use App\Events\Booking\BookingCancelled;
use App\Events\Booking\BookingConfirmed;
use App\Events\Booking\BookingCreated;
use App\Events\Booking\BookingDeclined;
use App\Models\ActivityLog;
use App\Models\Booking;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;

// ============================================================
// Access control
// ============================================================

it('blocks guests from activity logs index', function () {
    $this->get(route('admin.activity-logs.index'))->assertRedirect();
});

it('allows superadmin to view activity logs index', function () {
    superadmin();
    $this->get(route('admin.activity-logs.index'))->assertOk();
});

it('blocks guests from per-user activity log', function () {
    $user = User::factory()->create();
    $this->get(route('admin.activity-logs.user', $user))->assertRedirect();
});

it('allows superadmin to view per-user activity log', function () {
    superadmin();
    $user = User::factory()->create();
    $this->get(route('admin.activity-logs.user', $user))->assertOk();
});

// ============================================================
// ActivityLogService
// ============================================================

it('logs an activity entry', function () {
    $user = User::factory()->create();

    ActivityLogService::log(
        event: ActivityEvent::UserRegistered,
        description: 'Test registration',
        subject: $user,
        properties: ['email' => $user->email],
    );

    $this->assertDatabaseHas('activity_logs', [
        'event' => ActivityEvent::UserRegistered->value,
        'description' => 'Test registration',
        'subject_type' => User::class,
        'subject_id' => $user->id,
    ]);
});

// ============================================================
// Auth event listeners
// ============================================================

it('logs user registration via Registered event', function () {
    $user = User::factory()->create();

    event(new Registered($user));

    $this->assertDatabaseHas('activity_logs', [
        'event' => ActivityEvent::UserRegistered->value,
        'subject_type' => User::class,
        'subject_id' => $user->id,
    ]);
});

it('logs email verification via Verified event', function () {
    $user = User::factory()->create();

    event(new Verified($user));

    $this->assertDatabaseHas('activity_logs', [
        'event' => ActivityEvent::UserEmailVerified->value,
        'subject_type' => User::class,
        'subject_id' => $user->id,
    ]);
});

// ============================================================
// Booking event listeners
// ============================================================

it('logs booking created event', function () {
    $booking = createBookingForActivityTest();

    event(new BookingCreated($booking));

    $this->assertDatabaseHas('activity_logs', [
        'event' => ActivityEvent::BookingCreated->value,
        'subject_type' => Booking::class,
        'subject_id' => $booking->id,
    ]);
});

it('logs booking confirmed event', function () {
    $booking = createBookingForActivityTest();

    event(new BookingConfirmed($booking));

    $this->assertDatabaseHas('activity_logs', [
        'event' => ActivityEvent::BookingConfirmed->value,
        'subject_type' => Booking::class,
        'subject_id' => $booking->id,
    ]);
});

it('logs booking declined event', function () {
    $booking = createBookingForActivityTest();

    event(new BookingDeclined($booking));

    $this->assertDatabaseHas('activity_logs', [
        'event' => ActivityEvent::BookingDeclined->value,
        'subject_type' => Booking::class,
        'subject_id' => $booking->id,
    ]);
});

it('logs booking cancelled event', function () {
    $booking = createBookingForActivityTest();

    event(new BookingCancelled($booking, $booking->user_id));

    $this->assertDatabaseHas('activity_logs', [
        'event' => ActivityEvent::BookingCancelled->value,
        'subject_type' => Booking::class,
        'subject_id' => $booking->id,
    ]);
});

// ============================================================
// Payment observer
// ============================================================

it('logs payment received when booking payment_status changes to paid', function () {
    $booking = createBookingForActivityTest();

    $booking->update(['payment_status' => PaymentStatus::PAID]);

    $this->assertDatabaseHas('activity_logs', [
        'event' => ActivityEvent::PaymentReceived->value,
        'subject_type' => Booking::class,
        'subject_id' => $booking->id,
    ]);
});

it('logs payment refunded when booking payment_status changes to refunded', function () {
    $booking = createBookingForActivityTest();
    $booking->updateQuietly(['payment_status' => PaymentStatus::PAID]);

    $booking->update(['payment_status' => PaymentStatus::REFUNDED]);

    $this->assertDatabaseHas('activity_logs', [
        'event' => ActivityEvent::PaymentRefunded->value,
        'subject_type' => Booking::class,
        'subject_id' => $booking->id,
    ]);
});

it('does not log payment activity when unrelated fields change', function () {
    $booking = createBookingForActivityTest();
    ActivityLog::query()->delete();

    $booking->update(['guests' => 3]);

    $this->assertDatabaseMissing('activity_logs', [
        'subject_type' => Booking::class,
        'subject_id' => $booking->id,
    ]);
});

// ============================================================
// Index filtering
// ============================================================

it('filters activity logs by event type', function () {
    superadmin();

    ActivityLogService::log(ActivityEvent::UserRegistered, 'Registration');
    ActivityLogService::log(ActivityEvent::UserEmailVerified, 'Email verified');

    $this->get(route('admin.activity-logs.index', ['event' => ActivityEvent::UserRegistered->value]))
        ->assertOk()
        ->assertSee('Registration')
        ->assertDontSee('Email verified');
});

it('filters activity logs by user id', function () {
    superadmin();
    $user = User::factory()->create();

    ActivityLogService::log(ActivityEvent::UserRegistered, 'User A reg', $user);
    ActivityLogService::log(ActivityEvent::UserRegistered, 'Other reg');

    $this->get(route('admin.activity-logs.index', ['user_id' => $user->id]))
        ->assertOk()
        ->assertSee('User A reg')
        ->assertDontSee('Other login');
});

it('shows activity timeline on user view page', function () {
    superadmin();
    $user = User::factory()->create();

    ActivityLogService::log(ActivityEvent::UserRegistered, 'Registered user', $user);

    $this->get(url('/admin/users/'.$user->id))
        ->assertOk()
        ->assertSee('Activity Timeline')
        ->assertSee('Registered user');
});

// ============================================================
// Helpers
// ============================================================

function createBookingForActivityTest(): Booking
{
    return createBooking(null, null);
}
