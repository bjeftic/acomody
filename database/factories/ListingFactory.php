<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Listing;
use App\Models\Location;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Listing>
 */
class ListingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $locationId = Location::factory()->create()->id;

        return [
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'listable_id' => null,
            'listable_type' => null,
            'location_id' => $locationId,
            'user_id' => User::inRandomOrder()->first()->id,
            'longitude' => fake()->longitude(-180, 180),
            'latitude' => fake()->latitude(-90, 90),
            'street_address' => fake()->address(),
            'is_active' => fake()->boolean(95),
            'approved_by' => User::where('is_superadmin', true)->inRandomOrder()->first()->id,
        ];
    }
}
