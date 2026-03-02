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
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Booking Cancelled — {$this->booking->accommodation->title}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: $this->forHost ? 'mail.booking.cancelled-host' : 'mail.booking.cancelled-guest',
        );
    }
}
