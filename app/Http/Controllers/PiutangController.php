<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;

class PiutangController extends Controller
{
    public function index(Request $request)
    {
        $user           = auth()->user();
        $scopedBranchId = ! $user->isSuperAdmin()
            ? $user->branch_id
            : ($request->filled('branch_id') ? (int) $request->branch_id : null);

        $query = Transaction::with(['user', 'items.branch'])
            ->where('payment_status', 'Belum Lunas');

        if ($scopedBranchId) {
            $query->whereHas('items', fn($q) => $q->where('branch_id', $scopedBranchId))
                ->withSum(['items as branch_total' => fn($q) => $q->where('branch_id', $scopedBranchId)], 'subtotal');
        }

        $query->when($request->date_from, fn($q) => $q->whereDate('trx_date', '>=', $request->date_from))
              ->when($request->date_to,   fn($q) => $q->whereDate('trx_date', '<=', $request->date_to))
              ->when($request->user_id,   fn($q) => $q->where('user_id', $request->user_id));

        $totalPiutang = $scopedBranchId
            ? (clone $query)->get()->sum('branch_total')
            : (clone $query)->sum('total');

        $piutangList = $query->latest()->paginate(15)->withQueryString();

        $kasirList = User::whereHas('roles', fn($q) => $q->whereIn('name', ['kasir', 'admin-gudang', 'super-admin']))
            ->orderBy('name')->get();
        $branches = $user->isSuperAdmin()
            ? Branch::where('is_active', true)->orderBy('name')->get()
            : collect();

        return view('piutang.index', compact('piutangList', 'totalPiutang', 'kasirList', 'branches'));
    }

    public function markLunas(Request $request, Transaction $transaction)
    {
        if ($transaction->payment_status === 'Lunas') {
            return back()->with('info', 'Transaksi sudah lunas');
        }

        $request->validate(['paid_at' => 'required|date']);

        $transaction->update([
            'payment_status' => 'Lunas',
            'paid_at'        => \Carbon\Carbon::parse($request->paid_at),
        ]);

        return back()->with('success', 'Transaksi #' . $transaction->trx_no . ' berhasil dilunasi');
    }
}
