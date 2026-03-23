<?php

namespace Database\Seeders;

use App\Models\Accommodation;
use App\Models\AccommodationDraft;
use App\Models\Location;
use App\Models\User;
use App\Services\TypesenseCollectionService;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (app()->environment('local', 'development')) {

            $this->command->info('Start seeding...');

            $this->command->info('Setting up Typesense collections...');
            $this->setupTypesenseCollections();
            $this->command->newLine();

            // Users
            $this->command->info('Creating users...');
            $this->withProgressBar(150, function () {
                User::factory()->create();
            });
            $this->command->newLine();

            // Accommodation Drafts
            $this->command->info('Creating accommodation drafts...');
            $this->withProgressBar(150, function () {
                AccommodationDraft::withoutAuthorization(function () {
                    AccommodationDraft::factory()->create();
                });
            });
            $this->command->newLine();

            // Locations
            $this->command->info('Creating locations...');
            $this->withProgressBar(100, function () {
                Location::withoutAuthorization(function () {
                    Location::factory()->create();
                });
            });
            $this->command->newLine();

            // Accommodations
            $this->command->info('Creating accommodations...');
            $this->withProgressBar(1000, function () {
                Accommodation::withoutAuthorization(function () {
                    Accommodation::factory()->create();
                });
            });
            $this->command->newLine();

            // Home Sections
            $this->command->info('Creating home sections...');
            $this->call(HomeSectionSeeder::class);
            $this->command->newLine();

            // Legal Documents
            $this->command->info('Creating legal documents...');
            $this->call(LegalDocumentSeeder::class);
            $this->command->newLine();

            $this->command->info('Seeding completed!');
        }
    }

    /**
     * Setup Typesense collections
     */
    protected function setupTypesenseCollections(): void
    {
        $models = array_keys(TypesenseCollectionService::getIndexableModels());
        $typesenseService = new TypesenseCollectionService($this->command->getOutput());
        $typesenseService->rebuildCollections($models, verbose: true);
    }

    /**
     * Show progress bar for seeding
     */
    protected function withProgressBar(int $count, callable $callback): void
    {
        $bar = $this->command->getOutput()->createProgressBar($count);
        $bar->start();

        for ($i = 0; $i < $count; $i++) {
            $callback();
            $bar->advance();
        }

        $bar->finish();
    }
}
