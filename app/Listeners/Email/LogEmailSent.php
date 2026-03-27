<?php

namespace App\Listeners\Email;

use App\Enums\Email\EmailStatus;
use App\Models\EmailLog;
use Illuminate\Mail\Events\MessageSent;

class LogEmailSent
{
    public function handle(MessageSent $event): void
    {
        $logId = $event->message->getHeaders()->get('X-Acomody-Log-Id')?->getValue();

        if (! $logId) {
            return;
        }

        EmailLog::where('id', (int) $logId)->update([
            'status' => EmailStatus::Sent->value,
            'sent_at' => now(),
        ]);
    }
}
