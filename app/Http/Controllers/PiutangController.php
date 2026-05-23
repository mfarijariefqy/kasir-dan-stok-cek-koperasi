<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;

class PiutangController extends Controller
{
    private function branchScope($query)
    {
        $user = auth()->user();
        if (! $user->isSuperAdmin() && $user->branch_id) {
            $query->where('branch_id', $user->branch_id);
        }
        return $query;
    }

    public function index(Request $request)
    {
        $query = Transaction::with(['user', 'branch'])
            ->where('payment_status', 'Belum Lunas');

        $this->branchScope($query);

        $query->when($request->date_from, fn($q) => $q->whereDate('trx_date', '>=', $request->date_from))
              ->when($request->date_to,   fn($q) => $q->whereDate('trx_date', '<=', $request->date_to))
              ->when($request->user_id,   fn($q) => $q->where('user_id', $request->user_id))
              ->when($request->filled('branch_id') && auth()->user()->isSuperAdmin(),
                    fn($q) => $q->where('branch_id', $request->branch_id));

        $totalPiutang = (clone $query)->sum('total');
        $piutangList  = $query->latest()->paginate(15)->withQueryString();

        $kasirList = User::whereHas('roles', fn($q) => $q->whereIn('name', ['kasir', 'admin-gudang', 'super-admin']))
            ->orderBy('name')->get();
        $branches = auth()->user()->isSuperAdmin()
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
