<?php

namespace App\Mail\Booking;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class BookingDeclinedMail extends BookingMailable
{
    use Queueable;

    public function __construct(public readonly Booking $booking)
    {
        $this->locale($booking->guest->preferred_language ?? 'en');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('mail.booking_declined.subject', ['title' => $this->booking->accommodation->title]),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.booking.declined',
        );
    }
}
