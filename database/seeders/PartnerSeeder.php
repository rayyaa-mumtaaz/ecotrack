<?php

namespace Database\Seeders;

use App\Models\Partner;
use Illuminate\Database\Seeder;

class PartnerSeeder extends Seeder
{
    /**
     * Data contoh mitra pengelola e-waste. Bukan mitra sebenarnya.
     */
    public function run(): void
    {
        $partners = [
            [
                'name' => 'Mitra Daur Ulang Contoh',
                'type' => Partner::TYPE_RECYCLER,
                'contact_person' => 'Bagian Penerimaan',
                'phone' => '081200000001',
                'address' => 'Kawasan Industri (contoh)',
                'description' => 'Mengolah komponen elektronik menjadi bahan baku.',
            ],
            [
                'name' => 'Pengrajin Kreasi Contoh',
                'type' => Partner::TYPE_CRAFTSMAN,
                'contact_person' => 'Pemilik Usaha',
                'phone' => '081200000002',
                'address' => 'Sentra Kerajinan (contoh)',
                'description' => 'Membuat kerajinan dari kabel, PCB, dan casing bekas.',
            ],
            [
                'name' => 'Pengepul Elektronik Contoh',
                'type' => Partner::TYPE_COLLECTOR,
                'contact_person' => 'Pengelola Gudang',
                'phone' => '081200000003',
                'address' => 'Pasar Barang Bekas (contoh)',
                'description' => 'Menampung e-waste dalam jumlah besar.',
            ],
        ];

        foreach ($partners as $partner) {
            Partner::create($partner);
        }
    }
}
