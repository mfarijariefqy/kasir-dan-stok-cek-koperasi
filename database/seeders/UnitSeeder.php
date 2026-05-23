<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            'pcs', 'box', 'lusin', 'kodi',
            'kg', 'gram', 'ons',
            'liter', 'ml',
            'botol', 'sachet', 'karton', 'pak', 'dus',
            'meter', 'roll',
        ];

        foreach ($units as $name) {
            Unit::firstOrCreate(['name' => $name], ['is_active' => true]);
        }
    }
}
