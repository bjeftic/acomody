<?php

namespace App\Http\Controllers\SuperAdmin;

use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\Location;
use App\Models\Country;
use App\Enums\Location\LocationType;

class LocationController
{
    /**
     * Show the accommodation draft applications.
     *
     * @return View
     */
    public function index(Request $request): View
    {
        /** @var string $search */
        $search = $request->search ?? '';

        $locationsPaginated = Location::with('country')
            ->latest('id')
            ->when(!empty($search), function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%");
            })
            ->paginate(12)
            ->appends($request->only(['search', 'page']));

        return view('super-admin.locations.index', [
            'locations' => $locationsPaginated,
            'search' => $search,
            'page' => $request->page ?? 1,
        ]);
    }

    /**
     * Show the form for creating a new location.
     *
     * @return View
     */
    public function create(): View
    {
        $countries = Country::all()->pluck('name', 'id', 'iso_code_2')->toArray();
        $locationTypes = collect(LocationType::toArray())->pluck('label', 'id')->toArray();
        return view('super-admin.locations.create')
            ->with('countries', $countries)
            ->with('parentOptions', Location::pluck('name', 'id'))
            ->with('locationTypes', $locationTypes)
            ->with('origin', 'add');
    }

    /**
     * Search locations for select2 dropdown.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $validated = $request->validate([
            'search' => 'nullable|string|max:255',
            'page' => 'nullable|integer|min:1',
        ]);
        $search = $validated['search'] ?? null;
        $page = $validated['page'] ?? 1;
        $perPage = 10;

        $locations = Location::where('name', 'LIKE', "%{$search}%")
            ->select('id', 'name')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'results' => $locations->map(function ($location) {
                return [
                    'id' => $location->id,
                    'text' => $location->name
                ];
            }),
            'pagination' => [
                'more' => $locations->hasMorePages()
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'country' => 'required|exists:countries,id',
            'location_type' => 'required|in:' . implode(',', array_map(fn($type) => $type['id'], LocationType::toArray())),
            'parent' => 'nullable|exists:locations,id',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        Location::create([
            'name' => $validated['name'],
            'country_id' => $validated['country'],
            'location_type' => $validated['location_type'],
            'parent_id' => $validated['parent'] ?? null,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'user_id' => userOrFail()->id,
        ]);

        return redirect()->route('admin.locations.index')
            ->with('success', 'Location created successfully.');
    }
}
