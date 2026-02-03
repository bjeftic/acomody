<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Laravel\Scout\Builder;

class SearchService
{
    /**
     * Collection to Model mapping
     */
    protected array $collections = [
        'locations' => \App\Models\Location::class,
        'accommodations' => \App\Models\Accommodation::class,
    ];

    /**
     * Default search configuration per collection
     *
     * ⚠️ Important: query_by must contain ONLY TEXTUAL fields!
     * ❌ Do NOT include: ID fields, enum fields, integer fields
     * ✅ Include: name, description, address, title, etc.
     */
    protected array $defaultSearchConfig = [
        'locations' => [
            'query_by' => 'name,name_en,name_sr,name_de',
            'infix' => 'fallback',
            'num_typos' => 2,
            'prefix' => true,
            'min_len_1typo' => 3,
            'min_len_2typo' => 5,
        ],
        'listings' => [
            // 'query_by' => 'title,description,address',
            'num_typos' => 2,
            'prefix' => true,
        ],
    ];

    /**
     * Search single collection with full Typesense parameters
     */
    public function searchCollection(string $collection, string $query, array $options = []): array
    {
        $modelClass = $this->collections[$collection]
            ?? throw new \InvalidArgumentException("Unknown collection: {$collection}");

        // Merge with default configuration for this collection
        $config = array_merge(
            $this->defaultSearchConfig[$collection] ?? [],
            $options
        );

        // CRITICAL: query_by MUST be set for Typesense
        if (empty($config['query_by'])) {
            // Try to get from model
            if (method_exists($modelClass, 'typesenseQueryBy')) {
                $model = new $modelClass();
                $queryBy = $model->typesenseQueryBy();
                $config['query_by'] = is_array($queryBy) ? implode(',', $queryBy) : $queryBy;
            }

            // Still empty? Throw error
            if (empty($config['query_by'])) {
                throw new \InvalidArgumentException(
                    "query_by parameter is required for Typesense search on collection: {$collection}"
                );
            }
        }

        Log::info("SearchService: Building query", [
            'collection' => $collection,
            'model' => $modelClass,
            'query' => $query,
            'config' => $config,
        ]);

        // Build search query
        $builder = $modelClass::search($query);

        // Apply Typesense-specific parameters
        $this->applySearchParameters($builder, $config);

        // Execute search
        try {
            // Get raw results to access highlights and facets
            return $builder->raw();
        } catch (\Exception $e) {
            Log::error("Search execution failed", [
                'collection' => $collection,
                'error' => $e->getMessage(),
                'config' => $config,
            ]);
            throw $e;
        }
    }

    /**
     * Apply Typesense search parameters to builder
     */
    protected function applySearchParameters(Builder $builder, array $options): void
    {
        // Convert all options to the format Scout expects
        $scoutOptions = [];

        foreach ($options as $key => $value) {
            // Skip null values
            if ($value === null) {
                continue;
            }

            // Handle special cases
            switch ($key) {
                case 'filters':
                    // Legacy support - convert to filter_by
                    if (!isset($options['filter_by'])) {
                        $scoutOptions['filter_by'] = $this->buildFilterBy($value);
                    }
                    break;

                case 'limit':
                    // Convert limit to per_page if per_page not set
                    if (!isset($options['per_page'])) {
                        $scoutOptions['per_page'] = $value;
                    }
                    break;

                default:
                    $scoutOptions[$key] = $value;
            }
        }

        // Log what we're sending to Scout
        Log::info("SearchService: Applying Scout options", [
            'options' => $scoutOptions,
        ]);

        // Apply all options at once
        if (!empty($scoutOptions)) {
            $builder->options($scoutOptions);
        }
    }

    /**
     * Build filter_by string from filters array
     */
    protected function buildFilterBy(array $filters): string
    {
        $conditions = [];

        foreach ($filters as $field => $value) {
            if (is_array($value)) {
                $values = array_map(fn($v) => $this->escapeFilterValue($v), $value);
                $conditions[] = "{$field}:[" . implode(', ', $values) . "]";
            } else {
                $conditions[] = "{$field}:" . $this->escapeFilterValue($value);
            }
        }

        return implode(' && ', $conditions);
    }

    /**
     * Escape filter value for Typesense
     */
    protected function escapeFilterValue($value): string
    {
        if (is_string($value) && (str_contains($value, ' ') || str_contains($value, ','))) {
            return '`' . $value . '`';
        }

        return (string) $value;
    }

    /**
     * Advanced search with geo-filtering
     */
    public function geoSearch(
        string $collection,
        string $query,
        float $lat,
        float $lng,
        string $radius = '5 km',
        array $options = []
    ): array {
        $geoField = $options['geo_field'] ?? 'location';

        $options['filter_by'] = ($options['filter_by'] ?? '') .
            ($options['filter_by'] ? ' && ' : '') .
            "{$geoField}:({$lat}, {$lng}, {$radius})";

        $options['sort_by'] = $options['sort_by'] ?? "{$geoField}({$lat}, {$lng}):asc";

        return $this->searchCollection($collection, $query, $options);
    }

    /**
     * Faceted search
     */
    public function facetedSearch(
        string $collection,
        string $query,
        array $facetFields,
        array $options = []
    ): array {
        $options['facet_by'] = implode(',', $facetFields);
        $options['max_facet_values'] = $options['max_facet_values'] ?? 100;

        return $this->searchCollection($collection, $query, $options);
    }
}
