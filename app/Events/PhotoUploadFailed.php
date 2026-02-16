<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PhotoUploadFailed implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $photoId;
    public ?string $errorMessage;

    /**
     * Create a new event instance.
     */
    public function __construct(string $photoId, ?string $errorMessage = null)
    {
        $this->photoId = $photoId;
        $this->errorMessage = $errorMessage;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): Channel
    {
        // We need to get the photoable_id from the photo
        // This is a simplified version - in production you might want to pass this explicitly
        $photo = \App\Models\Photo::find($this->photoId);

        if (!$photo) {
            return new PrivateChannel('photo-uploads');
        }

        return new PrivateChannel(
            "accommodation-draft.{$photo->photoable_id}.photos"
        );
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'photo.failed';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'photo_id' => $this->photoId,
            'status' => 'failed',
            'error_message' => $this->errorMessage,
        ];
    }
}
