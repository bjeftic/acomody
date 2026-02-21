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
        {hours? : Only reindex records created in the last X hours}
        {--model= : Model class to reindex (e.g. Accommodation)}
    ';

    protected $description = 'Index all indexable entities which entered the DB in the last X hours.';

    public function handle(): int
    {
        $indexableModels = TypesenseCollectionService::getIndexableModels();
        $indexableModelClasses = array_keys($indexableModels);

        $modelOption = $this->option('model');
        $models = $modelOption
            ? [Arr::first($indexableModelClasses, fn($m) => Str::endsWith($m, $modelOption))]
            : $indexableModelClasses;

        $hours = $this->argument('hours');
        $startingDate = $hours ? now()->subHours((int) $hours) : null;

        if ($startingDate) {
            $this->info("Reindexing entities created after {$startingDate->toDateTimeString()}");
        } else {
            $this->info("Reindexing all entities");
        }

        // Disable queue to avoid overflow
        config(['scout.queue' => false]);

        // Rebuild Typesense collections
        $typesenseService = new TypesenseCollectionService($this->output);
        $typesenseService->rebuildCollections($models);

        foreach ($models as $modelClass) {
            if (is_null($modelClass)) {
                $this->warn("Skipping unknown model: {$modelOption}");
                continue;
            }

            $this->newLine();
            $this->info("Reindexing: <comment>{$modelClass}</comment>");

            $query = $modelClass::query()
                ->when($startingDate, fn($q) => $q->where('created_at', '>=', $startingDate));

            $count = $query->count();
            $this->line("  Found <comment>{$count}</comment> records");

            if ($count === 0) {
                $this->line("  Nothing to index, skipping.");
                continue;
            }

            $bar = $this->output->createProgressBar($count);
            $bar->start();

            $modelClass::withoutAuthorization(function () use ($query, $bar) {
                $query->chunk(100, function ($items) use ($bar) {
                    foreach ($items as $item) {
                        $item->searchable();
                        $bar->advance();
                    }
                });
            });

            $bar->finish();
            $this->newLine();
            $this->info("  Done: {$modelClass}");
        }

        $this->newLine();
        $this->info("Reindex completed!");

        return self::SUCCESS;
    }
}
