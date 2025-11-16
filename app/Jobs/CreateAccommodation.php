<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\AccommodationDraft;
use App\Models\Accommodation;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Queue\SerializesModels;

class CreateAccommodation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * The maximum number of seconds the job can run.
     */
    public $timeout = 120;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public $backoff = [10, 30, 60];

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
        $listing = Listing::create([
            'listable_type' => Accommodation::class,
            'listable_id' => null,
            'is_active' => false,
            'user_id' => $this->user_id,
            'location_id' => $this->accommodationDraft->data->location_id, // ovo je pitanje
            'longitude' => $this->accommodationDraft->data->address->longitude ?? null,
            'latitude' => $this->accommodationDraft->data->address->latitude ?? null,
            'street_address' => $this->accommodationDraft->data->address->street_address ?? null,
        ]);

        $accommodationData = [];
        $accommodationData['draft_id'] = $this->accommodationDraft->id;
        $accommodationData['title'] = $this->accommodationDraft->data->title;
        $accommodationData['description'] = $this->accommodationDraft->data->description;
        $accommodationData['accommodation_occupation_id'] = $this->accommodationDraft->data->accommodation_occupation_id;
        $accommodationData['amenities'] = $this->accommodationDraft->data->amenities;
        // Example: $accommodationData['name'] = $this->accommodationDraft->name;
        $accommodation = Accommodation::create($accommodationData);

        $listing->listable_id = $accommodation->id;
        $listing->save();
        
        // Notify user
        $user = User::find($this->user_id);
        // Notification::send($user, new AccommodationCreatedNotification($accommodation));
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        \Log::error('Failed to create accommodation', [
            'draft_id' => $this->accommodationDraft->id,
            'user_id' => $this->user_id,
            'error' => $exception->getMessage(),
        ]);
    }
}
