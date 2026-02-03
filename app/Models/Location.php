<?php

namespace App\Models;

use App\Enums\Location\LocationType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Scout\Searchable;
use Spatie\Translatable\HasTranslations;

class Location extends Model
{
    use HasFactory, Searchable, HasTranslations;

    protected $fillable = ['name', 'location_type', 'country_id', 'parent_id', 'latitude', 'longitude', 'user_id'];

    public $translatable = ['name'];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    public function canBeReadBy($user): bool
    {
        return true;
    }

    public function canBeCreatedBy($user): bool
    {
        return $user !== null;
    }

    public function canBeUpdatedBy($user): bool
    {
        return $user !== null;
    }

    public function canBeDeletedBy($user): bool
    {
        return $user !== null;
    }

    /**
     * Get the name of the index associated with the model.
     */
    public function searchableAs(): string
    {
        return 'locations';
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        $array = [
            'id' => (string) $this->id,
            'type' => 'location',
            'country_id' => (string) $this->country_id,
            'location_type' => $this->location_type,
            'parent_id' => $this->parent_id ? (string) $this->parent_id : null,
            'created_at' => $this->created_at->timestamp,
        ];

        // Get all translations
        $translations = $this->getTranslations('name');

        // Add translations as separate fields
        foreach ($translations as $locale => $value) {
            $array["name_{$locale}"] = $value ?? '';
        }

        // Always include a default 'name' field for searching
        $array['name'] = $this->getTranslation('name', app()->getLocale())
                      ?? $this->getTranslation('name', config('app.fallback_locale'))
                      ?? '';

        // Add location coordinates ONLY if both latitude and longitude exist
        // This prevents Typesense errors for documents without coordinates
        if ($this->latitude !== null && $this->longitude !== null) {
            $array['location'] = [
                (float) $this->latitude,
                (float) $this->longitude
            ];
        }

        return $array;
    }

     /**
     * Get the Typesense search parameters
     * Define which fields should be searched by default
     */
    public function typesenseQueryBy(): array
    {
        $locales = config('app.supported_locales', ['en', 'sr', 'de']);
        $queryFields = ['name'];

        // Add all locale-specific name fields
        foreach ($locales as $locale) {
            $queryFields[] = "name_{$locale}";
        }

        return $queryFields;
    }

    /**
     * Get the Typesense collection schema.
     * This defines the structure and field types for Typesense.
     *
     * @return array<string, mixed>
     */
    public function getCollectionSchema(): array
    {
        // Get supported locales from config
        $locales = config('app.supported_locales', ['en', 'sr', 'de']);

        $fields = [
            [
                'name' => 'id',
                'type' => 'string',
            ],
            [
                'name' => 'name',
                'type' => 'string',
                'facet' => false,
                'optional' => false,
            ],
            [
                'name' => 'type',
                'type' => 'string',
                'facet' => true,
            ],
            [
                'name' => 'country_id',
                'type' => 'string',
                'facet' => true,
            ],
            [
                'name' => 'location_type',
                'type' => 'string',
                'facet' => true,
                'optional' => true,
            ],
            [
                'name' => 'parent_id',
                'type' => 'string',
                'facet' => true,
                'optional' => true,
            ],
            [
                'name' => 'location',
                'type' => 'geopoint',
                'optional' => true,
            ],
            [
                'name' => 'created_at',
                'type' => 'int64',
            ],
        ];

        // Add a field for each locale
        foreach ($locales as $locale) {
            $fields[] = [
                'name' => "name_{$locale}",
                'type' => 'string',
                'facet' => false,
                'infix' => true,
                'optional' => true,
                'locale' => $locale,
            ];
        }

        return [
            'name' => $this->searchableAs(),
            'fields' => $fields,
            'default_sorting_field' => 'created_at',
        ];
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function parent()
    {
        return $this->belongsTo(Location::class, 'parent_id');
    }

    public function childrens()
    {
        return $this->hasMany(Location::class, 'parent_id');
    }

    public function locationType(): array
    {
        return LocationType::fromId($this->id)?->toArray() ?? [];
    }
}
