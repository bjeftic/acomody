<?php

namespace App\Mail\Booking;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingRequestedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Booking $booking,
        public readonly bool $forHost = false,
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->forHost
            ? "New Booking Request — {$this->booking->accommodation->title}"
            : "Booking Request Submitted — {$this->booking->accommodation->title}";

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            view: $this->forHost ? 'mail.booking.requested-host' : 'mail.booking.requested-guest',
        );
    }
}
