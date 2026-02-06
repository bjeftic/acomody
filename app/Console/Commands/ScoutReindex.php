<?php

namespace App\Console\Commands;

use App\Services\TypesenseCollectionService;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ScoutReindex extends Command
{
    protected $signature = '
    scout:reindex
    {hours?}
    {--model= : Model class to reindex}
    ';

    protected $description = 'Index all indexable entities which entered the DB in the last X hours.';

    public function handle()
    {
        $indexableModels = TypesenseCollectionService::getIndexableModels();
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
        $typesenseService = new TypesenseCollectionService($this->output);
        $typesenseService->rebuildCollections($models);

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
}
