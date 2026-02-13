<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Accommodation;
use App\Models\AccommodationDraft;
use App\Models\Location;
use App\Enums\Accommodation\AccommodationType;
use App\Models\Amenity;
use App\Models\User;
use App\Enums\Accommodation\AccommodationOccupation;
use App\Enums\Accommodation\BookingType;
use App\Models\PriceableItem;
use App\Enums\PriceableItem\PricingType;
use App\Models\Photo;

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
        $locationId = Location::inRandomOrder()->first()?->id ?? Location::factory()->create()->id;

        return [
            'accommodation_type' => AccommodationType::cases()[array_rand(AccommodationType::cases())]->value,
            'accommodation_occupation' => AccommodationOccupation::cases()[array_rand(AccommodationOccupation::cases())]->value,
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(4),
            'booking_type' => BookingType::cases()[array_rand(BookingType::cases())]->value,
            'cancellation_policy' => fake()->randomElement(['flexible', 'moderate', 'firm', 'strict']),
            'max_guests' => fake()->numberBetween(1, 10),
            'location_id' => $locationId,
            'user_id' => User::inRandomOrder()->first()->id,
            'latitude' => fake()->latitude(41, 44),
            'longitude' => fake()->longitude(19, 22),
            'street_address' => fake()->address(),
            'is_active' => fake()->boolean(95),
            'approved_by' => User::where('is_superadmin', true)->inRandomOrder()->first()->id,
            'views_count' => fake()->numberBetween(0, 10000),
            'favorites_count' => fake()->numberBetween(0, 5000),
            'is_featured' => fake()->boolean(10),
            'bedrooms' => fake()->numberBetween(1, 4),
            'beds' => fake()->numberBetween(1, 10),
            'bathrooms' => fake()->numberBetween(1, 2),
        ];
    }

    public function configure()
    {
        return $this->afterMaking(function (Accommodation $accommodation) {
            if (!isset($accommodation->user_id)) {
                $accommodation->user_id = User::factory()->create()->id;
            }

            $draft = AccommodationDraft::factory()->create([
                'user_id' => $accommodation->user_id,
                'status' => 'published',
            ]);

            $accommodation->accommodation_draft_id = $draft->id;
        })->afterCreating(function (Accommodation $accommodation) {
            $currency = fake()->randomElement(['EUR', 'USD', 'RSD']);

            if ($currency === 'EUR' || $currency === 'USD') {
                $price = fake()->numberBetween(30, 120);
                $basePriceEur = $currency === 'EUR' ? $price : round($price / 1.1, 2);
            }
            if ($currency === 'RSD') {
                $price = fake()->numberBetween(3000, 12000);
                $basePriceEur = round($price / 117, 2);
            }
            PriceableItem::create([
                'pricing_type' => PricingType::NIGHTLY,
                'base_price' => $price,
                'currency' => $currency,
                'base_price_eur' => $basePriceEur,
                'priceable_type' => Accommodation::class,
                'priceable_id' => $accommodation->id,
                'min_quantity' => 1,
                'max_quantity' => null,
                'is_active' => true,
            ]);

            $amenityIds = Amenity::inRandomOrder()
                ->where('category', 'essential')
                ->limit(fake()->numberBetween(2, 5))
                ->pluck('id')
                ->merge(
                    Amenity::inRandomOrder()
                        ->whereNot('category', 'essential')
                        ->limit(fake()->numberBetween(10, 15))
                        ->pluck('id')
                );

            $accommodation->amenities()->attach($amenityIds);

            // Create photos (5-12 photos per accommodation)
            $photoCount = fake()->numberBetween(5, 12);

            Photo::factory()
                ->count($photoCount)
                ->forAccommodation() // Use accommodation photos disk
                ->withPicsumPaths()
                ->sequence(fn ($sequence) => [
                    'order' => $sequence->index,
                    'is_primary' => $sequence->index === 0, // First photo is primary
                ])
                ->create([
                    'photoable_type' => Accommodation::class,
                    'photoable_id' => $accommodation->id,
                ]);
        });
    }
}
