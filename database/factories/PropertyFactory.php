<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Property;
use App\Models\Listing;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Property>
 */
class PropertyFactory extends Factory
{
    protected $model = Property::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'property_type_id' => \App\Models\PropertyType::inRandomOrder()->first()->id,
            'amenities' => \App\Models\Amenity::inRandomOrder()
                ->limit(fake()->numberBetween(2, 5))
                ->get()
                ->toJson(),
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Property $property) {
            // morphOne veza: property -> listing
            Listing::factory()->create([
                'listable_id' => $property->id,
                'listable_type' => get_class($property),
            ]);
        });
    }
}
