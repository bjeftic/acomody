<?php

namespace App\Services;

use App\Models\AccommodationDraft;

class AccommodationService
{
    public static function saveAccommodationDraft(int $userId, array $data, int $currentStep, string $status): AccommodationDraft
    {
        $accommodationDraft = AccommodationDraft::updateOrCreate(
            ['user_id' => $userId],
            [
                'data' => json_encode($data),
                'current_step' => $currentStep,
                'status' => $status,
                'last_saved_at' => now(),
            ]
        );

        return $accommodationDraft;
    }

    public static function getAccommodationDraft(int $userId): ?AccommodationDraft
    {
        return AccommodationDraft::where('user_id', $userId)->first() ?? null;
    }
}
