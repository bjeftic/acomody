<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\AccommodationDraft;
use App\Models\Accommodation;
use App\Models\User;

class CreateAccommodation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    protected AccommodationDraft $accommodationDraft;
    protected int $user_id;

    /**
     * Create a new job instance.
     */
    public function __construct(AccommodationDraft $accommodationDraft, int $userId)
    {
        $this->accommodationDraft = $accommodationDraft;
        $this->user_id = $userId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Logic to create accommodation from the draft
        // Notify the user once the accommodation is created

        $accommodationData = [];
        $accommodationData['draft_id'] = $this->accommodationDraft->id;
        $accommodationData['title'] = $this->accommodationDraft->data->title;
        $accommodationData['description'] = $this->accommodationDraft->data->description;
        $accommodationData['accommodation_occupation_id'] = $this->accommodationDraft->data->accommodation_occupation_id;
        $accommodationData['amenities'] = $this->accommodationDraft->data->amenities;
        // Example: $accommodationData['name'] = $this->accommodationDraft->name;
        $accommodation = Accommodation::create($accommodationData);
        // Notify user
        $user = User::find($this->user_id);
        // Notification::send($user, new AccommodationCreatedNotification($accommodation));
    }
}
