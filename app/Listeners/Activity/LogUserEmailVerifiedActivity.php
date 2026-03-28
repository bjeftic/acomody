<?php

namespace App\Listeners\Activity;

use App\Enums\Activity\ActivityEvent;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Auth\Events\Verified;

class LogUserEmailVerifiedActivity
{
    public function handle(Verified $event): void
    {
        /** @var User $user */
        $user = $event->user;

        ActivityLogService::log(
            event: ActivityEvent::UserEmailVerified,
            description: "Email verified for: {$user->email}",
            subject: $user,
            causer: $user,
        );
    }
}
