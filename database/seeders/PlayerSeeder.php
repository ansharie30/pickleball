<?php

namespace Database\Seeders;

use App\Models\PlayerProfile;
use App\Models\User;
use Illuminate\Database\Seeder;

class PlayerSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()
            ->count(20)
            ->create(['role' => 'player'])
            ->each(function (User $user): void {
                PlayerProfile::factory()->create([
                    'user_id' => $user->id,
                    'name' => $user->name,
                ]);
            });
    }
}