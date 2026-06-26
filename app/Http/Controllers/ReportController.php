<?php

namespace App\Http\Controllers;

use App\Exports\DailyReportExport;
use App\Exports\MonthlyReportExport;
use App\Exports\StockReportExport;
use App\Models\Branch;
use App\Models\Product;
use App\Models\ProductLog;
use App\Models\Transaction;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    /**
     * Branch scope for plain Product queries (Laporan Stok) — unrelated to
     * transaction revenue attribution, a product belongs to exactly one branch.
     */
    private function applyBranchScope($query, $branchId = null)
    {
        $user = auth()->user();
        if (! $user->isSuperAdmin() && $user->branch_id) {
            $query->where('branch_id', $user->branch_id);
        } elseif ($user->isSuperAdmin() && $branchId) {
            $query->where('branch_id', $branchId);
        }
        return $query;
    }

    /**
     * Branch scope for queries joined through transaction_items — a transaction
     * can mix items from several branches, so attribution happens per item.
     */
    private function applyBranchScopeJoined($query, $branchId = null)
    {
        $user = auth()->user();
        if (! $user->isSuperAdmin() && $user->branch_id) {
            $query->where('transaction_items.branch_id', $user->branch_id);
        } elseif ($user->isSuperAdmin() && $branchId) {
            $query->where('transaction_items.branch_id', $branchId);
        }
        return $query;
    }

    private function resolveScopedBranchId(Request $request): ?int
    {
        $user = auth()->user();
        if (! $user->isSuperAdmin()) {
            return $user->branch_id;
        }
        return $request->filled('branch_id') ? (int) $request->branch_id : null;
    }

    public function daily(Request $request)
    {
        return view('reports.daily', $this->getDailyData($request));
    }

    public function monthly(Request $request)
    {
        return view('reports.monthly', $this->getMonthlyData($request));
    }

    public function stock(Request $request)
    {
        $user     = auth()->user();
        $dateFrom = $request->input('date_from', Carbon::now()->startOfMonth()->toDateString());
        $dateTo   = $request->input('date_to', Carbon::today()->toDateString());
        $catId    = $request->input('category_id');
        $branchId = $request->input('branch_id');

        $query = Product::with('category', 'branch');
        $this->applyBranchScope($query, $branchId);

        if ($catId) $query->where('category_id', $catId);

        $products = $query->orderBy('name')->get()->map(function ($product) use ($dateFrom, $dateTo) {
            $in  = ProductLog::where('product_id', $product->id)->where('type', 'IN')
                ->whereBetween('logged_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])->sum('qty');
            $out = ProductLog::where('product_id', $product->id)->where('type', 'OUT')
                ->whereBetween('logged_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])->sum('qty');

            $product->stok_masuk  = $in;
            $product->stok_keluar = $out;
            $product->stok_akhir  = $product->stockAt($dateTo);
            $product->stok_awal   = $product->stok_akhir - $in + $out;
            return $product;
        });

        $categories = \App\Models\Category::orderBy('name')->get();
        $branches   = $user->isSuperAdmin()
            ? Branch::where('is_active', true)->orderBy('name')->get()
            : collect();

        return view('reports.stock', compact('products', 'categories', 'branches', 'dateFrom', 'dateTo'));
    }

    public function piutang(Request $request)
    {
        $user           = auth()->user();
        $scopedBranchId = $this->resolveScopedBranchId($request);

        $query = Transaction::with(['user', 'items.branch'])->where('payment_status', 'Belum Lunas');

        if ($scopedBranchId) {
            $query->whereHas('items', fn($q) => $q->where('branch_id', $scopedBranchId))
                ->withSum(['items as branch_total' => fn($q) => $q->where('branch_id', $scopedBranchId)], 'subtotal');
        }

        $query->when($request->date_from, fn($q) => $q->whereDate('trx_date', '>=', $request->date_from))
              ->when($request->date_to,   fn($q) => $q->whereDate('trx_date', '<=', $request->date_to));

        $piutangList  = $query->latest()->get();
        $totalPiutang = $scopedBranchId ? $piutangList->sum('branch_total') : $piutangList->sum('total');

        $branches = $user->isSuperAdmin()
            ? Branch::where('is_active', true)->orderBy('name')->get()
            : collect();

        return view('reports.piutang', compact('piutangList', 'totalPiutang', 'branches'));
    }

    public function exportDaily(Request $request)
    {
        $format = $request->input('format', 'excel');
        if ($format === 'pdf') {
            $data = $this->getDailyData($request);
            $pdf  = Pdf::loadView('reports.pdf.daily', $data)->setPaper('a4', 'landscape');
            return $pdf->download('laporan-harian-' . $data['date'] . '.pdf');
        }
        return Excel::download(new DailyReportExport($request), 'laporan-harian-' . $request->input('date', today()->toDateString()) . '.xlsx');
    }

    public function exportMonthly(Request $request)
    {
        $format = $request->input('format', 'excel');
        if ($format === 'pdf') {
            $data = $this->getMonthlyData($request);
            $pdf  = Pdf::loadView('reports.pdf.monthly', $data)->setPaper('a4', 'landscape');
            return $pdf->download('laporan-bulanan-' . $data['month'] . '.pdf');
        }
        return Excel::download(new MonthlyReportExport($request), 'laporan-bulanan-' . $request->input('month', now()->format('Y-m')) . '.xlsx');
    }

    public function exportStock(Request $request)
    {
        $format = $request->input('format', 'excel');
        if ($format === 'pdf') {
            $data = $this->getStockData($request);
            $pdf  = Pdf::loadView('reports.pdf.stock', $data)->setPaper('a4', 'landscape');
            return $pdf->download('laporan-stok-' . $data['dateFrom'] . '.pdf');
        }
        return Excel::download(new StockReportExport($request), 'laporan-stok.xlsx');
    }

    private function getDailyData(Request $request): array
    {
        $user           = auth()->user();
        $date           = $request->input('date', today()->toDateString());
        $branchId       = $request->input('branch_id');
        $userId         = $request->input('user_id');
        $customerName   = $request->input('customer_name');
        $scopedBranchId = $this->resolveScopedBranchId($request);

        $query = Transaction::with(['items.product', 'items.branch', 'user'])->whereDate('trx_date', $date);
        if ($scopedBranchId) {
            $query->whereHas('items', fn($q) => $q->where('branch_id', $scopedBranchId));
        }
        if ($userId)       $query->where('user_id', $userId);
        if ($customerName) $query->where('customer_name', 'like', '%' . $customerName . '%');

        // Each transaction's display figures are that branch's portion of its items
        // when scoped to one branch, or the full transaction when viewing all branches.
        $transactions = $query->get()->map(function ($t) use ($scopedBranchId) {
            $items = $scopedBranchId ? $t->items->where('branch_id', $scopedBranchId) : $t->items;

            $t->display_total  = $scopedBranchId ? (float) $items->sum('subtotal') : (float) $t->total;
            $t->display_hpp    = (float) $items->sum(fn($i) => $i->buy_price * $i->qty);
            $t->display_profit = $t->display_total - $t->display_hpp;

            return $t;
        });

        $totalSales        = $transactions->sum('display_total');
        $totalTransactions = $transactions->count();
        $totalLunas        = $transactions->where('payment_status', 'Lunas')->sum('display_total');
        $totalTempo        = $transactions->where('payment_status', 'Belum Lunas')->sum('display_total');
        $totalHPP          = $transactions->sum('display_hpp');
        $totalProfit       = $totalSales - $totalHPP;

        $productSummary = $this->applyBranchScopeJoined(
            DB::table('transaction_items')
                ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
                ->join('products', 'transaction_items.product_id', '=', 'products.id')
                ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
                ->whereDate('transactions.trx_date', $date),
            $branchId
        )
            ->when($userId,       fn($q) => $q->where('transactions.user_id', $userId))
            ->when($customerName, fn($q) => $q->where('transactions.customer_name', 'like', '%' . $customerName . '%'))
            ->selectRaw('products.id, products.name as product_name, products.barcode, products.unit,
                categories.name as category_name,
                SUM(transaction_items.qty) as total_qty,
                SUM(transaction_items.buy_price * transaction_items.qty) as total_hpp,
                SUM(transaction_items.subtotal) as total_revenue,
                SUM(CASE WHEN transactions.payment_status = "Lunas" THEN transaction_items.subtotal ELSE 0 END) as revenue_lunas,
                SUM(CASE WHEN transactions.payment_status = "Belum Lunas" THEN transaction_items.subtotal ELSE 0 END) as revenue_tempo')
            ->groupBy('products.id', 'products.name', 'products.barcode', 'products.unit', 'categories.name')
            ->orderByDesc('total_revenue')
            ->get()
            ->map(function ($p) {
                $p->total_profit = $p->total_revenue - $p->total_hpp;
                $p->margin = $p->total_revenue > 0 ? $p->total_profit / $p->total_revenue * 100 : 0;
                return $p;
            });

        $branchName = $user->isSuperAdmin() && $branchId
            ? Branch::find($branchId)?->name : null;

        $kasirList = User::whereHas('roles', fn($q) => $q->whereIn('name', ['kasir', 'admin-gudang', 'super-admin']))
            ->orderBy('name')->get();
        $branches  = $user->isSuperAdmin()
            ? Branch::where('is_active', true)->orderBy('name')->get()
            : collect();

        return compact(
            'transactions', 'date', 'totalSales', 'totalTransactions',
            'totalLunas', 'totalTempo', 'totalHPP', 'totalProfit',
            'productSummary', 'branchName', 'kasirList', 'branches'
        );
    }

    private function getMonthlyData(Request $request): array
    {
        $user         = auth()->user();
        $month        = $request->input('month', now()->format('Y-m'));
        $branchId     = $request->input('branch_id');
        $userId       = $request->input('user_id');
        $customerName = $request->input('customer_name');
        $start        = Carbon::parse($month . '-01')->startOfMonth();
        $end          = Carbon::parse($month . '-01')->endOfMonth();

        $itemsQuery = $this->applyBranchScopeJoined(
            DB::table('transaction_items')
                ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
                ->whereBetween('transactions.trx_date', [$start, $end]),
            $branchId
        )
            ->when($userId,       fn($q) => $q->where('transactions.user_id', $userId))
            ->when($customerName, fn($q) => $q->where('transactions.customer_name', 'like', '%' . $customerName . '%'));

        $dailySummary = (clone $itemsQuery)
            ->selectRaw('DATE(transactions.trx_date) as date,
                COUNT(DISTINCT transactions.id) as count,
                SUM(transaction_items.subtotal) as total,
                SUM(CASE WHEN transactions.payment_status = "Lunas" THEN transaction_items.subtotal ELSE 0 END) as lunas,
                SUM(CASE WHEN transactions.payment_status = "Belum Lunas" THEN transaction_items.subtotal ELSE 0 END) as tempo,
                SUM(transaction_items.buy_price * transaction_items.qty) as hpp')
            ->groupBy('date')->orderBy('date')->get()
            ->map(function ($row) {
                $row->total  = (float) $row->total;
                $row->hpp    = (float) $row->hpp;
                $row->profit = $row->total - $row->hpp;
                return $row;
            });

        $totalSales        = $dailySummary->sum('total');
        $totalTransactions = $dailySummary->sum('count');
        $totalPiutang      = $dailySummary->sum('tempo');
        $totalHPP          = $dailySummary->sum('hpp');
        $totalProfit       = $totalSales - $totalHPP;

        $topProducts = $this->applyBranchScopeJoined(
            DB::table('transaction_items')
                ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
                ->join('products', 'transaction_items.product_id', '=', 'products.id')
                ->whereBetween('transactions.trx_date', [$start, $end]),
            $branchId
        )
            ->select('products.name', DB::raw('SUM(transaction_items.qty) as total_qty'),
                DB::raw('SUM(transaction_items.subtotal) as total_sales'))
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_sales')->limit(10)->get();

        $productSummary = $this->applyBranchScopeJoined(
            DB::table('transaction_items')
                ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
                ->join('products', 'transaction_items.product_id', '=', 'products.id')
                ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
                ->whereBetween('transactions.trx_date', [$start, $end]),
            $branchId
        )
            ->when($userId,       fn($q) => $q->where('transactions.user_id', $userId))
            ->when($customerName, fn($q) => $q->where('transactions.customer_name', 'like', '%' . $customerName . '%'))
            ->selectRaw('products.name as product_name, products.unit, categories.name as category_name,
                SUM(transaction_items.qty) as total_qty,
                SUM(transaction_items.buy_price * transaction_items.qty) as total_hpp,
                SUM(transaction_items.subtotal) as total_revenue,
                SUM(CASE WHEN transactions.payment_status = "Lunas" THEN transaction_items.subtotal ELSE 0 END) as revenue_lunas,
                SUM(CASE WHEN transactions.payment_status = "Belum Lunas" THEN transaction_items.subtotal ELSE 0 END) as revenue_tempo')
            ->groupBy('products.id', 'products.name', 'products.unit', 'categories.name')
            ->orderByDesc('total_revenue')
            ->get()
            ->map(function ($p) {
                $p->total_profit = $p->total_revenue - $p->total_hpp;
                $p->margin = $p->total_revenue > 0 ? $p->total_profit / $p->total_revenue * 100 : 0;
                return $p;
            });

        $chartLabels = $dailySummary->pluck('date')->map(fn($d) => Carbon::parse($d)->format('d'))->toJson();
        $chartData   = $dailySummary->pluck('total')->toJson();

        $branchName = $user->isSuperAdmin() && $branchId
            ? Branch::find($branchId)?->name : null;

        $kasirList = User::whereHas('roles', fn($q) => $q->whereIn('name', ['kasir', 'admin-gudang', 'super-admin']))
            ->orderBy('name')->get();
        $branches  = $user->isSuperAdmin()
            ? Branch::where('is_active', true)->orderBy('name')->get()
            : collect();

        return compact(
            'dailySummary', 'month', 'totalSales', 'totalTransactions',
            'totalPiutang', 'totalHPP', 'totalProfit',
            'productSummary', 'topProducts', 'chartLabels', 'chartData',
            'branchName', 'kasirList', 'branches'
        );
    }

    private function getStockData(Request $request): array
    {
        $dateFrom = $request->input('date_from', now()->startOfMonth()->toDateString());
        $dateTo   = $request->input('date_to', today()->toDateString());
        $query    = Product::with('category', 'branch');
        $this->applyBranchScope($query, $request->branch_id);
        $products = $query->orderBy('name')->get()->map(function ($p) use ($dateFrom, $dateTo) {
            $in  = ProductLog::where('product_id', $p->id)->where('type', 'IN')
                ->whereBetween('logged_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])->sum('qty');
            $out = ProductLog::where('product_id', $p->id)->where('type', 'OUT')
                ->whereBetween('logged_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])->sum('qty');
            $p->stok_masuk = $in; $p->stok_keluar = $out;
            $p->stok_akhir = $p->stockAt($dateTo); $p->stok_awal = $p->stok_akhir - $in + $out;
            return $p;
        });
        return ['products' => $products, 'dateFrom' => $dateFrom, 'dateTo' => $dateTo];
    }
}
