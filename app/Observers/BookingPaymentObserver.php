<?php

namespace App\Observers;

use App\Enums\Activity\ActivityEvent;
use App\Enums\Booking\PaymentStatus;
use App\Models\Booking;
use App\Services\ActivityLogService;

/**
 * Watches for payment_status changes on bookings.
 *
 * This observer is the single integration point for payment activity logging.
 * It fires regardless of what triggers the status change — direct update,
 * Stripe webhook handler, or any future payment provider.
 *
 * When Stripe is integrated:
 *   - The webhook handler updates booking.payment_status (+ stripe_payment_intent_id etc.)
 *   - This observer fires automatically and captures the Stripe metadata into properties.
 *   - No changes needed here — just add the Stripe fields to the Booking model/migration.
 *
 *
 *
 *
 * Stripe fields to add to bookings table (future migration):
 *   - stripe_payment_intent_id (string, nullable)
 *   - stripe_charge_id (string, nullable)
 *   - stripe_customer_id (string, nullable)
 *   - stripe_refund_id (string, nullable)
 */
class BookingPaymentObserver
{
    public function updated(Booking $booking): void
    {
        if (! $booking->wasChanged('payment_status')) {
            return;
        }

        $booking->loadMissing('accommodation');
        $label = $booking->accommodation?->title ?? "#{$booking->id}";

        $newStatus = $booking->payment_status;

        $activityEvent = match ($newStatus) {
            PaymentStatus::PAID => ActivityEvent::PaymentReceived,
            PaymentStatus::REFUNDED => ActivityEvent::PaymentRefunded,
            PaymentStatus::PARTIALLY_REFUNDED => ActivityEvent::PaymentPartiallyRefunded,
            default => null,
        };

        if ($activityEvent === null) {
            return;
        }

        $description = match ($activityEvent) {
            ActivityEvent::PaymentReceived => "Payment received for booking \"{$label}\" ({$booking->total_price} {$booking->currency})",
            ActivityEvent::PaymentRefunded => "Full refund issued for booking \"{$label}\" ({$booking->refund_amount} {$booking->currency})",
            ActivityEvent::PaymentPartiallyRefunded => "Partial refund issued for booking \"{$label}\" ({$booking->refund_amount} {$booking->currency})",
            default => '',
        };

        // Base properties — always present
        $properties = [
            'accommodation_id' => $booking->accommodation_id,
            'total_price' => $booking->total_price,
            'currency' => $booking->currency,
            'refund_amount' => $booking->refund_amount,
        ];

        // Stripe metadata — captured automatically once Stripe fields are added to bookings table
        foreach (['stripe_payment_intent_id', 'stripe_charge_id', 'stripe_customer_id', 'stripe_refund_id'] as $field) {
            $value = $booking->getAttribute($field);
            if ($value !== null) {
                $properties[$field] = $value;
            }
        }

        ActivityLogService::log(
            event: $activityEvent,
            description: $description,
            subject: $booking,
            properties: $properties,
        );
    }
}
