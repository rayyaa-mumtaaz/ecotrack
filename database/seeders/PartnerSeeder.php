<?php

namespace Database\Seeders;

use App\Models\Partner;
use Illuminate\Database\Seeder;

class PartnerSeeder extends Seeder
{
    /**
     * Mitra pengelola e-waste. Nama dan kontak fiktif (bukan usaha sebenarnya);
     * nomor telepon sengaja dikosongkan agar tidak mengarah ke nomor milik orang lain.
     */
    public function run(): void
    {
        $partners = [
            [
                'name' => 'CV Lestari Daur Elektronik',
                'type' => Partner::TYPE_RECYCLER,
                'contact_person' => 'Sulastri',
                'address' => 'Kec. Kaliwates, Jember',
                'description' => 'Memilah dan mengolah komponen elektronik (logam, plastik, PCB) menjadi bahan baku daur ulang.',
            ],
            [
                'name' => 'UD Mitra Rosok Elektronik',
                'type' => Partner::TYPE_COLLECTOR,
                'contact_person' => 'Mulyono',
                'address' => 'Kec. Sumbersari, Jember',
                'description' => 'Menampung barang elektronik bekas dalam jumlah besar untuk disalurkan ke pengolah.',
            ],
        ];

        foreach ($partners as $partner) {
            Partner::create($partner);
        }
    }
}
