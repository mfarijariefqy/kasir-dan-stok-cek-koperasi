<?php

namespace App\Http\Controllers;

use App\Exceptions\InsufficientStockException;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Transaction;
use App\Models\User;
use App\Services\TransactionService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function __construct(private TransactionService $transactionService) {}

    public function index(Request $request)
    {
        $user  = auth()->user();
        $query = Transaction::with(['user', 'branch']);

        if (! $user->isSuperAdmin() && $user->branch_id) {
            $query->where('branch_id', $user->branch_id);
        }

        $query->when($request->date_from,      fn($q) => $q->whereDate('trx_date', '>=', $request->date_from))
              ->when($request->date_to,        fn($q) => $q->whereDate('trx_date', '<=', $request->date_to))
              ->when($request->payment_method, fn($q) => $q->where('payment_method', $request->payment_method))
              ->when($request->payment_status, fn($q) => $q->where('payment_status', $request->payment_status))
              ->when($request->user_id,        fn($q) => $q->where('user_id', $request->user_id));

        $grandTotal   = (clone $query)->sum('total');
        $totalCount   = (clone $query)->count();
        $transactions = $query->latest()->paginate(15)->withQueryString();

        $kasirList = User::whereHas('roles', fn($q) => $q->whereIn('name', ['kasir', 'admin-gudang', 'super-admin']))
            ->orderBy('name')->get();
        $branches  = $user->isSuperAdmin()
            ? Branch::where('is_active', true)->orderBy('name')->get()
            : collect();

        return view('transactions.index', compact('transactions', 'grandTotal', 'totalCount', 'kasirList', 'branches'));
    }

    public function create()
    {
        $user      = auth()->user();
        $branches  = Branch::where('is_active', true)->orderBy('name')->get();
        $customers = Customer::active()->orderBy('name')->get(['id', 'name', 'phone']);

        $defaultBranchId = session('default_branch_id', $user->branch_id);

        return view('transactions.create', compact('branches', 'defaultBranchId', 'customers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'items'              => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty'        => 'required|integer|min:1',
            'customer_id'        => 'nullable|exists:customers,id',
            'customer_name'      => 'nullable|string|max:255',
            'payment_method'     => 'required|in:Cash,Tempo',
            'branch_id'          => 'nullable|exists:branches,id',
        ]);

        $user     = auth()->user();
        $branchId = $request->filled('branch_id') ? (int) $request->branch_id : $user->branch_id;

        if ($request->input('save_default_branch') == '1' && $branchId) {
            session(['default_branch_id' => $branchId]);
        }

        try {
            $transaction = $this->transactionService->create(
                user: $user,
                branchId: $branchId,
                items: $request->items,
                paymentMethod: $request->payment_method,
                customerName: $request->customer_name,
                customerId: $request->filled('customer_id') ? (int) $request->customer_id : null,
            );
        } catch (InsufficientStockException $e) {
            return back()->withErrors(['items' => $e->getMessage()])->withInput();
        }

        return redirect()->route('transactions.show', $transaction)
            ->with('success', 'Transaksi berhasil disimpan');
    }

    public function show(Transaction $transaction)
    {
        $transaction->load('items.product.branch', 'user', 'branch');
        return view('transactions.show', compact('transaction'));
    }

    public function receipt(Transaction $transaction)
    {
        $transaction->load('items.product', 'user', 'branch');
        return view('transactions.receipt', compact('transaction'));
    }

    public function bayar(Request $request, Transaction $transaction)
    {
        if ($transaction->payment_status === 'Lunas') {
            return back()->with('info', 'Transaksi sudah lunas');
        }

        $request->validate(['paid_at' => 'required|date']);

        $transaction->update([
            'payment_status' => 'Lunas',
            'paid_at'        => Carbon::parse($request->paid_at),
        ]);

        return back()->with('success', 'Transaksi #' . $transaction->trx_no . ' berhasil ditandai lunas');
    }
}
