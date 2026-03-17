<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\FeatureFlag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FeatureFlagController extends Controller
{
    public function index(): View
    {
        $featureFlags = FeatureFlag::latest('id')->get();

        return view('super-admin.feature-flags.index', compact('featureFlags'));
    }

    public function create(): View
    {
        return view('super-admin.feature-flags.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'key' => 'required|string|max:100|unique:feature_flags,key|regex:/^[a-z][a-zA-Z0-9_]*$/',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'is_enabled' => 'boolean',
        ]);

        FeatureFlag::create([
            'key' => $validated['key'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_enabled' => $request->boolean('is_enabled'),
        ]);

        return redirect()->route('admin.feature-flags.index')
            ->with('success', 'Feature flag created successfully.');
    }

    public function edit(FeatureFlag $featureFlag): View
    {
        return view('super-admin.feature-flags.edit', compact('featureFlag'));
    }

    public function update(Request $request, FeatureFlag $featureFlag): RedirectResponse
    {
        $validated = $request->validate([
            'key' => 'required|string|max:100|unique:feature_flags,key,'.$featureFlag->id.'|regex:/^[a-z][a-zA-Z0-9_]*$/',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'is_enabled' => 'boolean',
        ]);

        $featureFlag->update([
            'key' => $validated['key'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_enabled' => $request->boolean('is_enabled'),
        ]);

        return redirect()->route('admin.feature-flags.index')
            ->with('success', 'Feature flag updated successfully.');
    }

    public function toggle(FeatureFlag $featureFlag): RedirectResponse
    {
        $featureFlag->update(['is_enabled' => ! $featureFlag->is_enabled]);

        $status = $featureFlag->is_enabled ? 'enabled' : 'disabled';

        return redirect()->route('admin.feature-flags.index')
            ->with('success', "Feature flag \"{$featureFlag->name}\" {$status}.");
    }

    public function destroy(FeatureFlag $featureFlag): RedirectResponse
    {
        $featureFlag->delete();

        return redirect()->route('admin.feature-flags.index')
            ->with('success', 'Feature flag deleted successfully.');
    }
}
