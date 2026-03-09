<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Enums\Location\LocationType;
use App\Models\Country;
use App\Models\Location;
use App\Services\PhotoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LocationController
{
    public function __construct(protected PhotoService $photoService) {}

    public function index(Request $request): View
    {
        /** @var string $search */
        $search = $request->search ?? '';

        $locationsPaginated = Location::with(['country', 'parent', 'primaryPhoto'])
            ->latest('id')
            ->when(! empty($search), function ($q) use ($search) {
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

    public function create(): View
    {
        $countries = Country::all()->pluck('name', 'id', 'iso_code_2')->toArray();
        $locationTypes = collect(LocationType::toArray())->pluck('label', 'id')->toArray();

        return view('super-admin.locations.create')
            ->with('countries', $countries)
            ->with('locationTypes', $locationTypes)
            ->with('origin', 'add');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate(array_merge(
            $this->nameValidationRules(),
            [
                'country' => 'required|exists:countries,id',
                'location_type' => 'required|in:'.implode(',', array_map(fn ($type) => $type['id'], LocationType::toArray())),
                'parent' => 'nullable|exists:locations,id',
                'latitude' => 'nullable|numeric|between:-90,90',
                'longitude' => 'nullable|numeric|between:-180,180',
                'is_active' => 'boolean',
                'image' => 'nullable|image|max:5120|mimes:jpeg,jpg,png,webp',
            ]
        ));

        $location = Location::withoutSyncingToSearch(fn () => Location::create([
            'name' => $this->buildTranslations($validated['name']),
            'country_id' => $validated['country'],
            'location_type' => $validated['location_type'],
            'parent_id' => $validated['parent'] ?? null,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'is_active' => $request->boolean('is_active'),
            'user_id' => userOrFail()->id,
        ]));

        if ($request->hasFile('image')) {
            $this->photoService->uploadPhoto($location, $request->file('image'), 0, true);
        }

        return redirect()->route('admin.locations.index')
            ->with('success', 'Location created successfully.');
    }

    public function edit(Location $location): View
    {
        $countries = Country::all()->pluck('name', 'id', 'iso_code_2')->toArray();
        $locationTypes = collect(LocationType::toArray())->pluck('label', 'id')->toArray();

        return view('super-admin.locations.edit')
            ->with('location', $location->load(['primaryPhoto', 'parent']))
            ->with('countries', $countries)
            ->with('locationTypes', $locationTypes)
            ->with('origin', 'edit');
    }

    public function update(Request $request, Location $location): RedirectResponse
    {
        $validated = $request->validate(array_merge(
            $this->nameValidationRules(),
            [
                'country' => 'required|exists:countries,id',
                'location_type' => 'required|in:'.implode(',', array_map(fn ($type) => $type['id'], LocationType::toArray())),
                'parent' => 'nullable|exists:locations,id',
                'latitude' => 'nullable|numeric|between:-90,90',
                'longitude' => 'nullable|numeric|between:-180,180',
                'is_active' => 'boolean',
                'remove_image' => 'boolean',
                'image' => 'nullable|image|max:5120|mimes:jpeg,jpg,png,webp',
            ]
        ));

        Location::withoutSyncingToSearch(fn () => $location->update([
            'name' => $this->buildTranslations($validated['name']),
            'country_id' => $validated['country'],
            'location_type' => $validated['location_type'],
            'parent_id' => $validated['parent'] ?? null,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]));

        if ($request->boolean('remove_image') || $request->hasFile('image')) {
            $existingPhoto = $location->primaryPhoto;
            if ($existingPhoto) {
                $this->photoService->deletePhoto($existingPhoto);
            }
        }

        if ($request->hasFile('image')) {
            $this->photoService->uploadPhoto($location, $request->file('image'), 0, true);
        }

        return redirect()->route('admin.locations.index')
            ->with('success', 'Location updated successfully.');
    }

    public function destroy(Location $location): RedirectResponse
    {
        // Delete all associated photos
        foreach ($location->photos as $photo) {
            $this->photoService->deletePhoto($photo);
        }

        Location::withoutSyncingToSearch(fn () => $location->delete());

        return redirect()->route('admin.locations.index')
            ->with('success', 'Location deleted successfully.');
    }

    /**
     * Build validation rules for the translatable name field.
     *
     * @return array<string, string>
     */
    private function nameValidationRules(): array
    {
        $locales = config('app.supported_locales', ['en', 'sr', 'de']);
        $rules = ['name' => 'required|array'];

        foreach ($locales as $locale) {
            $rules["name.{$locale}"] = $locale === 'en'
                ? 'required|string|max:255'
                : 'nullable|string|max:255';
        }

        return $rules;
    }

    /**
     * Filter out empty locale values from the translations array.
     *
     * @param  array<string, string|null>  $translations
     * @return array<string, string>
     */
    private function buildTranslations(array $translations): array
    {
        return array_filter($translations, fn ($value) => ! empty($value));
    }

    /**
     * Search locations for select2 dropdown.
     */
    public function search(Request $request): \Illuminate\Http\JsonResponse
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
            'results' => $locations->map(fn ($location) => [
                'id' => $location->id,
                'text' => $location->name,
            ]),
            'pagination' => [
                'more' => $locations->hasMorePages(),
            ],
        ]);
    }
}
