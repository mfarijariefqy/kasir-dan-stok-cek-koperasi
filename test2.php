<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = app(\App\Services\TransactionService::class);
$user = \App\Models\User::first();
auth()->login($user);

// Get Product 12 (Kopi Hitam that has ingredients)
$p = \App\Models\Product::with('ingredients')->find(12);
if (!$p)
    die("Product 12 not found\n");

echo "=== STOCK BEFORE ===\n";
foreach ($p->ingredients as $i) {
    echo "- {$i->name}: $i->stock\n";
}

$data = [
    'total' => 20000,
    'paid' => 20000,
    'change' => 0,
    'items' => [
        [
            'product_id' => 12,
            'qty' => 1,
            'price' => 20000,
            'subtotal' => 20000
        ]
    ]
];

$trx = $service->createTransaction($data);
echo "\n=== TRANSACTION CREATED ===\n";
echo "TRX: {$trx->trx_no}\n\n";

echo "=== STOCK AFTER ===\n";
foreach ($p->ingredients as $i) {
    // Refresh ingredient from DB
    $fresh = \App\Models\Ingredient::find($i->id);
    echo "- {$fresh->name}: {$fresh->stock}\n";
}

echo "\n=== LATEST LOGS ===\n";
foreach (\App\Models\IngredientLog::latest()->take(2)->get() as $log) {
    echo "- {$log->type} Qty {$log->qty} Note: {$log->note}\n";
}
