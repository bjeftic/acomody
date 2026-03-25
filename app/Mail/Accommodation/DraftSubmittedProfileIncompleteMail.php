<?php

namespace App\Mail\Accommodation;

use App\Models\AccommodationDraft;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class DraftSubmittedProfileIncompleteMail extends Mailable
{
    use Queueable;

    public function __construct(
        public readonly AccommodationDraft $draft,
    ) {
        $this->locale($draft->user->preferred_language ?? 'en');
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: __('mail.draft_submitted_incomplete.subject'));
    }

    public function content(): Content
    {
        return new Content(view: 'mail.accommodation.draft-submitted-profile-incomplete');
    }
}
