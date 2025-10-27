<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Accommodation;
use App\Models\Listing;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Accommodation>
 */
class AccommodationFactory extends Factory
{
    protected $model = Accommodation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'accommodation_type_id' => \App\Models\AccommodationType::inRandomOrder()->first()->id,
            'amenities' => \App\Models\Amenity::inRandomOrder()
                ->limit(fake()->numberBetween(2, 5))
                ->get()
                ->toJson(),
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Accommodation $accommodation) {
            Listing::factory()->create([
                'listable_id' => $accommodation->id,
                'listable_type' => get_class($accommodation),
            ]);
        });
    }
}
