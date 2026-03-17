<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/** @mixin \App\Models\HostProfile */
class HostProfileResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'display_name' => $this->display_name,
            'business_name' => $this->business_name,
            'contact_email' => $this->contact_email,
            'phone' => $this->phone,
            'address' => $this->address,
            'city' => $this->city,
            'country_id' => $this->country_id,
            'country' => $this->when(
                $this->relationLoaded('country') && $this->country,
                fn () => [
                    'id' => $this->country->id,
                    'name' => $this->country->name,
                    'iso_code' => $this->country->iso_code_2,
                ]
            ),
            'tax_id' => $this->tax_id,
            'vat_number' => $this->vat_number,
            'bio' => $this->bio,
            'avatar_url' => $this->avatar
                ? Storage::disk('user_profile_photos')->url($this->avatar)
                : null,
            'is_complete' => $this->is_complete,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
