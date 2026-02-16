<?php

namespace App\Events;

use App\Models\Photo;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PhotoUploadCompleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Photo $photo;

    /**
     * Create a new event instance.
     */
    public function __construct(Photo $photo)
    {
        $this->photo = $photo;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): Channel
    {
        return new PrivateChannel(
            "accommodation-draft.{$this->photo->photoable_id}.photos"
        );
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'photo.uploaded';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'photo_id' => $this->photo->id,
            'status' => $this->photo->status,
            'url' => $this->photo->url,
            'urls' => $this->photo->urls,
            'order' => $this->photo->order,
            'processed_at' => $this->photo->processed_at?->toIso8601String(),
        ];
    }
}
