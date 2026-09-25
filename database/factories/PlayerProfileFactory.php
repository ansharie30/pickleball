<?php

namespace Database\Factories;

use App\Models\PlayerProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlayerProfile>
 */
class PlayerProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => null,
            'name' => fake()->name(),
            'rating' => fake()->numberBetween(800, 1600),
            'wins' => fake()->numberBetween(0, 20),
            'losses' => fake()->numberBetween(0, 20),
        ];
    }
}
