<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Models\Product;
use App\Models\ProductLog;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    /**
     * Create a new transaction with items, stock deduction, and product logs.
     *
     * @throws InsufficientStockException
     */
    public function create(
        User $user,
        array $items,
        string $paymentMethod,
        ?string $customerName,
        ?int $customerId = null
    ): Transaction {
        for ($attempt = 1; ; $attempt++) {
            try {
                return DB::transaction(function () use ($user, $items, $paymentMethod, $customerName, $customerId) {
                    $paymentStatus = $paymentMethod === 'Cash' ? 'Lunas' : 'Belum Lunas';
                    $preparedItems = $this->prepareItems($items);
                    $total         = array_sum(array_column($preparedItems, 'subtotal'));

                    $transaction = Transaction::create([
                        'trx_no'         => $this->generateTrxNo(),
                        'trx_date'       => now()->toDateString(),
                        'user_id'        => $user->id,
                        'customer_id'    => $customerId,
                        'customer_name'  => $customerName,
                        'payment_method' => $paymentMethod,
                        'payment_status' => $paymentStatus,
                        'paid_at'        => $paymentStatus === 'Lunas' ? now() : null,
                        'total'          => $total,
                    ]);

                    foreach ($preparedItems as $item) {
                        TransactionItem::create([
                            'transaction_id' => $transaction->id,
                            'product_id'     => $item['product']->id,
                            'branch_id'      => $item['product']->branch_id,
                            'qty'            => $item['qty'],
                            'buy_price'      => $item['product']->buy_price,
                            'sell_price'     => $item['product']->sell_price,
                            'subtotal'       => $item['subtotal'],
                        ]);

                        $item['product']->decrement('stock_qty', $item['qty']);

                        ProductLog::create([
                            'product_id' => $item['product']->id,
                            'user_id'    => $user->id,
                            'type'       => 'OUT',
                            'qty'        => $item['qty'],
                            'note'       => 'Transaksi ' . $transaction->trx_no,
                            'logged_at'  => now(),
                        ]);
                    }

                    return $transaction;
                });
            } catch (QueryException $e) {
                $isDuplicateTrxNo = (int) $e->errorInfo[1] === 1062 && str_contains($e->getMessage(), 'trx_no');

                if (! $isDuplicateTrxNo || $attempt >= 3) {
                    throw $e;
                }
                // Two concurrent checkouts picked the same sequence number — retry with a freshly generated one.
            }
        }
    }

    /**
     * Validate stock availability and build enriched item array.
     *
     * @throws InsufficientStockException
     */
    private function prepareItems(array $items): array
    {
        $qtyByProductId = [];
        foreach ($items as $item) {
            $qtyByProductId[$item['product_id']] = ($qtyByProductId[$item['product_id']] ?? 0) + (int) $item['qty'];
        }

        $products = Product::whereIn('id', array_keys($qtyByProductId))
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        foreach ($qtyByProductId as $productId => $totalQty) {
            $product = $products->get($productId) ?? throw new ModelNotFoundException();

            if ($product->stock_qty < $totalQty) {
                throw new InsufficientStockException(
                    "Stok {$product->name} tidak cukup (sisa: {$product->stock_qty})"
                );
            }
        }

        $prepared = [];
        foreach ($items as $item) {
            $product = $products->get($item['product_id']);
            $qty     = (int) $item['qty'];

            $prepared[] = [
                'product'  => $product,
                'qty'      => $qty,
                'subtotal' => $product->sell_price * $qty,
            ];
        }

        return $prepared;
    }

    private function generateTrxNo(): string
    {
        $prefix = 'TRX' . now()->format('Ymd');
        $last   = Transaction::where('trx_no', 'like', $prefix . '%')
            ->lockForUpdate()
            ->orderByDesc('trx_no')
            ->value('trx_no');
        $seq = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
