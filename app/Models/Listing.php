<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Laravel\Scout\Searchable;

class Listing extends Model
{
    use HasFactory, HasUlids, Searchable;

    protected $fillable = [
        'listable_id',
        'listable_type',
        'user_id',
        'location_id',
        'longitude',
        'latitude',
        'street_address',
        'is_active',
        'approved_by',
        'created_at',
        'updated_at',
    ];

    public function canBeReadBy($user): bool
    {
        return $this->is_active;
    }

    public function canBeCreatedBy($user): bool
    {
        return $user !== null;
    }

    public function canBeUpdatedBy($user): bool
    {
        return $user && $user->id === $this->user_id;
    }

    public function canBeDeletedBy($user): bool
    {
        return $user && $user->id === $this->user_id;
    }

    /**
     * Get the name of the index associated with the model.
     */
    public function searchableAs(): string
    {
        return 'listings';
    }

    /**
     * Determine if the model should be searchable.
     */
    public function shouldBeSearchable(): bool
    {
        return $this->is_active && $this->listable_id !== null;
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        \Log::info('toSearchableArray called', [
            'listing_id' => $this->id,
            'listable_id' => $this->listable_id,
            'listable_type' => $this->listable_type,
            'created_at' => $this->created_at?->timestamp,
        ]);

        $array = [
            'id' => (string) $this->id,
            'listable' => (string) class_basename($this->listable_type),
            'created_at' => (int) $this->created_at->timestamp,
        ];

        // Add location coordinates
        if ($this->latitude !== null && $this->longitude !== null) {
            $array['location'] = [
                (float) $this->latitude,
                (float) $this->longitude
            ];
        }

        \Log::info('toSearchableArray result', [
            'array' => $array,
        ]);

        return $array;
    }

    /**
     * Get the Typesense collection schema.
     *
     * @return array<string, mixed>
     */
    public function getTypesenseSchema(): array
    {
        return [
            'name' => $this->searchableAs(),
            'fields' => [
                [
                    'name' => 'id',
                    'type' => 'string',
                ],
                [
                    'name' => 'listable',
                    'type' => 'string',
                    'facet' => true,
                ],
                [
                    'name' => 'location',
                    'type' => 'geopoint',
                    'optional' => true,
                ],
                [
                    'name' => 'created_at',
                    'type' => 'int64',
                    'optional' => false,
                ],
            ],
            'default_sorting_field' => 'created_at',
        ];
    }

    /**
     * Get the Typesense search parameters
     */
    public function typesenseQueryBy(): array
    {
        return [
            'listable',
        ];
    }

    public function listable()
    {
        return $this->morphTo();
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // public function reviews()
    // {
    //     return $this->morphMany(Review::class, 'reviewable');
    // }
}
