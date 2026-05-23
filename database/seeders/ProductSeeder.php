<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            ['name' => 'Kopi Hitam', 'sku' => 'KH001', 'price' => 10000, 'is_active' => true],
            ['name' => 'Kopi Susu', 'sku' => 'KS001', 'price' => 15000, 'is_active' => true],
            ['name' => 'Cappuccino', 'sku' => 'CP001', 'price' => 18000, 'is_active' => true],
            ['name' => 'Latte', 'sku' => 'LT001', 'price' => 20000, 'is_active' => true],
            ['name' => 'Teh Manis', 'sku' => 'TM001', 'price' => 8000, 'is_active' => true],
            ['name' => 'Teh Tarik', 'sku' => 'TT001', 'price' => 12000, 'is_active' => true],
            ['name' => 'Jus Jeruk', 'sku' => 'JJ001', 'price' => 15000, 'is_active' => true],
            ['name' => 'Roti Bakar', 'sku' => 'RB001', 'price' => 12000, 'is_active' => true],
            ['name' => 'Pisang Goreng', 'sku' => 'PG001', 'price' => 10000, 'is_active' => true],
            ['name' => 'Kentang Goreng', 'sku' => 'KG001', 'price' => 15000, 'is_active' => true],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
