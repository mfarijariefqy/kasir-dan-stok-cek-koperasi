<?php

namespace Database\Seeders;

use App\Models\Ingredient;
use Illuminate\Database\Seeder;

class IngredientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ingredients = [
            ['name' => 'Kopi Bubuk', 'unit' => 'gram', 'stock' => 5000],
            ['name' => 'Susu Cair', 'unit' => 'ml', 'stock' => 10000],
            ['name' => 'Gula Pasir', 'unit' => 'gram', 'stock' => 3000],
            ['name' => 'Teh Celup', 'unit' => 'pcs', 'stock' => 200],
            ['name' => 'Jeruk', 'unit' => 'pcs', 'stock' => 50],
        ];

        foreach ($ingredients as $ingredient) {
            Ingredient::create($ingredient);
        }
    }
}
