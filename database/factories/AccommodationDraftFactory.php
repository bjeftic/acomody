<?php

namespace Database\Factories;

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
            'status' => $this->faker->randomElement(['draft', 'waiting_for_approval', 'published']),
            'data' => json_encode([
                'accommodation_type'       => $this->faker->numberBetween(1, 5),
                'accommodation_occupation' => $this->faker->numberBetween(1, 3),

                'address' => [
                    'country'  => 'RS',
                    'street'   => $this->faker->streetAddress(),
                    'city'     => $this->faker->city(),
                    'state'    => null,
                    'zip_code' => $this->faker->postcode(),
                ],

                'coordinates' => [
                    'latitude'  => $this->faker->latitude(44.7, 44.9),
                    'longitude' => $this->faker->longitude(20.3, 20.6),
                ],

                'floor_plan' => [
                    'guests'    => $this->faker->numberBetween(1, 8),
                    'bedrooms'  => $this->faker->numberBetween(1, 4),
                    'beds'      => $this->faker->numberBetween(1, 6),
                    'bathrooms' => $this->faker->numberBetween(1, 3),
                ],

                'amenities' => $this->faker->randomElements([2,5,8,102,105,106,113,114], 4),

                'title'       => $this->faker->sentence(4),
                'description' => $this->faker->paragraph(4),

                'pricing' => [
                    'basePrice'            => $this->faker->numberBetween(20, 120),
                    'hasWeekendPrice'      => false,
                    'weekendPrice'         => 0,
                    'weeklyDiscount'       => 0,
                    'monthlyDiscount'      => 0,
                    'bookingType'          => 'instant',
                    'minStay'              => 1,
                    'hasDaySpecificMinStay'=> false,
                    'daySpecificMinStay'   => [
                        'monday'    => ['enabled' => false, 'nights' => 1],
                        'tuesday'   => ['enabled' => false, 'nights' => 1],
                        'wednesday' => ['enabled' => false, 'nights' => 1],
                        'thursday'  => ['enabled' => false, 'nights' => 1],
                        'friday'    => ['enabled' => false, 'nights' => 3],
                        'saturday'  => ['enabled' => false, 'nights' => 2],
                        'sunday'    => ['enabled' => false, 'nights' => 1],
                    ],
                ],

                'house_rules' => [
                    'allowSmoking'    => false,
                    'allowPets'       => false,
                    'allowEvents'     => false,
                    'allowChildren'   => true,
                    'allowInfants'    => true,
                    'checkInFrom'     => '15:00',
                    'checkInUntil'    => '20:00',
                    'checkOutUntil'   => '11:00',
                    'hasQuietHours'   => false,
                    'quietHoursFrom'  => '22:00',
                    'quietHoursUntil' => '08:00',
                    'additionalRules' => null,
                    'cancellationPolicy' => 'moderate',
                ],
            ]),

            'current_step'   => 12,
            'last_saved_at'  => now(),
        ];
    }
}
