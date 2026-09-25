<?php

namespace Database\Factories;

use App\Models\Tournament;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tournament>
 */
class TournamentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'venue_id' => \App\Models\Venue::factory(),
            'name' => fake()->city() . ' Open',
            'format' => fake()->randomElement(['single_elimination', 'double_elimination', 'round_robin', 'pool_play']),
            'start_date' => fake()->dateTimeBetween('now', '+1 month'),
            'status' => 'draft',
        ];
    }
}
