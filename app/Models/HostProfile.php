<?php

namespace App\Models;

use App\Enums\Activity\ActivityEvent;
use App\Mail\Host\AccommodationsNowSearchableMail;
use App\Services\ActivityLogService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Mail;

class HostProfile extends Model
{
    /** @use HasFactory<\Database\Factories\HostProfileFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'display_name',
        'business_name',
        'contact_email',
        'phone',
        'address',
        'city',
        'country_id',
        'tax_id',
        'vat_number',
        'bio',
        'avatar',
        'is_complete',
    ];

    protected function casts(): array
    {
        return [
            'is_complete' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $hostProfile) {
            $hostProfile->is_complete = ! empty($hostProfile->display_name)
                && ! empty($hostProfile->contact_email)
                && ! empty($hostProfile->phone);
        });

        static::created(function (self $hostProfile) {
            /** @var User $user */
            $user = $hostProfile->user;

            ActivityLogService::log(
                event: ActivityEvent::HostProfileCreated,
                description: "Host profile created for {$user->email}",
                subject: $user,
                causer: $user,
            );
        });

        static::saved(function (self $hostProfile) {
            if ($hostProfile->wasChanged('is_complete') && $hostProfile->is_complete) {
                /** @var User $user */
                $user = $hostProfile->user;

                ActivityLogService::log(
                    event: ActivityEvent::HostProfileCompleted,
                    description: "Host profile completed for {$user->email}",
                    subject: $user,
                    causer: $user,
                );

                /** @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\Accommodation> $accommodations */
                $accommodations = $user->accommodations()->where('is_active', true)->get();

                if ($accommodations->isNotEmpty()) {
                    // @phpstan-ignore-next-line
                    $accommodations->searchable();

                    Mail::to($user->email)
                        ->queue(new AccommodationsNowSearchableMail($user, $accommodations->count()));
                }
            }
        });
    }

    public function isComplete(): bool
    {
        return $this->is_complete;
    }

    public function canBeReadBy($user): bool
    {
        return $user && $user->id === $this->user_id;
    }

    public function canBeCreatedBy($user): bool
    {
        return $user !== null;
    }

    public function canBeUpdatedBy($user): bool
    {
        return $user && $user->id === $this->user_id;
    }

    public function canBeDeletedBy($user): bool
    {
        return $user && $user->id === $this->user_id;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }
}
