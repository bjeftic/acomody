<?php

namespace App\Mail\Booking;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class BookingCancelledMail extends BookingMailable
{
    use Queueable;

    public function __construct(
        public readonly Booking $booking,
        public readonly bool $forHost = false,
    ) {
        $recipient = $forHost ? $booking->host : $booking->guest;
        $this->locale($recipient->preferred_language ?? 'en');
    }

    public function envelope(): Envelope
    {
        $key = $this->forHost ? 'mail.booking_cancelled_host.subject' : 'mail.booking_cancelled_guest.subject';

        return new Envelope(
            subject: __($key, ['title' => $this->booking->accommodation->title]),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: $this->forHost ? 'mail.booking.cancelled-host' : 'mail.booking.cancelled-guest',
        );
    }
}
