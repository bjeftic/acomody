<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Models\Location;
use App\Models\Listing;

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
            Listing::class => 'listings',
        ];
        $indexableModelClasses = array_keys($indexableModels);

        /** @var string|null */
        $model = $this->option('model');
        if (!is_null($model)) {
            $models = [
                Arr::first($indexableModelClasses, fn ($m) => Str::endsWith($m, $model))
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

        // Ensure collections exist
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
     */
    protected function ensureCollectionsExist(array $models): void
    {
        $this->info("Checking Typesense collections...");

        try {
            // Create Typesense client manually
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
                    // Check if collection exists
                    $typesense->collections[$indexName]->retrieve();
                    $this->info("✓ Collection '{$indexName}' already exists");
                } catch (\Typesense\Exceptions\ObjectNotFound $e) {
                    // Collection doesn't exist, create it
                    $this->warn("Collection '{$indexName}' not found, creating...");

                    // Get schema from model if it has the method
                    if (method_exists($model, 'getCollectionSchema')) {
                        $schema = $model->getCollectionSchema();
                    } elseif (method_exists($model, 'getTypesenseSchema')) {
                        $schema = $model->getTypesenseSchema();
                    } else {
                        // Create basic schema from first record
                        $schema = $this->generateSchemaFromModel($modelClass, $indexName);
                    }

                    $typesense->collections->create($schema);
                    $this->info("✓ Collection '{$indexName}' created successfully");
                } catch (\Exception $e) {
                    $this->error("Error with collection '{$indexName}': " . $e->getMessage());
                }
            }
        } catch (\Exception $e) {
            $this->error("Error ensuring collections exist: " . $e->getMessage());
            $this->error($e->getTraceAsString());
            throw $e; // Stop execution if we can't create collections
        }
    }

    /**
     * Generate schema from a model by examining a sample record
     */
    protected function generateSchemaFromModel(string $modelClass, string $indexName): array
    {
        // Try to get a sample record to infer schema
        $sample = $modelClass::first();

        if ($sample) {
            $searchableData = $sample->toSearchableArray();
        } else {
            // No records exist, create minimal schema
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
            return 'string'; // default for null
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
            // Check type of first element
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

        return 'string'; // default
    }
}
