<?php

namespace App\Mail\Booking;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class BookingConfirmedMail extends BookingMailable
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
        $subject = $this->forHost
            ? __('mail.booking_confirmed_host.subject', ['title' => $this->booking->accommodation->title])
            : __('mail.booking_confirmed_guest.subject', ['title' => $this->booking->accommodation->title]);

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            view: $this->forHost ? 'mail.booking.confirmed-host' : 'mail.booking.confirmed',
        );
    }
}
