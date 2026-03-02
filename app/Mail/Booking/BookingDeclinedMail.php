<?php

namespace App\Mail\Booking;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class BookingDeclinedMail extends BookingMailable
{
    use Queueable;

    public function __construct(public readonly Booking $booking) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Booking Request Declined — {$this->booking->accommodation->title}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.booking.declined',
        );
    }
}
