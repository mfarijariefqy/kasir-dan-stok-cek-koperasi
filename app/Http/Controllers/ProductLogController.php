<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductLogController extends Controller
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
        $query = Product::with(['category', 'branch']);
        $this->branchScope($query);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('barcode', 'like', '%' . $request->search . '%');
            });
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('branch_id') && auth()->user()->isSuperAdmin()) {
            $query->where('branch_id', $request->branch_id);
        }

        $products   = $query->orderBy('name')->paginate(20)->withQueryString();
        $categories = Category::orderBy('name')->get();
        $branches   = auth()->user()->isSuperAdmin()
            ? Branch::where('is_active', true)->orderBy('name')->get()
            : collect();

        return view('stock.index', compact('products', 'categories', 'branches'));
    }

    public function create(Request $request)
    {
        $product    = null;
        $categories = Category::orderBy('name')->get();
        $branches   = auth()->user()->isSuperAdmin()
            ? Branch::where('is_active', true)->orderBy('name')->get()
            : collect();

        if ($request->filled('barcode')) {
            $q = Product::with('category');
            $this->branchScope($q);
            $product = $q->where('barcode', $request->barcode)->first();
        }

        return view('stock.in', compact('product', 'categories', 'branches'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'qty'        => 'required|integer|min:1',
            'note'       => 'nullable|string|max:255',
            'logged_at'  => 'required|date',
        ]);

        DB::transaction(function () use ($request) {
            $product = Product::findOrFail($request->product_id);

            $product->increment('stock_qty', $request->qty);

            ProductLog::create([
                'product_id' => $product->id,
                'user_id'    => auth()->id(),
                'type'       => 'IN',
                'qty'        => $request->qty,
                'note'       => $request->note,
                'logged_at'  => $request->logged_at,
            ]);
        });

        return redirect()->route('stock.index')
            ->with('success', 'Stok berhasil ditambahkan');
    }

    public function history(Request $request)
    {
        $query = ProductLog::with(['product.branch', 'user']);

        if (! auth()->user()->isSuperAdmin()) {
            $branchId = auth()->user()->branch_id;
            $query->whereHas('product', fn($q) => $q->where('branch_id', $branchId));
        }

        $query->when($request->filled('product_id'),  fn($q) => $q->where('product_id', $request->product_id))
              ->when($request->filled('type'),         fn($q) => $q->where('type', $request->type))
              ->when($request->filled('user_id'),      fn($q) => $q->where('user_id', $request->user_id))
              ->when($request->filled('date_from'),    fn($q) => $q->whereDate('logged_at', '>=', $request->date_from))
              ->when($request->filled('date_to'),      fn($q) => $q->whereDate('logged_at', '<=', $request->date_to))
              ->when($request->filled('branch_id') && auth()->user()->isSuperAdmin(),
                    fn($q) => $q->whereHas('product', fn($qq) => $qq->where('branch_id', $request->branch_id)));

        $logs = $query->orderByDesc('logged_at')->paginate(20)->withQueryString();

        $productList = Product::with('branch')
            ->when(! auth()->user()->isSuperAdmin(), fn($q) => $q->where('branch_id', auth()->user()->branch_id))
            ->orderBy('name')->get();

        $branches = auth()->user()->isSuperAdmin()
            ? Branch::where('is_active', true)->orderBy('name')->get()
            : collect();

        $userList = \App\Models\User::whereHas('productLogs')
            ->orderBy('name')->get(['id', 'name']);

        return view('stock.history', compact('logs', 'productList', 'branches', 'userList'));
    }
}
