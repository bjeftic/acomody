<?php

namespace App\Mail\Host;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class AccommodationsNowSearchableMail extends Mailable
{
    use Queueable;

    public function __construct(
        public readonly User $user,
        public readonly int $accommodationCount,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Your Listings Are Now Live on Acomody');
    }

    public function content(): Content
    {
        return new Content(view: 'mail.host.accommodations-now-searchable');
    }
}
