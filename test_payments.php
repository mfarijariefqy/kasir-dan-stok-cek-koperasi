<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = app(\App\Services\TransactionService::class);
$user = \App\Models\User::first();
auth()->login($user);

$product = \App\Models\Product::first();

// Test Cash transaction
$dataCash = [
    'total' => $product->price,
    'paid' => $product->price,
    'change' => 0,
    'payment_method' => 'Cash',
    'items' => [
        [
            'product_id' => $product->id,
            'qty' => 1,
            'price' => $product->price,
            'subtotal' => $product->price
        ]
    ]
];

$trxCash = $service->createTransaction($dataCash);
echo "Transaction (Cash) created: " . $trxCash->trx_no . " | Method: " . $trxCash->payment_method . "\n";

// Test QRIS transaction
$dataQris = [
    'total' => $product->price,
    'paid' => $product->price,
    'change' => 0,
    'payment_method' => 'QRIS',
    'items' => [
        [
            'product_id' => $product->id,
            'qty' => 1,
            'price' => $product->price,
            'subtotal' => $product->price
        ]
    ]
];

$trxQris = $service->createTransaction($dataQris);
echo "Transaction (QRIS) created: " . $trxQris->trx_no . " | Method: " . $trxQris->payment_method . "\n";

echo "Both transactions created successfully.\n";
