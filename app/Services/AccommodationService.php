<?php

namespace App\Services;

use App\Models\AccommodationDraft;

class AccommodationService
{
    public function createAccommodationDraft(int $userId, array $data): AccommodationDraft
    {
        $accommodationDraft = AccommodationDraft::create([
            'user_id' => $userId,
            'data' => json_encode($data),
            'current_step' => 2,
            'status' => 'draft',
            'last_saved_at' => now(),
        ]);

        return $accommodationDraft;
    }

    public static function updateAccommodationDraft(AccommodationDraft $accommodationDraft, array $data, int $currentStep, string $status): AccommodationDraft
    {
        $accommodationDraft->update([
            'data' => json_encode($data),
            'current_step' => $currentStep,
            'status' => $status,
            'last_saved_at' => now(),
        ]);

        return $accommodationDraft;
    }

    public static function getAccommodationDraft(int $userId, ?string $status = null): AccommodationDraft
    {
        return AccommodationDraft::where('user_id', $userId)
            ->when($status, fn($query) => $query->where('status', $status))
            ->firstOrFail();
    }

    public function getAccommodationDraftStats(int $userId): array
    {
        $stats = AccommodationDraft::query()
            ->where('user_id', $userId)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get()
            ->mapWithKeys(fn($item) => [$item->status => $item->count]);

        return $stats->toArray();
    }
}
