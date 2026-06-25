<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class CustomerPortalController extends Controller
{
    private function customer()
    {
        return auth()->user()->customerProfile;
    }

    public function index()
    {
        $customer = $this->customer();

        if (! $customer) {
            abort(403, 'Profil pelanggan tidak ditemukan.');
        }

        $transactions = Transaction::where('customer_id', $customer->id)
            ->with(['items.product', 'items.branch'])
            ->latest()
            ->paginate(10);

        return view('portal.transactions.index', compact('transactions', 'customer'));
    }

    public function show(Transaction $transaction)
    {
        Gate::authorize('viewOwn', $transaction);
        $transaction->load('items.product', 'items.branch');

        return view('portal.transactions.show', compact('transaction'));
    }

    public function editQty(Transaction $transaction)
    {
        Gate::authorize('editQty', $transaction);
        $transaction->load('items.product');

        return view('portal.transactions.edit_qty', compact('transaction'));
    }

    public function updateQty(Request $request, Transaction $transaction)
    {
        Gate::authorize('editQty', $transaction);

        $request->validate([
            'quantities'   => 'required|array',
            'quantities.*' => 'required|integer|min:1',
        ]);

        $quantities = $request->input('quantities'); // [item_id => new_qty]

        DB::transaction(function () use ($transaction, $quantities) {
            $transaction->load('items.product');

            foreach ($transaction->items as $item) {
                if (! isset($quantities[$item->id])) {
                    continue;
                }

                $newQty  = (int) $quantities[$item->id];
                $oldQty  = $item->qty;
                $diff    = $newQty - $oldQty;

                if ($diff === 0) {
                    continue;
                }

                // Verify available stock allows the increase
                $product = $item->product()->lockForUpdate()->first();

                if ($diff > 0 && $product->stock_qty < $diff) {
                    throw new \Exception(
                        "Stok {$product->name} tidak cukup untuk penambahan ({$product->stock_qty} tersisa)."
                    );
                }

                // Adjust stock
                $product->decrement('stock_qty', $diff); // negative diff = increment

                // Update item
                $newSubtotal = $item->sell_price * $newQty;
                $item->update(['qty' => $newQty, 'subtotal' => $newSubtotal]);
            }

            // Recalculate transaction total
            $newTotal = $transaction->items()->sum('subtotal');
            $transaction->update(['total' => $newTotal]);
        });

        return redirect()->route('portal.transactions.show', $transaction)
            ->with('success', 'Pesanan berhasil diperbarui.');
    }
}
