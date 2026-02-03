<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Enums\Location\LocationType;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Location>
 */
class LocationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->city(),
            'country_id' => \App\Models\Country::inRandomOrder()->first()->id,
            'parent_id' => fake()->boolean(50)
                ? \App\Models\Location::inRandomOrder()->first()?->id
                : null,
            'location_type' => LocationType::cases()[array_rand(LocationType::cases())]->value,
            'longitude' => fake()->longitude(41, 44),
            'latitude' => fake()->latitude(19, 22),
            'is_active' => true,
            'user_id' => \App\Models\User::inRandomOrder()->first()->id,
        ];
    }
}
