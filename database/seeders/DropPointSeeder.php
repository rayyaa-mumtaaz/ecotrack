<?php

namespace Database\Seeders;

use App\Models\DropPoint;
use Illuminate\Database\Seeder;

class DropPointSeeder extends Seeder
{
    /**
     * Data contoh lokasi pengumpulan. Ganti dengan lokasi sebenarnya bila tersedia.
     */
    public function run(): void
    {
        DropPoint::create([
            'name' => 'Drop Point Gedung Utama',
            'address' => 'Lobi Gedung Utama Kampus',
            'description' => 'Lokasi pengumpulan utama.',
            'is_active' => true,
        ]);

        DropPoint::create([
            'name' => 'Drop Point Perpustakaan',
            'address' => 'Lantai 1 Perpustakaan Kampus',
            'description' => 'Dekat pintu masuk perpustakaan.',
            'is_active' => true,
        ]);

        DropPoint::create([
            'name' => 'Drop Point Kantin',
            'address' => 'Area Kantin Kampus',
            'description' => 'Sementara tidak beroperasi.',
            'is_active' => false,
        ]);
    }
}
