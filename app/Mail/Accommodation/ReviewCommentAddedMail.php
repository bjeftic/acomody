<?php

namespace App\Mail\Accommodation;

use App\Models\AccommodationDraft;
use App\Models\ReviewComment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class ReviewCommentAddedMail extends Mailable
{
    use Queueable;

    public function __construct(
        public readonly AccommodationDraft $draft,
        public readonly ReviewComment $comment,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'A Reviewer Has Left a Comment on Your Accommodation');
    }

    public function content(): Content
    {
        return new Content(view: 'mail.accommodation.review-comment-added');
    }
}
