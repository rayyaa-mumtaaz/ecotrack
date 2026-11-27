<?php

namespace Database\Seeders;

use App\Models\DropPoint;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Akun demo. Password semua akun: password
     */
    public function run(): void
    {
        $dropPoints = DropPoint::pluck('id', 'name');

        // Superadmin (koordinator): tidak ditugaskan ke drop point tertentu
        User::create([
            'name' => 'Koordinator EcoTrack',
            'email' => 'superadmin@ecotrack.com',
            'password' => 'password',
            'role' => User::ROLE_SUPERADMIN,
        ]);

        // Petugas drop point
        User::create([
            'name' => 'Petugas Lapangan STS',
            'email' => 'petugas@ecotrack.com',
            'password' => 'password',
            'role' => User::ROLE_ADMIN,
            'drop_point_id' => $dropPoints['Drop Point Lapangan STS'],
        ]);

        // User / penyetor
        $users = [
            ['name' => 'Rayyaa', 'email' => 'rayyaa@ecotrack.com'],
            ['name' => 'Tristan', 'email' => 'tristan@ecotrack.com'],
            ['name' => 'Mumtaaz', 'email' => 'mumtaaz@ecotrack.com'],
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
