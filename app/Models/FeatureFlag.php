<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeatureFlag extends Model
{
    protected $fillable = [
        'key',
        'name',
        'description',
        'is_enabled',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::updated(function (FeatureFlag $featureFlag) {
            if (
                $featureFlag->key === 'cold_start'
                && $featureFlag->wasChanged('is_enabled')
                && ! $featureFlag->is_enabled
            ) {
                HostSubscription::where('is_early_host', true)
                    ->whereNull('early_host_expires_at')
                    ->update(['early_host_expires_at' => now()->addMonths(6)]);
            }
        });
    }
}
