<?php

namespace App\Http\Resources;

use App\Enums\Subscription\PlanCode;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * @OA\Schema(
 *     schema="User",
 *     type="object",
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="email", type="string", example="user@example.com"),
 *     @OA\Property(property="email_verified_at", type="string", format="date-time", nullable=true, example=null),
 *     @OA\Property(property="status", type="string", example="active"),
 *     @OA\Property(property="terms_accepted", type="boolean", example=true),
 *     @OA\Property(property="privacy_policy_accepted", type="boolean", example=true),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-08-27T21:30:27.000000Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-08-31T21:47:00.000000Z")
 * )
 *
 * @mixin \App\Models\User
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
        $isHost = $this->isHost();
        $hasInProgressDraft = $this->accommodationDrafts()
            ->where('status', 'draft')
            ->exists();
        $hasSubmittedDraft = $this->accommodationDrafts()
            ->where('status', 'waiting_for_approval')
            ->exists();
        $hasActiveListing = $this->accommodations()
            ->where('is_active', true)
            ->exists();

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
            'is_host' => $isHost,
            'host_profile' => $isHost ? new HostProfileResource($this->hostProfile) : null,
            'host_profile_complete' => $this->hasCompleteHostProfile(),
            'has_in_progress_draft' => $hasInProgressDraft,
            'has_submitted_draft' => $hasSubmittedDraft,
            'has_active_listing' => $hasActiveListing,
            'subscription' => $this->buildSubscriptionData(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    private function buildSubscriptionData(): ?array
    {
        if (! $this->resource->relationLoaded('hostSubscription')) {
            return null;
        }

        $subscription = $this->resource->hostSubscription;
        $plan = $subscription?->plan;

        return [
            'plan_code' => $plan?->code?->value,
            'plan_name' => $plan?->name,
            'price_eur' => $plan?->price_eur,
            'features' => $plan?->features,
            'is_active' => $subscription?->isActive() ?? false,
            'ends_at' => $subscription?->ends_at?->toISOString(),
            'commission_rate' => $subscription?->commissionRate() ?? ($plan?->commission_rate ?? PlanCode::Free->defaultCommissionRate()),
            'is_commission_free' => $subscription?->isCommissionFree() ?? false,
            'is_early_host' => $subscription?->isEarlyHostActive() ?? false,
            'early_host_expires_at' => $subscription?->early_host_expires_at?->toISOString(),
        ];
    }
}
