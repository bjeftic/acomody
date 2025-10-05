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
        ];
    }
}
