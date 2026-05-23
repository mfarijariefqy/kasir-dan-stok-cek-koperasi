<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $trxQuery = Transaction::query();
        $prodQuery = Product::query();

        if (! $user->isSuperAdmin() && $user->branch_id) {
            $trxQuery->where('branch_id', $user->branch_id);
            $prodQuery->where('branch_id', $user->branch_id);
        }

        $todaySales       = (clone $trxQuery)->whereDate('trx_date', Carbon::today())->sum('total');
        $todayTransactions = (clone $trxQuery)->whereDate('trx_date', Carbon::today())->count();

        $monthSales       = (clone $trxQuery)->whereMonth('trx_date', Carbon::now()->month)
                                ->whereYear('trx_date', Carbon::now()->year)->sum('total');
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
