<?php

namespace Database\Seeders;

use App\Models\DropPoint;
use Illuminate\Database\Seeder;

class DropPointSeeder extends Seeder
{
    /**
     * Data contoh lokasi pengumpulan. Ganti dengan lokasi sebenarnya bila tersedia.
     * KOORDINAT CONTOH di area kampus, Kota Jember — titik tepat tiap gedung belum diverifikasi.
     * Koordinat asli diatur admin melalui peta pada form drop point.
     */
    public function run(): void
    {
        DropPoint::create([
            'name' => 'Drop Point Gedung Utama',
            'address' => 'Lobi Gedung Utama Kampus',
            'latitude' => -8.1654000,
            'longitude' => 113.7166000,
            'description' => 'Lokasi pengumpulan utama.',
            'is_active' => true,
        ]);

        DropPoint::create([
            'name' => 'Drop Point Perpustakaan',
            'address' => 'Lantai 1 Perpustakaan Kampus',
            'latitude' => -8.1641000,
            'longitude' => 113.7181000,
            'description' => 'Dekat pintu masuk perpustakaan.',
            'is_active' => true,
        ]);

        DropPoint::create([
            'name' => 'Drop Point Kantin',
            'address' => 'Area Kantin Kampus',
            'latitude' => -8.1667000,
            'longitude' => 113.7149000,
            'description' => 'Sementara tidak beroperasi.',
            'is_active' => false,
        ]);
    }
}
