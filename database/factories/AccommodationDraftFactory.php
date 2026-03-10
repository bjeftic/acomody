<?php

namespace Database\Factories;

use App\Enums\Accommodation\AccommodationOccupation;
use App\Enums\Accommodation\AccommodationType;
use App\Models\Amenity;
use App\Models\Country;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AccommodationDraft>
 */
class AccommodationDraftFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'status' => $this->faker->randomElement(['draft', 'waiting_for_approval', 'processing', 'published', 'rejected']),
            'data' => json_encode([
                'accommodation_type' => AccommodationType::cases()[array_rand(AccommodationType::cases())]->value,
                'accommodation_occupation' => AccommodationOccupation::cases()[array_rand(AccommodationOccupation::cases())]->value,

                'address' => [
                    'country' => Country::inRandomOrder()->first()->iso_code_2,
                    'street' => $this->faker->streetAddress(),
                    'city' => $this->faker->city(),
                    'state' => null,
                    'zip_code' => $this->faker->postcode(),
                ],

                'coordinates' => [
                    'latitude' => $this->faker->latitude(19, 22),
                    'longitude' => $this->faker->longitude(42, 44),
                ],

                'floor_plan' => [
                    'guests' => $this->faker->numberBetween(1, 8),
                    'bedrooms' => $this->faker->numberBetween(1, 4),
                    'bathrooms' => $this->faker->numberBetween(1, 3),
                    'bed_types' => collect(\App\Enums\Accommodation\BedType::cases())
                        ->shuffle()
                        ->take(2)
                        ->map(fn ($bt) => [
                            'bed_type' => $bt->value,
                            'quantity' => $this->faker->numberBetween(1, 3),
                        ])
                        ->values()
                        ->toArray(),
                ],

                'amenities' => Amenity::inRandomOrder()->limit(4)->pluck('id')->toArray(),

                'title' => $this->faker->sentence(4),
                'description' => $this->faker->paragraph(4),

                'pricing' => [
                    'basePrice' => $this->faker->numberBetween(20, 120),
                    // 'hasWeekendPrice'      => false,
                    // 'weekendPrice'         => 0,
                    // 'weeklyDiscount'       => 0,
                    // 'monthlyDiscount'      => 0,
                    'bookingType' => $this->faker->randomElement(['instant_booking', 'request_to_book']),
                    'minStay' => 1,
                    // 'hasDaySpecificMinStay'=> false,
                    // 'daySpecificMinStay'   => [
                    //     'monday'    => ['enabled' => false, 'nights' => 1],
                    //     'tuesday'   => ['enabled' => false, 'nights' => 1],
                    //     'wednesday' => ['enabled' => false, 'nights' => 1],
                    //     'thursday'  => ['enabled' => false, 'nights' => 1],
                    //     'friday'    => ['enabled' => false, 'nights' => 3],
                    //     'saturday'  => ['enabled' => false, 'nights' => 2],
                    //     'sunday'    => ['enabled' => false, 'nights' => 1],
                    // ],
                    // 'standardFees' => [
                    //     [
                    //         'feeType'    => 'cleaning',
                    //         'amount'     => 10,
                    //         'chargeType' => 'per_booking',
                    //         'isOptional' => false,
                    //     ],
                    //     [
                    //         'feeType'    => 'guest_service',
                    //         'amount'     => 5,
                    //         'chargeType' => 'percentage',
                    //         'isOptional' => false,
                    //     ],
                    // ],
                    // 'amenityFees' => [
                    //     [
                    //         'feeType'    => 'amenity',
                    //         'name'       => Amenity::where('is_feeable', true)->inRandomOrder()->first()->id,
                    //         'amount'     => 5,
                    //         'chargeType' => 'per_unit',
                    //     ],
                    //     [
                    //         'feeType'    => 'amenity',
                    //         'name'       => Amenity::where('is_feeable', true)->inRandomOrder()->first()->id,
                    //         'amount'     => 5,
                    //         'chargeType' => 'per_booking',
                    //     ],
                    // ],
                    // 'customFees' => [
                    //     [
                    //         'feeType'     => 'custom',
                    //         'name'        => 'Some fee',
                    //         'description' => 'Some fee',
                    //         'amount'      => 5,
                    //         'chargeType'  => 'per_unit',
                    //         'isOptional'  => false,
                    //     ],
                    // ],
                ],
                'house_rules' => [
                    'checkInFrom' => '15:00',
                    'checkInUntil' => '20:00',
                    'checkOutUntil' => '11:00',
                    'hasQuietHours' => false,
                    'quietHoursFrom' => '22:00',
                    'quietHoursUntil' => '08:00',
                    'cancellationPolicy' => 'moderate',
                ],
            ]),

            'current_step' => 12,
            'last_saved_at' => now(),
        ];
    }
}
