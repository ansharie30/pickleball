<?php

namespace Database\Seeders;

use App\Models\PlayerProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'ansharimasandag5@gmail.com'],
            [
                'name' => 'Anshari',
                'password' => Hash::make('Qweqwe1!'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        // Admins don't strictly need a PlayerProfile, but this keeps things
        // consistent in case you ever want an admin to also join a team.
        if (!$user->playerProfile) {
            PlayerProfile::create([
                'user_id' => $user->id,
                'name' => $user->name,
                'rating' => 1000,
            ]);
        }
    }
}