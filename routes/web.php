<?php

use App\Http\Controllers\BranchController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerPortalController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\PiutangController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductLogController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

require __DIR__ . '/auth.php';

// ── Portal Pelanggan ──────────────────────────────────────────────────────────
Route::middleware(['auth', 'ensure.customer'])->prefix('portal')->name('portal.')->group(function () {
    Route::get('/transactions', [CustomerPortalController::class, 'index'])
        ->name('transactions.index');
    Route::get('/transactions/{transaction}', [CustomerPortalController::class, 'show'])
        ->name('transactions.show');
    Route::get('/transactions/{transaction}/edit-qty', [CustomerPortalController::class, 'editQty'])
        ->name('transactions.editQty');
    Route::patch('/transactions/{transaction}/update-qty', [CustomerPortalController::class, 'updateQty'])
        ->name('transactions.updateQty');
});

Route::middleware(['auth', 'redirect.if.customer'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('permission:view-dashboard')
        ->name('dashboard');

    // Profile
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });

    // Transaksi
    Route::prefix('transactions')->name('transactions.')->group(function () {
        Route::get('/', [TransactionController::class, 'index'])
            ->middleware('permission:view-transactions|manage-transactions')
            ->name('index');
        Route::get('/create', [TransactionController::class, 'create'])
            ->middleware('permission:manage-transactions')
            ->name('create');
        Route::post('/', [TransactionController::class, 'store'])
            ->middleware('permission:manage-transactions')
            ->name('store');
        Route::get('/{transaction}', [TransactionController::class, 'show'])
            ->middleware('permission:view-transactions|manage-transactions')
            ->name('show');
        Route::get('/{transaction}/receipt', [TransactionController::class, 'receipt'])
            ->middleware('permission:view-transactions|manage-transactions')
            ->name('receipt');
        Route::patch('/{transaction}/bayar', [TransactionController::class, 'bayar'])
            ->middleware('permission:manage-piutang')
            ->name('bayar');
    });

    // Piutang
    Route::prefix('piutang')->name('piutang.')->middleware('permission:view-piutang|manage-piutang')->group(function () {
        Route::get('/', [PiutangController::class, 'index'])->name('index');
        Route::patch('/{transaction}/lunas', [PiutangController::class, 'markLunas'])
            ->middleware('permission:manage-piutang')
            ->name('lunas');
    });

    // Product barcode search — accessible to kasir (manage-transactions) and admins (manage-products)
    Route::get('/api/products/search', [ProductController::class, 'search'])
        ->middleware('permission:manage-transactions|manage-products')
        ->name('products.search');

    // Customer autocomplete — accessible to kasir and anyone managing customers
    Route::get('/api/customers/search', [CustomerController::class, 'search'])
        ->middleware('permission:manage-transactions|manage-customers')
        ->name('customers.search');

    // Produk
    Route::middleware('permission:manage-products')->group(function () {
        Route::get('/products/import', [ProductController::class, 'importForm'])->name('products.import.form');
        Route::post('/products/import', [ProductController::class, 'import'])->name('products.import');
        Route::get('/products/import/template', [ProductController::class, 'importTemplate'])->name('products.import.template');
        Route::resource('products', ProductController::class);
    });

    // Stok Barang
    Route::prefix('stock')->name('stock.')->middleware('permission:view-stock|manage-stock')->group(function () {
        Route::get('/', [ProductLogController::class, 'index'])->name('index');
        Route::get('/in', [ProductLogController::class, 'create'])
            ->middleware('permission:manage-stock')
            ->name('in');
        Route::post('/in', [ProductLogController::class, 'store'])
            ->middleware('permission:manage-stock')
            ->name('store');
        Route::get('/history', [ProductLogController::class, 'history'])->name('history');
    });

    // Laporan
    Route::middleware('permission:view-reports')->prefix('reports')->name('reports.')->group(function () {
        Route::get('/daily', [ReportController::class, 'daily'])->name('daily');
        Route::get('/monthly', [ReportController::class, 'monthly'])->name('monthly');
        Route::get('/stock', [ReportController::class, 'stock'])->name('stock');
        Route::get('/piutang', [ReportController::class, 'piutang'])->name('piutang');
        Route::get('/daily/export', [ReportController::class, 'exportDaily'])->name('daily.export');
        Route::get('/monthly/export', [ReportController::class, 'exportMonthly'])->name('monthly.export');
        Route::get('/stock/export', [ReportController::class, 'exportStock'])->name('stock.export');
    });

    // Pengaturan (super-admin only)
    Route::middleware('permission:manage-branches')->group(function () {
        Route::resource('branches', BranchController::class);
    });

    Route::middleware('permission:manage-categories')->group(function () {
        Route::resource('categories', CategoryController::class);
    });

    // Pelanggan Tetap
    Route::middleware('permission:manage-customers')->group(function () {
        Route::resource('customers', CustomerController::class);
    });

    // Master Satuan
    Route::middleware('permission:manage-units')->group(function () {
        Route::resource('units', UnitController::class)->except('show');
    });

    // Users
    Route::middleware('permission:manage-users')->group(function () {
        Route::resource('users', UserController::class);
    });

    // Roles & Permissions
    Route::middleware('permission:manage-roles')->group(function () {
        Route::resource('roles', RoleController::class)->only(['index', 'edit', 'update']);
    });
});
