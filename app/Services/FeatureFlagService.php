<?php

namespace App\Services;

use App\Models\FeatureFlag;

class FeatureFlagService
{
    /**
     * Returns only enabled feature flags as an associative array of key => true.
     * Disabled flags are absent from the result to avoid exposing unreleased feature names.
     *
     * @return array<string, true>
     */
    public static function getEnabledFlags(): array
    {
        return FeatureFlag::where('is_enabled', true)
            ->pluck('key')
            ->mapWithKeys(fn ($key) => [$key => true])
            ->all();
    }
}
