<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Models\Accommodation;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccommodationController
{
    public function index(Request $request): View
    {
        /** @var string $search */
        $search = $request->search ?? '';

        $accommodations = Accommodation::with(['user', 'location'])
            ->latest('id')
            ->when(! empty($search), function ($q) use ($search) {
                $q->where('title', 'ilike', "%{$search}%");
            })
            ->paginate(20)
            ->appends($request->only(['search', 'page']));

        return view('super-admin.accommodations.index', [
            'accommodations' => $accommodations,
            'search' => $search,
            'page' => $request->page ?? 1,
        ]);
    }

    public function show(string $id): View
    {
        $accommodation = Accommodation::with([
            'user',
            'location',
            'photos',
            'amenities',
            'pricing.currency',
            'activeFees',
        ])->findOrFail($id);

        return view('super-admin.accommodations.show', compact('accommodation'));
    }
}
