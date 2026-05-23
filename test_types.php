<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$products = \App\Models\Product::all();
echo "Total Products: " . $products->count() . "\n";
foreach ($products as $p) {
    if ($p->type != 'Minuman') { // Since default is Minuman, check if others exist or just list
        echo "Found different type: {$p->name} -> {$p->type}\n";
    }
}

// Let's create a Snack and a Makanan Berat manually just to ensure DB takes it
$snack = \App\Models\Product::create([
    'name' => 'Kentang Goreng Test',
    'sku' => 'SNK-001',
    'price' => 15000,
    'type' => 'Snack',
    'is_active' => true
]);
echo "Created Snack: {$snack->name} with type {$snack->type}\n";

$food = \App\Models\Product::create([
    'name' => 'Nasi Goreng Test',
    'sku' => 'MK-001',
    'price' => 25000,
    'type' => 'Makanan Berat',
    'is_active' => true
]);
echo "Created Food: {$food->name} with type {$food->type}\n";
