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
    ) {
        $this->locale($user->preferred_language ?? 'en');
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: __('mail.listings_live.subject'));
    }

    public function content(): Content
    {
        return new Content(view: 'mail.host.accommodations-now-searchable');
    }
}
