<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Amenity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AmenityController extends Controller
{
    private const CATEGORIES = [
        'essential', 'kitchen', 'bedroom', 'bathroom',
        'heating-cooling', 'family-children', 'pets', 'outdoor',
        'parking-facilities', 'safety', 'entertainment', 'office',
        'location', 'luxury', 'services',
    ];

    public function index(): View
    {
        $amenities = Amenity::withoutGlobalScopes()
            ->orderBy('category')
            ->orderBy('sort_order')
            ->orderBy('slug')
            ->get();

        $categories = self::CATEGORIES;

        return view('super-admin.amenities.index', compact('amenities', 'categories'));
    }

    public function create(): View
    {
        $categories = self::CATEGORIES;
        $locales = config('constants.supported_locales');

        return view('super-admin.amenities.create', compact('categories', 'locales'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'slug' => 'required|string|max:100|unique:amenities,slug|regex:/^[a-z0-9_-]+$/',
            'icon' => 'nullable|string|max:100',
            'category' => 'required|string|in:'.implode(',', self::CATEGORIES),
            'is_feeable' => 'boolean',
            'is_highlighted' => 'boolean',
            'is_optional' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
            'name' => 'required|array',
            'name.*' => 'nullable|string|max:255',
        ]);

        $amenity = new Amenity;
        $amenity->slug = $validated['slug'];
        $amenity->icon = $validated['icon'] ?? null;
        $amenity->category = $validated['category'];
        $amenity->is_feeable = $request->boolean('is_feeable');
        $amenity->is_highlighted = $request->boolean('is_highlighted');
        $amenity->is_optional = $request->boolean('is_optional', true);
        $amenity->is_active = $request->boolean('is_active');
        $amenity->sort_order = $validated['sort_order'] ?? 0;

        $locales = config('constants.supported_locales');
        foreach ($locales as $locale) {
            $code = $locale['code'];
            $amenity->setTranslation('name', $code, $validated['name'][$code] ?? '');
        }

        $amenity->save();

        return redirect()->route('admin.amenities.index')
            ->with('success', "Amenity \"{$amenity->slug}\" created successfully.");
    }

    public function edit(Amenity $amenity): View
    {
        $categories = self::CATEGORIES;
        $locales = config('constants.supported_locales');

        return view('super-admin.amenities.edit', compact('amenity', 'categories', 'locales'));
    }

    public function update(Request $request, Amenity $amenity): RedirectResponse
    {
        $validated = $request->validate([
            'slug' => 'required|string|max:100|unique:amenities,slug,'.$amenity->id.'|regex:/^[a-z0-9_-]+$/',
            'icon' => 'nullable|string|max:100',
            'category' => 'required|string|in:'.implode(',', self::CATEGORIES),
            'is_feeable' => 'boolean',
            'is_highlighted' => 'boolean',
            'is_optional' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
            'name' => 'required|array',
            'name.*' => 'nullable|string|max:255',
        ]);

        $amenity->slug = $validated['slug'];
        $amenity->icon = $validated['icon'] ?? null;
        $amenity->category = $validated['category'];
        $amenity->is_feeable = $request->boolean('is_feeable');
        $amenity->is_highlighted = $request->boolean('is_highlighted');
        $amenity->is_optional = $request->boolean('is_optional', true);
        $amenity->is_active = $request->boolean('is_active');
        $amenity->sort_order = $validated['sort_order'] ?? 0;

        $locales = config('constants.supported_locales');
        foreach ($locales as $locale) {
            $code = $locale['code'];
            $amenity->setTranslation('name', $code, $validated['name'][$code] ?? '');
        }

        $amenity->save();

        return redirect()->route('admin.amenities.index')
            ->with('success', "Amenity \"{$amenity->slug}\" updated successfully.");
    }

    public function toggle(Amenity $amenity): RedirectResponse
    {
        $amenity->update(['is_active' => ! $amenity->is_active]);

        $status = $amenity->is_active ? 'activated' : 'deactivated';

        return redirect()->route('admin.amenities.index')
            ->with('success', "Amenity \"{$amenity->slug}\" {$status}.");
    }

    public function destroy(Amenity $amenity): RedirectResponse
    {
        $amenity->delete();

        return redirect()->route('admin.amenities.index')
            ->with('success', "Amenity \"{$amenity->slug}\" deleted.");
    }
}
