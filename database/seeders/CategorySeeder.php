<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Perangkat komputer', 'description' => 'Laptop, PC, monitor, keyboard, mouse, dan komponen komputer.'],
            ['name' => 'Perangkat komunikasi', 'description' => 'Handphone, tablet, telepon, dan modem.'],
            ['name' => 'Perangkat elektronik kecil', 'description' => 'Charger, kabel, baterai, earphone, dan powerbank.'],
            ['name' => 'Perangkat elektronik lainnya', 'description' => 'Perangkat elektronik yang tidak termasuk kategori lain.'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
