<?php

namespace App\Services;

use App\Models\AccommodationDraft;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

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

    public static function getAccommodationDrafts(int $userId, ?string $status = null): Collection
    {
        return AccommodationDraft::where('user_id', $userId)
            ->when($status, fn($query) => $query->where('status', $status))
            ->get();
    }

    // Fetch a single accommodation draft with 'draft' status for a user
    public static function getAccommodationDraft(int $userId): AccommodationDraft
    {
        return AccommodationDraft::where('user_id', $userId)
            ->where('status', 'draft')
            ->firstOrFail();
    }

    public function getAccommodationDraftStats(int $userId): array
    {
        $stats = DB::table('accommodation_drafts')
            ->where('user_id', $userId)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get()
            ->mapWithKeys(fn($item) => [$item->status => $item->count]);

        return $stats->toArray();
    }
}
