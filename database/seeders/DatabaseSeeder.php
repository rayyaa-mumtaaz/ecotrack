<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Mengisi data awal EcoTrack.
     * Urutan penting karena ada foreign key:
     * drop point dibuat sebelum user (admin ditugaskan ke drop point),
     * mitra dibuat sebelum pengangkutan.
     */
    public function run(): void
    {
        $this->call([
            CategorySeeder::class,
            DropPointSeeder::class,
            PartnerSeeder::class,
            UserSeeder::class,
            DepositSeeder::class,
            PickupSeeder::class,
        ]);
    }
}
