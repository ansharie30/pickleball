<?php

namespace Database\Factories;

use App\Models\Court;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Court>
 */
class CourtFactory extends Factory
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
            'name' => 'Court ' . fake()->unique()->numberBetween(1, 20),
            'qr_code_token' => \Illuminate\Support\Str::random(16),
            'status' => 'available',
        ];
    }
}
