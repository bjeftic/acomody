<?php

namespace App\Services;

use Illuminate\Console\OutputStyle;
use Typesense\Client;

class TypesenseCollectionService
{
    protected Client $client;
    protected ?OutputStyle $output;

    public function __construct(?OutputStyle $output = null)
    {
        $config = [
            'api_key' => config('scout.typesense.client-settings.api_key'),
            'nodes' => config('scout.typesense.client-settings.nodes'),
            'connection_timeout_seconds' => config('scout.typesense.client-settings.connection_timeout_seconds', 2),
        ];

        $this->client = new Client($config);
        $this->output = $output;
    }

    /**
     * Get list of indexable models
     */
    public static function getIndexableModels(): array
    {
        return [
            \App\Models\Location::class => 'locations',
            \App\Models\Accommodation::class => 'accommodations',
        ];
    }

    /**
     * Rebuild collections for given models
     *
     * @param array $modelClasses Array of model class names
     * @param bool $verbose Whether to output detailed information
     */
    public function rebuildCollections(array $modelClasses, bool $verbose = true): void
    {
        if ($verbose && $this->output) {
            $this->output->writeln("Rebuilding Typesense collections...");
        }

        foreach ($modelClasses as $modelClass) {
            if (is_null($modelClass)) {
                continue;
            }

            $this->rebuildCollection($modelClass, $verbose);
        }
    }

    /**
     * Rebuild a single collection
     */
    public function rebuildCollection(string $modelClass, bool $verbose = true): void
    {
        $model = new $modelClass;
        $indexName = $model->searchableAs();

        try {
            // Delete existing collection
            $this->deleteCollection($indexName, $verbose);

            // Get schema from model
            $schema = $this->getModelSchema($model, $modelClass, $indexName, $verbose);

            // Validate schema
            $this->validateSchema($schema, $indexName, $verbose);

            // Create new collection
            $this->client->collections->create($schema);

            if ($verbose && $this->output) {
                $this->output->writeln("<info>✓ Collection '{$indexName}' created successfully</info>");
            }
        } catch (\Exception $e) {
            if ($this->output) {
                $this->output->writeln("<error>Error with collection '{$indexName}': {$e->getMessage()}</error>");
            }
            throw $e;
        }
    }

    /**
     * Delete a collection if it exists
     */
    public function deleteCollection(string $indexName, bool $verbose = true): void
    {
        try {
            $this->client->collections[$indexName]->delete();
            if ($verbose && $this->output) {
                $this->output->writeln("<comment>Deleted old collection '{$indexName}'</comment>");
            }
        } catch (\Typesense\Exceptions\ObjectNotFound $e) {
            if ($verbose && $this->output) {
                $this->output->writeln("<info>Collection '{$indexName}' doesn't exist yet</info>");
            }
        }
    }

    /**
     * Get schema from model
     */
    protected function getModelSchema($model, string $modelClass, string $indexName, bool $verbose = true): array
    {
        if (method_exists($model, 'getCollectionSchema')) {
            if ($verbose && $this->output) {
                $this->output->writeln("   Using getCollectionSchema() from model");
            }
            return $model->getCollectionSchema();
        }

        if (method_exists($model, 'getTypesenseSchema')) {
            if ($verbose && $this->output) {
                $this->output->writeln("   Using getTypesenseSchema() from model");
            }
            return $model->getTypesenseSchema();
        }

        if ($verbose && $this->output) {
            $this->output->writeln("<comment>   No schema method found, generating from model data</comment>");
        }
        return $this->generateSchemaFromModel($modelClass, $indexName);
    }

    /**
     * Validate schema structure
     */
    protected function validateSchema(array $schema, string $indexName, bool $verbose = true): void
    {
        if (!isset($schema['fields']) || !is_array($schema['fields'])) {
            throw new \Exception("Invalid schema structure for '{$indexName}': 'fields' must be an array");
        }

        if ($verbose && $this->output) {
            $this->output->writeln("Creating collection '{$indexName}' with fields:");
        }

        foreach ($schema['fields'] as $index => $field) {
            if (!is_array($field)) {
                throw new \Exception(
                    "Invalid field at index {$index} in '{$indexName}' schema: Expected array, got " . gettype($field)
                );
            }

            if (!isset($field['name']) || !isset($field['type'])) {
                throw new \Exception(
                    "Field at index {$index} missing 'name' or 'type': " . json_encode($field)
                );
            }

            if ($verbose && $this->output) {
                $optional = isset($field['optional']) && $field['optional'] ? ' (optional)' : '';
                $this->output->writeln("   - {$field['name']} ({$field['type']}){$optional}");
            }
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
            if ($this->output) {
                $this->output->writeln("<comment>No records found for {$modelClass}, creating minimal schema</comment>");
            }
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
