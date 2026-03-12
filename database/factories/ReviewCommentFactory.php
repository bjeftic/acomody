<?php

namespace Database\Factories;

use App\Models\AccommodationDraft;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ReviewComment>
 */
class ReviewCommentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'commentable_type' => AccommodationDraft::class,
            'commentable_id' => AccommodationDraft::factory(),
            'user_id' => User::factory(),
            'body' => $this->faker->paragraph(),
        ];
    }
}
