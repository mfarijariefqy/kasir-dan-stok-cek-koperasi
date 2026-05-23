<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Models\Product;
use App\Models\ProductLog;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
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
        ?int $branchId,
        array $items,
        string $paymentMethod,
        ?string $customerName,
        ?int $customerId = null
    ): Transaction {
        return DB::transaction(function () use ($user, $branchId, $items, $paymentMethod, $customerName, $customerId) {
            $paymentStatus = $paymentMethod === 'Cash' ? 'Lunas' : 'Belum Lunas';
            $preparedItems = $this->prepareItems($items);
            $total         = array_sum(array_column($preparedItems, 'subtotal'));

            $transaction = Transaction::create([
                'trx_no'         => $this->generateTrxNo(),
                'trx_date'       => now()->toDateString(),
                'user_id'        => $user->id,
                'branch_id'      => $branchId,
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
    }

    /**
     * Validate stock availability and build enriched item array.
     *
     * @throws InsufficientStockException
     */
    private function prepareItems(array $items): array
    {
        $prepared = [];

        foreach ($items as $item) {
            $product = Product::findOrFail($item['product_id']);
            $qty     = (int) $item['qty'];

            if ($product->stock_qty < $qty) {
                throw new InsufficientStockException(
                    "Stok {$product->name} tidak cukup (sisa: {$product->stock_qty})"
                );
            }

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
            ->orderByDesc('trx_no')
            ->value('trx_no');
        $seq = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
