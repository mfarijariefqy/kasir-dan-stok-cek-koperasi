<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== PRODUCTS ===\n";
foreach (\App\Models\Product::with('ingredients')->get() as $product) {
    echo "Product: {$product->name} (ID: {$product->id})\n";
    foreach ($product->ingredients as $ing) {
        echo "  - Ingredient: {$ing->name} (Pivot Qty: {$ing->pivot->qty})\n";
    }
}

echo "\n=== INGREDIENT LOGS ===\n";
foreach (\App\Models\IngredientLog::latest()->take(5)->get() as $log) {
    echo "Log: {$log->type} - {$log->qty} - {$log->note}\n";
}

echo "\n=== TRANSACTIONS ===\n";
foreach (\App\Models\Transaction::with('items')->latest()->take(3)->get() as $trx) {
    echo "Trx: {$trx->trx_no} (Total: {$trx->total})\n";
    foreach ($trx->items as $item) {
        echo "  - Item: Product ID {$item->product_id}, Qty {$item->qty}\n";
    }
}
