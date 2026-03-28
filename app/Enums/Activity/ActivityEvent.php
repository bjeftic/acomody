<?php

namespace App\Enums\Activity;

enum ActivityEvent: string
{
    // User
    case UserRegistered = 'user.registered';
    case UserEmailVerified = 'user.email_verified';

    // Host profile
    case HostProfileCreated = 'host.profile_created';
    case HostProfileCompleted = 'host.profile_completed';

    // Accommodation drafts
    case AccommodationDraftSubmitted = 'accommodation.draft_submitted';
    case AccommodationApproved = 'accommodation.approved';
    case AccommodationRejected = 'accommodation.rejected';

    // Bookings
    case BookingCreated = 'booking.created';
    case BookingConfirmed = 'booking.confirmed';
    case BookingDeclined = 'booking.declined';
    case BookingCancelled = 'booking.cancelled';
    case BookingCompleted = 'booking.completed';

    // Payments (observer-based; Stripe webhook handler updates payment_status → observer fires)
    case PaymentReceived = 'payment.received';
    case PaymentRefunded = 'payment.refunded';
    case PaymentPartiallyRefunded = 'payment.partially_refunded';
    case PaymentFailed = 'payment.failed';
    case PaymentDisputeOpened = 'payment.dispute_opened';
    case PaymentDisputeResolved = 'payment.dispute_resolved';

    // Emails
    case EmailSent = 'email.sent';
    case EmailFailed = 'email.failed';

    public function label(): string
    {
        return match ($this) {
            self::UserRegistered => 'User Registered',
            self::UserEmailVerified => 'Email Verified',
            self::HostProfileCreated => 'Host Profile Created',
            self::HostProfileCompleted => 'Host Profile Completed',
            self::AccommodationDraftSubmitted => 'Draft Submitted',
            self::AccommodationApproved => 'Accommodation Approved',
            self::AccommodationRejected => 'Accommodation Rejected',
            self::BookingCreated => 'Booking Created',
            self::BookingConfirmed => 'Booking Confirmed',
            self::BookingDeclined => 'Booking Declined',
            self::BookingCancelled => 'Booking Cancelled',
            self::BookingCompleted => 'Booking Completed',
            self::PaymentReceived => 'Payment Received',
            self::PaymentRefunded => 'Payment Refunded',
            self::PaymentPartiallyRefunded => 'Partially Refunded',
            self::PaymentFailed => 'Payment Failed',
            self::PaymentDisputeOpened => 'Dispute Opened',
            self::PaymentDisputeResolved => 'Dispute Resolved',
            self::EmailSent => 'Email Sent',
            self::EmailFailed => 'Email Failed',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::UserRegistered, self::UserEmailVerified, self::HostProfileCreated, self::HostProfileCompleted => 'label-info',
            self::AccommodationDraftSubmitted => 'label-warning',
            self::AccommodationApproved => 'label-success',
            self::AccommodationRejected => 'label-danger',
            self::BookingCreated => 'label-primary',
            self::BookingConfirmed => 'label-success',
            self::BookingDeclined, self::BookingCancelled => 'label-danger',
            self::BookingCompleted => 'label-success',
            self::PaymentReceived => 'label-success',
            self::PaymentRefunded, self::PaymentPartiallyRefunded => 'label-warning',
            self::PaymentFailed => 'label-danger',
            self::PaymentDisputeOpened => 'label-danger',
            self::PaymentDisputeResolved => 'label-success',
            self::EmailSent => 'label-default',
            self::EmailFailed => 'label-danger',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::UserRegistered => '👤',
            self::UserEmailVerified => '✉️',
            self::HostProfileCreated, self::HostProfileCompleted => '🏠',
            self::AccommodationDraftSubmitted => '📝',
            self::AccommodationApproved => '✅',
            self::AccommodationRejected => '❌',
            self::BookingCreated => '📅',
            self::BookingConfirmed => '✅',
            self::BookingDeclined => '🚫',
            self::BookingCancelled => '🚫',
            self::BookingCompleted => '🎉',
            self::PaymentReceived => '💳',
            self::PaymentRefunded, self::PaymentPartiallyRefunded => '↩️',
            self::PaymentFailed => '💳',
            self::PaymentDisputeOpened => '⚠️',
            self::PaymentDisputeResolved => '✅',
            self::EmailSent => '📧',
            self::EmailFailed => '📧',
        };
    }

    /** @return self[] */
    public static function userEvents(): array
    {
        return [
            self::UserRegistered,
            self::UserEmailVerified,
            self::HostProfileCreated,
            self::HostProfileCompleted,
        ];
    }

    /** @return self[] */
    public static function accommodationEvents(): array
    {
        return [
            self::AccommodationDraftSubmitted,
            self::AccommodationApproved,
            self::AccommodationRejected,
        ];
    }

    /** @return self[] */
    public static function bookingEvents(): array
    {
        return [
            self::BookingCreated,
            self::BookingConfirmed,
            self::BookingDeclined,
            self::BookingCancelled,
            self::BookingCompleted,
        ];
    }

    /** @return self[] */
    public static function paymentEvents(): array
    {
        return [
            self::PaymentReceived,
            self::PaymentRefunded,
            self::PaymentPartiallyRefunded,
            self::PaymentFailed,
            self::PaymentDisputeOpened,
            self::PaymentDisputeResolved,
        ];
    }

    /** @return self[] */
    public static function emailEvents(): array
    {
        return [
            self::EmailSent,
            self::EmailFailed,
        ];
    }
}
