<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Mengisi data awal EcoTrack.
     * Urutan penting karena ada foreign key.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CategorySeeder::class,
            DropPointSeeder::class,
            DepositSeeder::class,
            PickupSeeder::class,
        ]);
    }
}
