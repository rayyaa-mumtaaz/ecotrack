<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Deposit;
use App\Models\DepositItem;
use App\Models\DropPoint;
use App\Models\User;
use Illuminate\Database\Seeder;

class DepositSeeder extends Seeder
{
    /**
     * Data contoh transaksi setoran beserta detail barang.
     */
    public function run(): void
    {
        $users = User::where('role', User::ROLE_USER)->pluck('id', 'email');
        $dropPoints = DropPoint::where('is_active', true)->pluck('id', 'name');
        $categories = Category::pluck('id', 'name');

        // Petugas yang bertugas di tiap drop point = penerima barang
        $officers = User::where('role', User::ROLE_ADMIN)->pluck('id', 'drop_point_id');

        // Format barang: [nama, kategori, jumlah, berat (kg), kondisi]
        $deposits = [
            [
                'email' => 'rayyaa@ecotrack.test',
                'drop_point' => 'Drop Point Gedung Utama',
                'date' => '2026-10-20',
                'items' => [
                    ['Laptop bekas', 'Perangkat komputer', 1, 2.10, DepositItem::CONDITION_BROKEN],
                    ['Charger laptop', 'Perangkat elektronik kecil', 2, 0.60, DepositItem::CONDITION_WORKING],
                ],
            ],
            [
                'email' => 'tristan@ecotrack.test',
                'drop_point' => 'Drop Point Perpustakaan',
                'date' => '2026-11-03',
                'items' => [
                    ['Handphone', 'Perangkat komunikasi', 2, 0.40, DepositItem::CONDITION_DAMAGED],
                    ['Kabel data', 'Perangkat elektronik kecil', 5, 0.25, DepositItem::CONDITION_WORKING],
                ],
            ],
            [
                'email' => 'mumtaaz@ecotrack.test',
                'drop_point' => 'Drop Point Gedung Utama',
                'date' => '2026-11-05',
                'items' => [
                    ['Monitor CRT', 'Perangkat komputer', 1, 7.50, DepositItem::CONDITION_BROKEN],
                ],
            ],
            [
                'email' => 'rayyaa@ecotrack.test',
                'drop_point' => 'Drop Point Perpustakaan',
                'date' => '2026-11-12',
                'items' => [
                    ['Baterai AA', 'Perangkat elektronik kecil', 20, 0.46, DepositItem::CONDITION_BROKEN],
                    ['Radio portabel', 'Perangkat elektronik lainnya', 1, 0.80, DepositItem::CONDITION_DAMAGED],
                ],
            ],
            [
                'email' => 'tristan@ecotrack.test',
                'drop_point' => 'Drop Point Gedung Utama',
                'date' => '2026-11-18',
                'items' => [
                    ['Mouse', 'Perangkat komputer', 3, 0.30, DepositItem::CONDITION_WORKING],
                    ['Powerbank', 'Perangkat elektronik kecil', 1, 0.35, DepositItem::CONDITION_DAMAGED],
                ],
            ],
        ];

        foreach ($deposits as $data) {
            $dropPointId = $dropPoints[$data['drop_point']];

            $deposit = Deposit::create([
                'deposit_number' => Deposit::generateNumber($data['date']),
                'user_id' => $users[$data['email']],
                'received_by' => $officers[$dropPointId],
                'drop_point_id' => $dropPointId,
                'deposit_date' => $data['date'],
                'status' => Deposit::STATUS_COLLECTED,
            ]);

            foreach ($data['items'] as [$name, $category, $quantity, $weight, $condition]) {
                $deposit->items()->create([
                    'category_id' => $categories[$category],
                    'item_name' => $name,
                    'quantity' => $quantity,
                    'weight' => $weight,
                    'condition' => $condition,
                ]);
            }

            $deposit->recalculateTotalWeight();
        }
    }
}
