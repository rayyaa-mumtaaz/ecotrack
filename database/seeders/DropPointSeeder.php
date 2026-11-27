<?php

namespace Database\Seeders;

use App\Models\DropPoint;
use Illuminate\Database\Seeder;

class DropPointSeeder extends Seeder
{
    /**
     * Lokasi pengumpulan awal di kampus (Jember).
     * Koordinat masih perkiraan; titik tepat dapat digeser admin melalui peta pada form drop point.
     */
    public function run(): void
    {
        DropPoint::create([
            'name' => 'Drop Point Lapangan STS',
            'address' => 'Area Lapangan STS, Universitas Jember',
            'latitude' => -8.1658479,
            'longitude' => 113.7180233,
            'description' => 'Titik pengumpulan sampah elektronik di sekitar Lapangan STS.',
            'is_active' => true,
        ]);
    }
}
