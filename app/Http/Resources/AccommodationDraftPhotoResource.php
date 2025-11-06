<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccommodationDraftPhotoResource extends JsonResource
{
    /**
     * @OA\Schema(
     *    schema="AccommodationDraftPhoto",
     *   type="object",
     *   @OA\Property(property="id", type="integer", example=1),
     *  @OA\Property(property="accommodation_draft_id", type="integer", example=1),
     *  @OA\Property(
     *    property="urls",
     *   type="object",
     *   @OA\Property(property="original", type="string", example="https://cdn.example.com/photos/original/photo1.jpg"),
     *  @OA\Property(property="large", type="string", example="https://cdn.example.com/photos/large/photo1.jpg"),
     *  @OA\Property(property="medium", type="string", example="https://cdn.example.com/photos/medium/photo1.jpg"),
     *  @OA\Property(property="thumbnail", type="string", example="https://cdn.example.com/photos/thumbnail/photo1.jpg"),
     * ),
     * @OA\Property(
     *   property="file_info",
     *  type="object",
     *  @OA\Property(property="original_filename", type="string", example="photo1.jpg"),
     * @OA\Property(property="mime_type", type="string", example="image/jpeg"),
     * @OA\Property(property="file_size", type="integer", example=204800),
     * @OA\Property(property="formatted_size", type="string", example="200 KB"),
     * @OA\Property(property="width", type="integer", example=1920),
     * @OA\Property(property="height", type="integer", example=1080),
     * ),
     * @OA\Property(property="order", type="integer", example=1),
     * @OA\Property(property="is_primary", type="boolean", example=true),
     * @OA\Property(property="status", type="string", example="completed"),
     * @OA\Property(property="alt_text", type="string", example="A beautiful view from the apartment"),
     * @OA\Property(property="caption", type="string", example="Sunset over the city skyline"),
     * @OA\Property(property="metadata", type="object", example={"camera": "Canon EOS 80D", "lens": "EF-S18-135mm"}),
     * @OA\Property(property="error_message", type="string", example="Failed to process image"),
     * @OA\Property(property="uploaded_at", type="string", format="date-time", example="2025-10-28T12:34:56Z"),
     * @OA\Property(property="processed_at", type="string", format="date-time", example="2025-10-28T12:45:00Z"),
     * @OA\Property(property="created_at", type="string", format="date-time", example="2025-10-28T12:34:56Z"),
     * @OA\Property(property="updated_at", type="string", format="date-time", example="2025-10-28T12:50:00Z"),
     * )
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'accommodation_draft_id' => $this->accommodation_draft_id,

            // URLs for different sizes
            'urls' => [
                'original' => $this->url,
                'large' => $this->large_url,
                'medium' => $this->medium_url,
                'thumbnail' => $this->thumbnail_url,
            ],

            // File information
            'file_info' => [
                'original_filename' => $this->original_filename,
                'mime_type' => $this->mime_type,
                'file_size' => $this->file_size,
                'formatted_size' => $this->formatted_size,
                'width' => $this->width,
                'height' => $this->height,
            ],

            // Photo properties
            'order' => $this->order,
            'is_primary' => $this->is_primary,
            'status' => $this->status,

            // Optional content
            'alt_text' => $this->alt_text,
            'caption' => $this->caption,

            // Metadata (only if needed)
            'metadata' => $this->when(
                $request->get('include_metadata'),
                $this->metadata
            ),

            // Error info (only if failed)
            'error_message' => $this->when(
                $this->status === 'failed',
                $this->error_message
            ),

            // Timestamps
            'uploaded_at' => $this->uploaded_at?->toISOString(),
            'processed_at' => $this->processed_at?->toISOString(),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
