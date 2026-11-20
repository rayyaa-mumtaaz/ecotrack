<?php

namespace Database\Seeders;

use App\Models\Deposit;
use App\Models\Pickup;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class PickupSeeder extends Seeder
{
    /**
     * Data contoh pengangkutan.
     * Hasil: 1 setoran Selesai, 2 setoran Diangkut, 2 setoran tetap Terkumpul.
     */
    public function run(): void
    {
        $deposits = Deposit::orderBy('deposit_date')->get();

        // Pengangkutan pertama: sudah diserahkan ke mitra
        $this->createPickup('2026-11-10', 'Mitra Daur Ulang Contoh A', $deposits->take(1), Deposit::STATUS_COMPLETED);

        // Pengangkutan kedua: masih dalam pengangkutan
        $this->createPickup('2026-11-17', 'Mitra Daur Ulang Contoh B', $deposits->slice(1, 2), Deposit::STATUS_SHIPPED);
    }

    private function createPickup(string $date, string $partner, Collection $deposits, string $status): void
    {
        $pickup = Pickup::create([
            'pickup_number' => Pickup::generateNumber($date),
            'pickup_date' => $date,
            'partner_name' => $partner,
        ]);

        foreach ($deposits as $deposit) {
            $deposit->update([
                'pickup_id' => $pickup->id,
                'status' => $status,
            ]);
        }

        $pickup->recalculateTotalWeight();
    }
}
