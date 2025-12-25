<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Laravel\Scout\Builder;

class SearchService
{
    /**
     * Collection to Model mapping
     */
    protected array $collections = [
        'locations' => \App\Models\Location::class,
        'listings' => \App\Models\Listing::class,
    ];

    /**
     * Default search configuration per collection
     *
     * ⚠️ ВАЖНО: query_by mora sadržati SAMO TEKSTUALNA polja!
     * ❌ NE stavljaj: ID polja, enum polja, integer polja
     * ✅ Stavljaj: name, description, address, title, itd.
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
            'query_by' => 'title,description,address',
            'num_typos' => 2,
            'prefix' => true,
        ],
    ];

    /**
     * Multi-search across multiple collections
     */
    public function multiSearch(string $query, array $collections, array $options = []): array
    {
        $results = [];

        foreach ($collections as $collection) {
            try {
                // Get collection-specific options
                $collectionOptions = $options[$collection] ?? $options['global'] ?? [];

                // IMPORTANT: Ensure query_by is always set
                if (empty($collectionOptions['query_by'])) {
                    $defaults = $this->defaultSearchConfig[$collection] ?? [];
                    if (!empty($defaults['query_by'])) {
                        $collectionOptions['query_by'] = $defaults['query_by'];
                    }

                    // Last resort: try to get from model
                    if (empty($collectionOptions['query_by'])) {
                        $modelClass = $this->collections[$collection] ?? null;
                        if ($modelClass && method_exists($modelClass, 'typesenseQueryBy')) {
                            $model = new $modelClass();
                            $queryBy = $model->typesenseQueryBy();
                            if (!empty($queryBy)) {
                                $collectionOptions['query_by'] = is_array($queryBy)
                                    ? implode(',', $queryBy)
                                    : $queryBy;
                            }
                        }
                    }
                }

                Log::info("SearchService: Searching collection", [
                    'collection' => $collection,
                    'query' => $query,
                    'options' => $collectionOptions,
                ]);

                $results[$collection] = $this->searchCollection(
                    $collection,
                    $query,
                    $collectionOptions
                );
            } catch (\Exception $e) {
                Log::error("Search failed: {$collection}", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'query' => $query,
                    'options' => $options[$collection] ?? $options['global'] ?? [],
                ]);

                $results[$collection] = [
                    'success' => false,
                    'error' => $e->getMessage(),
                    'data' => [],
                    'count' => 0,
                    'facets' => [],
                    'debug' => config('app.debug') ? [
                        'exception' => get_class($e),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'options_sent' => $options[$collection] ?? $options['global'] ?? [],
                    ] : null,
                ];
            }
        }

        return [
            'query' => $query,
            'results' => $results,
            'total_results' => array_sum(array_column($results, 'count')),
        ];
    }

    /**
     * Search single collection with full Typesense parameters
     */
    protected function searchCollection(string $collection, string $query, array $options = []): array
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
            $rawResults = $builder->raw();

            // Get model instances
            $results = $builder->get();

            return [
                'success' => true,
                'collection' => $collection,
                'data' => $this->transform($results, $collection, $rawResults),
                'count' => $rawResults['found'] ?? $results->count(),
                'facets' => $this->extractFacets($rawResults),
                'highlights' => $this->extractHighlights($rawResults),
                'search_time_ms' => $rawResults['search_time_ms'] ?? null,
                'query_info' => [
                    'query' => $query,
                    'options' => $config,
                ],
            ];
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
     * Extract facets from raw search results
     */
    protected function extractFacets(?array $rawResults): array
    {
        if (!$rawResults || !isset($rawResults['facet_counts'])) {
            return [];
        }

        $facets = [];
        foreach ($rawResults['facet_counts'] as $facet) {
            $fieldName = $facet['field_name'] ?? null;
            if (!$fieldName) {
                continue;
            }

            $facets[$fieldName] = [
                'field_name' => $fieldName,
                'counts' => array_map(function ($count) {
                    return [
                        'value' => $count['value'],
                        'count' => $count['count'],
                        'highlighted' => $count['highlighted'] ?? $count['value'],
                    ];
                }, $facet['counts'] ?? []),
                'stats' => $facet['stats'] ?? null,
            ];
        }

        return $facets;
    }

    /**
     * Extract highlights from raw search results
     */
    protected function extractHighlights(?array $rawResults): array
    {
        if (!$rawResults || !isset($rawResults['hits'])) {
            return [];
        }

        $highlights = [];
        foreach ($rawResults['hits'] as $hit) {
            $docId = $hit['document']['id'] ?? null;
            if ($docId && isset($hit['highlights'])) {
                $highlights[$docId] = $this->formatHighlights($hit['highlights']);
            }
        }

        return $highlights;
    }

    /**
     * Format highlights into a cleaner structure
     */
    protected function formatHighlights(array $highlights): array
    {
        $formatted = [];

        foreach ($highlights as $highlight) {
            $field = $highlight['field'] ?? null;
            if (!$field) {
                continue;
            }

            $formatted[$field] = [
                'field' => $field,
                'matched_tokens' => $highlight['matched_tokens'] ?? [],
                'snippet' => $highlight['snippet'] ?? null,
                'snippets' => $highlight['snippets'] ?? [],
                'value' => $highlight['value'] ?? null,
                'values' => $highlight['values'] ?? [],
            ];
        }

        return $formatted;
    }

    /**
     * Transform results for frontend
     */
    protected function transform(Collection $results, string $collection, ?array $rawResults = null): array
    {
        // Create a map of highlights by document ID
        $highlightsMap = [];
        if ($rawResults && isset($rawResults['hits'])) {
            foreach ($rawResults['hits'] as $hit) {
                $docId = $hit['document']['id'] ?? null;
                if ($docId) {
                    $highlightsMap[$docId] = $hit['highlights'] ?? [];
                }
            }
        }

        return $results->map(function ($item) use ($collection, $highlightsMap) {
            $itemId = (string) $item->id;

            return [
                'id' => $item->id,
                'type' => $collection,
                'name' => $item->name ?? $item->title ?? $item->email,
                'highlights' => $highlightsMap[$itemId] ?? [],
                ...$this->getExtraFields($item, $collection),
            ];
        })->toArray();
    }

    /**
     * Get extra fields based on collection type
     */
    protected function getExtraFields($item, string $collection): array
    {
        return match ($collection) {
            'locations' => [
                'country' => $item->country?->name,
                'parent' => $item->parent?->name,
                'location_type' => $item->location_type,
            ],
            'listings' => [
                'accommodation_type' => $item->accommodation_type,
                'location_id' => $item->location_id,
            ],
            default => [],
        };
    }

    /**
     * Add new searchable collection
     */
    public function addCollection(string $name, string $modelClass): void
    {
        $this->collections[$name] = $modelClass;
    }

    /**
     * Set default search configuration for a collection
     */
    public function setDefaultSearchConfig(string $collection, array $config): void
    {
        $this->defaultSearchConfig[$collection] = $config;
    }

    /**
     * Get default search configuration for a collection
     */
    public function getDefaultSearchConfig(string $collection): array
    {
        return $this->defaultSearchConfig[$collection] ?? [];
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
