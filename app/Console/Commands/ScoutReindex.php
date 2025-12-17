<?php

namespace App\Console\Commands;

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
            $bar = $this->output->createProgressBar($count);
            $query->chunk(100, function ($items) use ($bar) {
                foreach ($items as $item) {
                    $item->searchable();
                    $bar->advance();
                }
            });
            $bar->finish();
            $this->info("");
            $this->info("Done with model: {$modelClass}");
        }

        $this->info("");
        $this->info("done with non client company models");
        $this->info("Done!");
    }
}
