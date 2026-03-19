<?php

namespace Database\Seeders;

use App\Enums\HomeSection\SectionType;
use App\Models\HomeSection;
use App\Models\HomeSectionLocation;
use App\Models\Location;
use Illuminate\Database\Seeder;

class HomeSectionSeeder extends Seeder
{
    public function run(): void
    {
        HomeSection::withoutAuthorization(function () {
            // Section 1: Locations row — visible to all
            $locationsSection = HomeSection::create([
                'title' => [
                    'en' => 'Top Destinations',
                    'sr' => 'Najpopularnije destinacije',
                    'de' => 'Top-Reiseziele',
                ],
                'type' => SectionType::Locations,
                'sort_order' => 0,
                'is_active' => true,
                'country_codes' => null,
            ]);

            // Section 2: Accommodations by location — all countries
            $accommodationsSection = HomeSection::create([
                'title' => [
                    'en' => 'Stay in Belgrade',
                    'sr' => 'Smještaj u Beogradu',
                    'de' => 'Unterkunft in Belgrad',
                ],
                'type' => SectionType::Accommodations,
                'sort_order' => 1,
                'is_active' => true,
                'country_codes' => null,
            ]);

            // Section 3: Serbia-only section
            $serbiaSection = HomeSection::create([
                'title' => [
                    'en' => 'Discover Serbia',
                    'sr' => 'Otkrijte Srbiju',
                    'de' => 'Serbien entdecken',
                ],
                'type' => SectionType::Locations,
                'sort_order' => 2,
                'is_active' => true,
                'country_codes' => ['RS'],
            ]);

            // Attach the first available locations to both sections
            $locations = Location::query()->limit(9)->get();

            HomeSectionLocation::withoutAuthorization(function () use ($locationsSection, $serbiaSection, $locations) {
                foreach ($locations as $index => $location) {
                    HomeSectionLocation::create([
                        'home_section_id' => $locationsSection->id,
                        'location_id' => $location->id,
                        'sort_order' => $index,
                    ]);

                    HomeSectionLocation::create([
                        'home_section_id' => $serbiaSection->id,
                        'location_id' => $location->id,
                        'sort_order' => $index,
                    ]);
                }
            });

            // Attach first location to the accommodations section
            $firstLocation = $locations->first();
            if ($firstLocation) {
                HomeSectionLocation::withoutAuthorization(function () use ($accommodationsSection, $firstLocation) {
                    HomeSectionLocation::create([
                        'home_section_id' => $accommodationsSection->id,
                        'location_id' => $firstLocation->id,
                        'sort_order' => 0,
                    ]);
                });
            }
        });
    }
}
