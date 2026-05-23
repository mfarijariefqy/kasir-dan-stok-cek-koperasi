<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Sembako',
            'Minuman',
            'Snack',
            'Kebutuhan Rumah Tangga',
            'Alat Tulis',
            'Lainnya',
        ];

        foreach ($categories as $name) {
            Category::firstOrCreate(['name' => $name]);
        }
    }
}
