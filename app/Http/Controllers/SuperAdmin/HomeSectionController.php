<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Enums\HomeSection\SectionType;
use App\Models\HomeSection;
use App\Models\HomeSectionLocation;
use App\Models\Location;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeSectionController
{
    public function index(): View
    {
        $sections = HomeSection::with(['sectionLocations.location'])
            ->orderBy('sort_order')
            ->get();

        return view('super-admin.home-sections.index', compact('sections'));
    }

    public function create(): View
    {
        $sectionTypes = collect(SectionType::cases())->mapWithKeys(
            fn (SectionType $type) => [$type->value => $type->label()]
        )->toArray();

        $countries = $this->countryOptions();

        return view('super-admin.home-sections.create', compact('sectionTypes', 'countries'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate(array_merge(
            $this->titleValidationRules(),
            [
                'type' => 'required|in:'.implode(',', array_column(SectionType::cases(), 'value')),
                'sort_order' => 'required|integer|min:0',
                'is_active' => 'boolean',
                'country_codes' => 'nullable|array',
                'country_codes.*' => 'string|size:2',
            ]
        ));

        HomeSection::withoutAuthorization(function () use ($validated, $request) {
            HomeSection::create([
                'title' => $this->buildTranslations($validated['title']),
                'type' => $validated['type'],
                'sort_order' => $validated['sort_order'],
                'is_active' => $request->boolean('is_active'),
                'country_codes' => ! empty($validated['country_codes']) ? array_map('strtoupper', $validated['country_codes']) : null,
            ]);
        });

        return redirect()->route('admin.home-sections.index')
            ->with('success', 'Home section created successfully.');
    }

    public function edit(HomeSection $homeSection): View
    {
        $sectionTypes = collect(SectionType::cases())->mapWithKeys(
            fn (SectionType $type) => [$type->value => $type->label()]
        )->toArray();

        $countries = $this->countryOptions();

        $homeSection->load(['sectionLocations.location.primaryPhoto']);

        return view('super-admin.home-sections.edit', compact('homeSection', 'sectionTypes', 'countries'));
    }

    public function update(Request $request, HomeSection $homeSection): RedirectResponse
    {
        $validated = $request->validate(array_merge(
            $this->titleValidationRules(),
            [
                'type' => 'required|in:'.implode(',', array_column(SectionType::cases(), 'value')),
                'sort_order' => 'required|integer|min:0',
                'is_active' => 'boolean',
                'country_codes' => 'nullable|array',
                'country_codes.*' => 'string|size:2',
            ]
        ));

        HomeSection::withoutAuthorization(function () use ($homeSection, $validated, $request) {
            $homeSection->update([
                'title' => $this->buildTranslations($validated['title']),
                'type' => $validated['type'],
                'sort_order' => $validated['sort_order'],
                'is_active' => $request->boolean('is_active'),
                'country_codes' => ! empty($validated['country_codes']) ? array_map('strtoupper', $validated['country_codes']) : null,
            ]);
        });

        return redirect()->route('admin.home-sections.index')
            ->with('success', 'Home section updated successfully.');
    }

    public function destroy(HomeSection $homeSection): RedirectResponse
    {
        HomeSection::withoutAuthorization(fn () => $homeSection->delete());

        return redirect()->route('admin.home-sections.index')
            ->with('success', 'Home section deleted successfully.');
    }

    /**
     * Add a location to a home section.
     */
    public function addLocation(Request $request, HomeSection $homeSection): RedirectResponse
    {
        $validated = $request->validate([
            'location_id' => 'required|exists:locations,id',
        ]);

        $exists = $homeSection->sectionLocations()
            ->where('location_id', $validated['location_id'])
            ->exists();

        if (! $exists) {
            $maxOrder = $homeSection->sectionLocations()->max('sort_order') ?? -1;

            HomeSectionLocation::withoutAuthorization(function () use ($homeSection, $validated, $maxOrder) {
                HomeSectionLocation::create([
                    'home_section_id' => $homeSection->id,
                    'location_id' => $validated['location_id'],
                    'sort_order' => $maxOrder + 1,
                ]);
            });
        }

        return redirect()->route('admin.home-sections.edit', $homeSection)
            ->with('success', 'Location added successfully.');
    }

    /**
     * Remove a location from a home section.
     */
    public function removeLocation(HomeSection $homeSection, HomeSectionLocation $sectionLocation): RedirectResponse
    {
        HomeSectionLocation::withoutAuthorization(fn () => $sectionLocation->delete());

        return redirect()->route('admin.home-sections.edit', $homeSection)
            ->with('success', 'Location removed successfully.');
    }

    /**
     * Search locations for Select2 AJAX.
     */
    public function searchLocations(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'search' => 'nullable|string|max:255',
            'page' => 'nullable|integer|min:1',
        ]);

        $search = $validated['search'] ?? null;
        $page = $validated['page'] ?? 1;

        $locations = Location::query()
            ->when($search, fn ($q) => $q->where('name', 'ILIKE', "%{$search}%"))
            ->select('id', 'name')
            ->paginate(10, ['*'], 'page', $page);

        return response()->json([
            'results' => $locations->map(fn (Location $location) => [
                'id' => $location->id,
                'text' => $location->getTranslation('name', 'en'),
            ]),
            'pagination' => ['more' => $locations->hasMorePages()],
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function titleValidationRules(): array
    {
        $locales = config('app.supported_locales', ['en', 'sr', 'de']);
        $rules = ['title' => 'required|array'];

        foreach ($locales as $locale) {
            $rules["title.{$locale}"] = $locale === 'en'
                ? 'required|string|max:255'
                : 'nullable|string|max:255';
        }

        return $rules;
    }

    /**
     * @param  array<string, string|null>  $translations
     * @return array<string, string>
     */
    private function buildTranslations(array $translations): array
    {
        return array_filter($translations, fn ($value) => ! empty($value));
    }

    /**
     * @return array<string, string>
     */
    private function countryOptions(): array
    {
        return \App\Models\Country::query()
            ->orderBy('name->en')
            ->get(['iso_code_2', 'name'])
            ->mapWithKeys(fn (\App\Models\Country $country) => [
                $country->iso_code_2 => $country->getTranslation('name', 'en'),
            ])
            ->toArray();
    }
}
