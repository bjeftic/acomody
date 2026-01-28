<?php

namespace App\Console\Commands;

use App\Models\Accommodation;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Models\Location;

class ScoutReindex extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = '
    scout:reindex
    {hours?}
    {--model= : Model class to reindex}
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Index all indexable entities which entered the DB in the last X hours.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $indexableModels = [
            Location::class => 'locations',
            Accommodation::class => 'accommodations',
        ];
        $indexableModelClasses = array_keys($indexableModels);

        /** @var string|null */
        $model = $this->option('model');
        if (!is_null($model)) {
            $models = [
                Arr::first($indexableModelClasses, fn($m) => Str::endsWith($m, $model))
            ];
        } else {
            $models = &$indexableModelClasses;
        }

        /** @var string|null */
        $hours = $this->argument('hours');
        $this->info("Reindexing entities");
        $startingDate = !is_null($hours) ? now()->subHours(intval($hours)) : null;

        // we don't want to overflow the queue with this one
        config(['scout.queue' => false]);

        // Ensure collections exist (with rebuild)
        $this->ensureCollectionsExist($models);

        foreach ($models as $modelClass) {
            if (is_null($modelClass)) {
                continue;
            }
            $this->info("Reindexing model: {$modelClass}");
            $query = $modelClass::query();
            if (!is_null($startingDate)) {
                $query->where('created_at', '>=', $startingDate);
            }
            $count = $query->count();
            $this->info("Found {$count} records to reindex");

            if ($count > 0) {
                $bar = $this->output->createProgressBar($count);
                $query->chunk(100, function ($items) use ($bar) {
                    foreach ($items as $item) {
                        $item->searchable();
                        $bar->advance();
                    }
                });
                $bar->finish();
                $this->info("");
            }

            $this->info("Done with model: {$modelClass}");
        }

        $this->info("");
        $this->info("Done!");
    }

    /**
     * Ensure Typesense collections exist for all models
     * ALWAYS rebuilds collections to ensure latest schema
     */
    protected function ensureCollectionsExist(array $models): void
    {
        $this->info("Rebuilding Typesense collections...");

        try {
            $config = [
                'api_key' => config('scout.typesense.client-settings.api_key'),
                'nodes' => config('scout.typesense.client-settings.nodes'),
                'connection_timeout_seconds' => config('scout.typesense.client-settings.connection_timeout_seconds', 2),
            ];

            $typesense = new \Typesense\Client($config);

            foreach ($models as $modelClass) {
                if (is_null($modelClass)) {
                    continue;
                }

                $model = new $modelClass;
                $indexName = $model->searchableAs();

                try {
                    // Try to delete existing collection
                    try {
                        $typesense->collections[$indexName]->delete();
                        $this->warn("Deleted old collection '{$indexName}'");
                    } catch (\Typesense\Exceptions\ObjectNotFound $e) {
                        $this->info("Collection '{$indexName}' doesn't exist yet");
                    }

                    // Get schema from model
                    $schema = $this->getModelSchema($model, $modelClass, $indexName);

                    // Debug: Show schema fields
                    $this->info("Creating collection '{$indexName}' with fields:");

                    // Validate schema structure
                    if (!isset($schema['fields']) || !is_array($schema['fields'])) {
                        $this->error("Invalid schema structure for '{$indexName}': 'fields' must be an array");
                        throw new \Exception("Invalid schema structure");
                    }

                    foreach ($schema['fields'] as $index => $field) {
                        // Check if field is actually an array
                        if (!is_array($field)) {
                            $this->error("Invalid field at index {$index} in '{$indexName}' schema:");
                            $this->error("Expected array, got: " . gettype($field));
                            $this->error("Value: " . (is_string($field) ? $field : json_encode($field)));
                            throw new \Exception("Invalid field structure at index {$index}");
                        }

                        // Check for required keys
                        if (!isset($field['name']) || !isset($field['type'])) {
                            $this->error("Field at index {$index} missing 'name' or 'type':");
                            $this->error(json_encode($field, JSON_PRETTY_PRINT));
                            throw new \Exception("Invalid field structure at index {$index}");
                        }

                        $optional = isset($field['optional']) && $field['optional'] ? ' (optional)' : '';
                        $this->info("   - {$field['name']} ({$field['type']}){$optional}");
                    }

                    // Create new collection
                    $typesense->collections->create($schema);
                    $this->info("Collection '{$indexName}' created successfully");
                } catch (\Exception $e) {
                    $this->error("Error with collection '{$indexName}': " . $e->getMessage());
                    throw $e; // Stop execution on error
                }
            }
        } catch (\Exception $e) {
            $this->error("Error ensuring collections exist: " . $e->getMessage());
            $this->error($e->getTraceAsString());
            throw $e;
        }
    }

    /**
     * Get schema from model
     */
    protected function getModelSchema($model, string $modelClass, string $indexName): array
    {
        if (method_exists($model, 'getCollectionSchema')) {
            $this->info("   Using getCollectionSchema() from model");
            return $model->getCollectionSchema();
        } elseif (method_exists($model, 'getTypesenseSchema')) {
            $this->info("   Using getTypesenseSchema() from model");
            return $model->getTypesenseSchema();
        } else {
            $this->warn("   No schema method found, generating from model data");
            return $this->generateSchemaFromModel($modelClass, $indexName);
        }
    }

    /**
     * Generate schema from a model by examining a sample record
     */
    protected function generateSchemaFromModel(string $modelClass, string $indexName): array
    {
        $sample = $modelClass::first();

        if ($sample) {
            $searchableData = $sample->toSearchableArray();
        } else {
            $this->warn("No records found for {$modelClass}, creating minimal schema");
            return [
                'name' => $indexName,
                'fields' => [
                    ['name' => 'id', 'type' => 'string'],
                ],
                'default_sorting_field' => 'id',
            ];
        }

        $fields = [];

        foreach ($searchableData as $key => $value) {
            $fieldType = $this->inferTypesenseType($value);

            $field = [
                'name' => $key,
                'type' => $fieldType,
            ];

            // Make all fields optional except id
            if ($key !== 'id') {
                $field['optional'] = true;
            }

            // Enable faceting for certain types
            if (in_array($fieldType, ['string', 'int32', 'int64', 'bool'])) {
                $field['facet'] = true;
            }

            $fields[] = $field;
        }

        // Ensure id field exists
        if (!collect($fields)->contains('name', 'id')) {
            array_unshift($fields, [
                'name' => 'id',
                'type' => 'string',
            ]);
        }

        // Find a good sorting field
        $sortingField = 'id';
        if (collect($fields)->contains('name', 'created_at')) {
            $sortingField = 'created_at';
        }

        return [
            'name' => $indexName,
            'fields' => $fields,
            'default_sorting_field' => $sortingField,
        ];
    }

    /**
     * Infer Typesense field type from PHP value
     */
    protected function inferTypesenseType($value): string
    {
        if (is_null($value)) {
            return 'string';
        }

        if (is_bool($value)) {
            return 'bool';
        }

        if (is_int($value)) {
            return 'int64';
        }

        if (is_float($value)) {
            return 'float';
        }

        if (is_array($value)) {
            if (empty($value)) {
                return 'string[]';
            }
            $firstElement = reset($value);
            if (is_string($firstElement)) {
                return 'string[]';
            } elseif (is_int($firstElement)) {
                return 'int64[]';
            } elseif (is_bool($firstElement)) {
                return 'bool[]';
            }
            return 'string[]';
        }

        return 'string';
    }
}
