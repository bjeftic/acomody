<?php

namespace App\Services;

use App\Models\Listing;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListingService
{
    public static function getListings(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return Listing::where('user_id', $userId)
            ->with('listable')
            ->latest() // or orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public static function getListingById(int $userId, string $listingId): ?Listing
    {
        return Listing::where('user_id', $userId)
            ->where('id', $listingId)
            ->with('listable')
            ->first();
    }
}
