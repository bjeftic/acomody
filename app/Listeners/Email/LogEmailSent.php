<?php

namespace App\Listeners\Email;

use App\Enums\Activity\ActivityEvent;
use App\Enums\Email\EmailStatus;
use App\Models\EmailLog;
use App\Services\ActivityLogService;
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

        $emailLog = EmailLog::find((int) $logId);

        if ($emailLog) {
            ActivityLogService::log(
                event: ActivityEvent::EmailSent,
                description: "Email sent to {$emailLog->recipient_email}: {$emailLog->subject}",
                subject: $emailLog,
                properties: [
                    'recipient_email' => $emailLog->recipient_email,
                    'subject' => $emailLog->subject,
                ],
            );
        }
    }
}
