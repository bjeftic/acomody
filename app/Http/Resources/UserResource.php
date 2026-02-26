<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * @OA\Schema(
 *     schema="User",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="email", type="string", example="user@example.com"),
 *     @OA\Property(property="email_verified_at", type="string", format="date-time", nullable=true, example=null),
 *     @OA\Property(property="status", type="string", example="active"),
 *     @OA\Property(property="terms_accepted", type="boolean", example=true),
 *     @OA\Property(property="privacy_policy_accepted", type="boolean", example=true),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-08-27T21:30:27.000000Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-08-31T21:47:00.000000Z")
 * )
 */
class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,
            'status' => $this->status,
            'terms_accepted' => $this->terms_accepted_at ? true : false,
            'privacy_policy_accepted' => $this->privacy_policy_accepted_at ? true : false,
            'first_name' => $this->userProfile?->first_name,
            'last_name' => $this->userProfile?->last_name,
            'phone' => $this->userProfile?->phone,
            'avatar_url' => $this->userProfile?->avatar
                ? Storage::disk('user_profile_photos')->url($this->userProfile->avatar)
                : null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
