<?php

namespace App\Services;

use App\Enums\DeletionRequestStatus;
use App\Enums\DeletionRequestType;
use App\Models\Accommodation;
use App\Models\DeletionRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DeletionRequestService
{
    /**
     * Submit a full user account deletion request.
     */
    public function requestUserAccountDeletion(User $user): DeletionRequest
    {
        return $this->upsertRequest($user, DeletionRequestType::UserAccount);
    }

    /**
     * Submit a host account deletion request (host profile + listings, user account stays).
     */
    public function requestHostAccountDeletion(User $user): DeletionRequest
    {
        return $this->upsertRequest($user, DeletionRequestType::HostAccount);
    }

    /**
     * Submit a single accommodation deletion request.
     */
    public function requestAccommodationDeletion(User $user, Accommodation $accommodation): DeletionRequest
    {
        return $this->upsertRequest($user, DeletionRequestType::Accommodation, $accommodation->id);
    }

    /**
     * Approve a deletion request and execute the appropriate deletion logic.
     */
    public function approve(DeletionRequest $deletionRequest, User $admin): void
    {
        DB::transaction(function () use ($deletionRequest, $admin) {
            match ($deletionRequest->type) {
                DeletionRequestType::UserAccount => $this->deleteUserAccount($deletionRequest->user),
                DeletionRequestType::HostAccount => $this->deleteHostAccount($deletionRequest->user),
                DeletionRequestType::Accommodation => $this->deleteAccommodation($deletionRequest),
            };

            $deletionRequest->update([
                'status' => DeletionRequestStatus::Approved,
                'processed_by' => $admin->id,
                'processed_at' => now(),
            ]);
        });
    }

    /**
     * Reject a deletion request with an optional reason.
     */
    public function reject(DeletionRequest $deletionRequest, User $admin, ?string $reason = null): void
    {
        $deletionRequest->update([
            'status' => DeletionRequestStatus::Rejected,
            'reason' => $reason,
            'processed_by' => $admin->id,
            'processed_at' => now(),
        ]);
    }

    private function upsertRequest(User $user, DeletionRequestType $type, string|int|null $subjectId = null): DeletionRequest
    {
        return DeletionRequest::updateOrCreate(
            [
                'user_id' => $user->id,
                'type' => $type,
                'subject_id' => $subjectId,
            ],
            [
                'status' => DeletionRequestStatus::Pending,
                'reason' => null,
                'processed_by' => null,
                'processed_at' => null,
            ]
        );
    }

    private function deleteUserAccount(User $user): void
    {
        $this->deleteHostAccount($user);
        $user->delete();
    }

    private function deleteHostAccount(User $user): void
    {
        $user->accommodations()->each(function (Accommodation $accommodation) {
            $accommodation->unsearchable();
            $accommodation->delete();
        });

        $user->accommodationDrafts()->delete();
        $user->hostProfile?->delete();
    }

    private function deleteAccommodation(DeletionRequest $deletionRequest): void
    {
        /** @var User $requestUser */
        $requestUser = $deletionRequest->user;
        $accommodation = $requestUser->accommodations()
            ->find($deletionRequest->subject_id);

        if ($accommodation) {
            $accommodation->unsearchable();
            $accommodation->delete();
        }
    }
}
