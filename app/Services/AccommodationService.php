<?php

namespace App\Services;

use App\Mail\Accommodation\DraftSubmittedMail;
use App\Mail\Accommodation\DraftSubmittedProfileIncompleteMail;
use App\Models\Accommodation;
use App\Models\AccommodationDraft;
use App\Models\Currency;
use App\Notifications\AccommodationUnderReviewNotification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class AccommodationService
{
    public function __construct(private readonly CurrencyService $currencyService) {}

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
        $previousStatus = $accommodationDraft->status;

        $accommodationDraft->update([
            'data' => json_encode($data),
            'current_step' => $currentStep,
            'status' => $status,
            'last_saved_at' => now(),
        ]);

        if ($previousStatus !== 'waiting_for_approval' && $status === 'waiting_for_approval') {
            /** @var \App\Models\User|null $user */
            $user = $accommodationDraft->user;

            if ($user) {
                $mail = $user->hasCompleteHostProfile()
                    ? new DraftSubmittedMail($accommodationDraft)
                    : new DraftSubmittedProfileIncompleteMail($accommodationDraft);

                Mail::to($user->email)->queue($mail);
                $user->notify(new AccommodationUnderReviewNotification($accommodationDraft));
            }
        }

        return $accommodationDraft;
    }

    public static function getAccommodationDrafts(int $userId, ?string $status = null): Collection
    {
        return AccommodationDraft::where('user_id', $userId)
            ->when($status, fn ($query) => $query->where('status', $status))
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
            ->mapWithKeys(fn ($item) => [$item->status => $item->count]);

        return $stats->toArray();
    }

    public static function getAccommodations(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return Accommodation::where('user_id', $userId)
            ->latest() // or orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public static function getAccommodationById(int $userId, string $accommodationId): ?Accommodation
    {
        return Accommodation::where('user_id', $userId)
            ->where('id', $accommodationId)
            ->first();
    }

    public function fetchAccommodation(string $accommodationId): ?Accommodation
    {
        $accommodation = Accommodation::query()
            ->with([
                'amenities' => fn ($q) => $q->orderBy('sort_order'),
                'photos' => fn ($q) => $q->whereNull('deleted_at'),
                'pricing' => fn ($q) => $q->where('is_active', true),
            ])
            ->find($accommodationId);

        if ($accommodation) {
            // @phpstan-ignore-next-line
            $accommodation->host_profile = DB::table('host_profiles')
                ->where('user_id', $accommodation->user_id)
                ->select('id', 'display_name', 'bio', 'avatar')
                ->first();
        }

        return $accommodation;
    }

    public function getAccommodationForEdit(string $accommodationId): ?Accommodation
    {
        return Accommodation::query()
            ->with([
                'amenities',
                'photos' => fn ($q) => $q->whereNull('deleted_at'),
                'pricing' => fn ($q) => $q->where('is_active', true),
                'beds',
                'location.country',
            ])
            ->find($accommodationId);
    }

    public function updateAccommodation(Accommodation $accommodation, array $data): Accommodation
    {
        $accommodation->update([
            'accommodation_type' => $data['accommodation_type'],
            'accommodation_occupation' => $data['accommodation_occupation'],
            'title' => $data['title'],
            'description' => $data['description'],
            'street_address' => $data['address']['street'] ?? $accommodation->street_address,
            'latitude' => $data['coordinates']['latitude'] ?? $accommodation->latitude,
            'longitude' => $data['coordinates']['longitude'] ?? $accommodation->longitude,
            'max_guests' => $data['floor_plan']['guests'],
            'bedrooms' => $data['floor_plan']['bedrooms'],
            'bathrooms' => $data['floor_plan']['bathrooms'],
            'booking_type' => $data['pricing']['bookingType'],
            'cancellation_policy' => $data['house_rules']['cancellationPolicy'],
            'payment_policy' => $data['house_rules']['paymentPolicy'] ?? 'on_site',
            'check_in_from' => $data['house_rules']['checkInFrom'],
            'check_in_until' => $data['house_rules']['checkInUntil'],
            'check_out_until' => $data['house_rules']['checkOutUntil'],
            'quiet_hours_from' => $data['house_rules']['quietHoursFrom'] ?? null,
            'quiet_hours_until' => $data['house_rules']['quietHoursUntil'] ?? null,
        ]);

        $accommodation->amenities()->sync($data['amenities'] ?? []);

        $bedTypes = collect($data['floor_plan']['bed_types'] ?? [])
            ->filter(fn (array $bt) => ($bt['quantity'] ?? 0) > 0)
            ->map(fn (array $bt) => [
                'bed_type' => $bt['bed_type'],
                'quantity' => $bt['quantity'],
            ])
            ->values()
            ->toArray();

        if (! empty($bedTypes)) {
            $accommodation->beds()->delete();
            $accommodation->beds()->createMany($bedTypes);
        }

        /** @var \App\Models\Location $location */
        $location = $accommodation->location;
        /** @var \App\Models\Country $country */
        $country = $location->country;
        $currency = $this->currencyService->getCurrencyByCountry($country->iso_code_2) ?? Currency::where('code', 'EUR')->first();

        if ($accommodation->pricing) {
            $accommodation->pricing->update([
                'base_price' => $data['pricing']['basePrice'],
                'base_price_eur' => calculatePriceInSettedCurrency($data['pricing']['basePrice'], $currency->code, 'EUR'),
                'min_quantity' => $data['pricing']['minStay'],
            ]);
        }

        return $accommodation->fresh(['amenities', 'photos', 'pricing', 'beds', 'location.country']);
    }
}
