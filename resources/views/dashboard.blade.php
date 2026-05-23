@extends('layouts.app')

@section('title', 'Dashboard - Kasir Koperasi')

@section('page-title', 'Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@push('styles')
<style>
    .stat-card {
        border-radius: 16px !important;
        border: none !important;
        overflow: hidden;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        margin-bottom: 20px;
        cursor: default;
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 35px rgba(0,0,0,0.15) !important;
    }
    .stat-card .stat-icon {
        width: 56px;
        height: 56px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        flex-shrink: 0;
    }
    .stat-card .stat-label {
        font-size: 0.78rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        opacity: 0.75;
    }
    .stat-card .stat-value {
        font-size: 1.55rem;
        font-weight: 700;
        line-height: 1.2;
        margin-top: 2px;
    }
    .stat-card .stat-body {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 20px 22px;
    }

    /* Stat card color variants */
    .stat-blue  { background: linear-gradient(135deg, #1565C0, #1E88E5) !important; color: #fff; }
    .stat-green { background: linear-gradient(135deg, #2E7D32, #43A047) !important; color: #fff; }
    .stat-amber { background: linear-gradient(135deg, #E65100, #FF8F00) !important; color: #fff; }
    .stat-red   { background: linear-gradient(135deg, #B71C1C, #E53935) !important; color: #fff; }

    .stat-blue  .stat-icon { background: rgba(255,255,255,0.2); color: #fff; }
    .stat-green .stat-icon { background: rgba(255,255,255,0.2); color: #fff; }
    .stat-amber .stat-icon { background: rgba(255,255,255,0.2); color: #fff; }
    .stat-red   .stat-icon { background: rgba(255,255,255,0.2); color: #fff; }

    /* Table transaction */
    .trx-no {
        font-weight: 600;
        color: #3E2723;
        font-size: 0.82rem;
        font-family: monospace;
    }
    .trx-date {
        color: #888;
        font-size: 0.82rem;
    }
    .trx-amount {
        font-weight: 700;
        color: #2E7D32;
    }

    /* Quick stat info boxes */
    .quick-stat-box {
        background: #fff;
        border-radius: 12px;
        padding: 14px 18px;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 14px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        transition: all 0.25s ease;
    }
    .quick-stat-box:hover {
        transform: translateX(5px);
        box-shadow: 0 4px 16px rgba(0,0,0,0.1);
    }
    .quick-stat-icon {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
        flex-shrink: 0;
    }
    .quick-stat-label {
        font-size: 0.78rem;
        color: #888;
        font-weight: 500;
    }
    .quick-stat-value {
        font-size: 1.2rem;
        font-weight: 700;
        color: #3E2723;
        line-height: 1.2;
    }

    /* Greeting banner */
    .greeting-banner {
        background: linear-gradient(135deg, #3E2723, #6F4E37);
        border-radius: 16px;
        padding: 22px 26px;
        margin-bottom: 22px;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-shadow: 0 6px 20px rgba(62,39,35,0.3);
    }
    .greeting-banner .greeting-text h4 {
        font-size: 1.15rem;
        font-weight: 700;
        margin: 0 0 4px;
    }
    .greeting-banner .greeting-text p {
        font-size: 0.83rem;
        opacity: 0.75;
        margin: 0;
    }
    .greeting-banner .greeting-icon {
        font-size: 3rem;
        opacity: 0.25;
    }
</style>
@endpush

@section('content')
    <!-- Greeting Banner -->
    <div class="greeting-banner">
        <div class="greeting-text">
            <h4>Selamat Datang, {{ auth()->user()->name }}!</h4>
            <p><i class="fas fa-calendar-alt mr-1"></i> {{ now()->translatedFormat('l, d F Y') }}</p>
        </div>
        <div class="greeting-icon">
            <i class="fas fa-store"></i>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="row">
        <div class="col-lg-3 col-sm-6">
            <div class="card stat-card stat-blue shadow">
                <div class="stat-body">
                    <div class="stat-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div>
                        <div class="stat-label">Penjualan Hari Ini</div>
                        <div class="stat-value">Rp {{ number_format($todaySales, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6">
            <div class="card stat-card stat-green shadow">
                <div class="stat-body">
                    <div class="stat-icon">
                        <i class="fas fa-receipt"></i>
                    </div>
                    <div>
                        <div class="stat-label">Transaksi Hari Ini</div>
                        <div class="stat-value">{{ $todayTransactions }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6">
            <div class="card stat-card stat-amber shadow">
                <div class="stat-body">
                    <div class="stat-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div>
                        <div class="stat-label">Penjualan Bulan Ini</div>
                        <div class="stat-value">Rp {{ number_format($monthSales, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6">
            <div class="card stat-card stat-red shadow">
                <div class="stat-body">
                    <div class="stat-icon">
                        <i class="fas fa-box"></i>
                    </div>
                    <div>
                        <div class="stat-label">Produk Aktif</div>
                        <div class="stat-value">{{ $activeProducts }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Transactions -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-clock mr-2 text-muted"></i>Transaksi Terbaru</h3>
                    @can('view-transactions')
                        <div class="card-tools">
                            <a href="{{ route('transactions.index') }}" class="btn btn-sm btn-secondary">
                                Lihat Semua <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                    @endcan
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>No. Transaksi</th>
                                <th>Tanggal</th>
                                <th>Kasir</th>
                                <th>Total</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentTransactions as $transaction)
                                <tr>
                                    <td><span class="trx-no">{{ $transaction->trx_no }}</span></td>
                                    <td><span class="trx-date">{{ $transaction->trx_date->format('d/m/Y') }}</span></td>
                                    <td>{{ $transaction->user->name }}</td>
                                    <td><span class="trx-amount">Rp {{ number_format($transaction->total, 0, ',', '.') }}</span></td>
                                    <td class="text-center">
                                        <a href="{{ route('transactions.show', $transaction) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <i class="fas fa-inbox fa-2x text-muted mb-2 d-block"></i>
                                        <span class="text-muted">Belum ada transaksi hari ini</span>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-chart-pie mr-2 text-muted"></i>Statistik Cepat</h3>
                </div>
                <div class="card-body">
                    <div class="quick-stat-box">
                        <div class="quick-stat-icon" style="background:#E3F2FD; color:#1565C0;">
                            <i class="fas fa-box"></i>
                        </div>
                        <div>
                            <div class="quick-stat-label">Total Produk</div>
                            <div class="quick-stat-value">{{ $totalProducts }}</div>
                        </div>
                    </div>

                    <div class="quick-stat-box">
                        <div class="quick-stat-icon" style="background:#E8F5E9; color:#2E7D32;">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div>
                            <div class="quick-stat-label">Produk Aktif</div>
                            <div class="quick-stat-value">{{ $activeProducts }}</div>
                        </div>
                    </div>

                    @if($lowStockProducts > 0)
                    <div class="quick-stat-box" style="border-left: 3px solid #FF8F00;">
                        <div class="quick-stat-icon" style="background:#FFF8E1; color:#E65100;">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div>
                            <div class="quick-stat-label">Stok Produk Menipis</div>
                            <div class="quick-stat-value" style="color:#E65100;">{{ $lowStockProducts }} produk</div>
                        </div>
                    </div>
                    @else
                    <div class="quick-stat-box" style="border-left: 3px solid #43A047;">
                        <div class="quick-stat-icon" style="background:#E8F5E9; color:#2E7D32;">
                            <i class="fas fa-warehouse"></i>
                        </div>
                        <div>
                            <div class="quick-stat-label">Status Stok Produk</div>
                            <div class="quick-stat-value" style="color:#2E7D32; font-size:0.95rem;">Semua Aman</div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
