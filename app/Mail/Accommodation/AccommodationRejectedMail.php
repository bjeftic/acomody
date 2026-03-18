<?php

namespace App\Mail\Accommodation;

use App\Models\AccommodationDraft;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class AccommodationRejectedMail extends Mailable
{
    use Queueable;

    public function __construct(
        public readonly AccommodationDraft $draft,
        public readonly ?string $reason = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Your Accommodation Submission Was Not Approved');
    }

    public function content(): Content
    {
        return new Content(view: 'mail.accommodation.rejected');
    }
}
