<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\HostProfile>
 */
class HostProfileFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'display_name' => fake()->name(),
            'contact_email' => fake()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'business_name' => null,
            'address' => null,
            'city' => null,
            'tax_id' => null,
            'vat_number' => null,
            'bio' => null,
            'avatar' => null,
        ];
    }

    public function incomplete(): static
    {
        return $this->state([
            'display_name' => null,
            'contact_email' => null,
            'phone' => null,
        ]);
    }
}
