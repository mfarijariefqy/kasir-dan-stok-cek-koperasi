<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user     = auth()->user();
        $branchId = ! $user->isSuperAdmin() ? $user->branch_id : null;

        $trxQuery  = Transaction::query();
        $prodQuery = Product::query();

        if ($branchId) {
            $trxQuery->whereHas('items', fn($q) => $q->where('branch_id', $branchId));
            $prodQuery->where('branch_id', $branchId);
        }

        if ($branchId) {
            // Scoped to one branch: only that branch's portion of each transaction counts as "its" revenue.
            $branchItems = fn() => DB::table('transaction_items')
                ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
                ->where('transaction_items.branch_id', $branchId);

            $todaySales = (float) $branchItems()
                ->whereDate('transactions.trx_date', Carbon::today())
                ->sum('transaction_items.subtotal');
            $monthSales = (float) $branchItems()
                ->whereMonth('transactions.trx_date', Carbon::now()->month)
                ->whereYear('transactions.trx_date', Carbon::now()->year)
                ->sum('transaction_items.subtotal');
        } else {
            $todaySales = (clone $trxQuery)->whereDate('trx_date', Carbon::today())->sum('total');
            $monthSales = (clone $trxQuery)->whereMonth('trx_date', Carbon::now()->month)
                                ->whereYear('trx_date', Carbon::now()->year)->sum('total');
        }

        $todayTransactions = (clone $trxQuery)->whereDate('trx_date', Carbon::today())->count();
        $monthTransactions = (clone $trxQuery)->whereMonth('trx_date', Carbon::now()->month)
                                ->whereYear('trx_date', Carbon::now()->year)->count();

        $activeProducts  = (clone $prodQuery)->where('is_active', true)->count();
        $totalProducts   = (clone $prodQuery)->count();
        $lowStockProducts = (clone $prodQuery)->where('is_active', true)->where('stock_qty', '<=', 5)->count();

        $recentTransactions = (clone $trxQuery)->with('user')
            ->latest('trx_date')->limit(5)->get();

        return view('dashboard', compact(
            'todaySales', 'todayTransactions',
            'monthSales', 'monthTransactions',
            'activeProducts', 'totalProducts', 'lowStockProducts',
            'recentTransactions'
        ));
    }
}
