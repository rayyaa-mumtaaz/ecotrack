<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Akun demo. Password semua akun: password
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin EcoTrack',
            'email' => 'admin@ecotrack.test',
            'password' => 'password',
            'role' => User::ROLE_ADMIN,
        ]);

        $users = [
            ['name' => 'Rayyaa', 'email' => 'rayyaa@ecotrack.test'],
            ['name' => 'Tristan', 'email' => 'tristan@ecotrack.test'],
            ['name' => 'Mumtaaz', 'email' => 'mumtaaz@ecotrack.test'],
        ];

        foreach ($users as $user) {
            User::create([
                'name' => $user['name'],
                'email' => $user['email'],
                'password' => 'password',
                'role' => User::ROLE_USER,
            ]);
        }
    }
}
