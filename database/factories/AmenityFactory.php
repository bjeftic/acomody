<?php

namespace Database\Factories;

use App\Models\Amenity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Amenity>
 */
class AmenityFactory extends Factory
{
    protected $model = Amenity::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'slug' => $this->faker->unique()->slug(2),
            'name' => ['en' => $this->faker->word()],
            'icon' => null,
            'category' => 'essential',
            'is_feeable' => false,
            'is_active' => true,
        ];
    }
}
