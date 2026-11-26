<?php

namespace Database\Seeders;

use App\Models\Deposit;
use App\Models\Partner;
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
        $partners = Partner::pluck('id', 'type');

        // Pengangkutan pertama: sudah diserahkan ke mitra pendaur ulang
        $this->createPickup('2026-11-10', $partners[Partner::TYPE_RECYCLER], $deposits->take(1), Deposit::STATUS_COMPLETED);

        // Pengangkutan kedua: masih dalam pengangkutan ke pengrajin
        $this->createPickup('2026-11-17', $partners[Partner::TYPE_CRAFTSMAN], $deposits->slice(1, 2), Deposit::STATUS_SHIPPED);
    }

    private function createPickup(string $date, int $partnerId, Collection $deposits, string $status): void
    {
        $pickup = Pickup::create([
            'pickup_number' => Pickup::generateNumber($date),
            'pickup_date' => $date,
            'partner_id' => $partnerId,
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
