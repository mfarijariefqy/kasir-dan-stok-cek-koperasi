@extends('layouts.app')

@section('title', 'Laporan Harian')
@section('page-title', 'Laporan Harian')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Laporan Harian</li>
@endsection

@section('content')
<!-- Filter -->
<div class="card">
    <div class="card-body">
        <form method="GET" class="row align-items-end">
            <div class="col-md-2 mb-2">
                <label class="small mb-1">Tanggal</label>
                <input type="date" name="date" class="form-control form-control-sm" value="{{ $date }}">
            </div>
            @if(auth()->user()->isSuperAdmin())
            <div class="col-md-2 mb-2">
                <label class="small mb-1">Cabang</label>
                <select name="branch_id" class="form-control form-control-sm">
                    <option value="">Semua</option>
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}" {{ request('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="col-md-2 mb-2">
                <label class="small mb-1">Kasir</label>
                <select name="user_id" class="form-control form-control-sm">
                    <option value="">Semua</option>
                    @foreach($kasirList as $u)
                        <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <label class="small mb-1">Pelanggan</label>
                <input type="text" name="customer_name" class="form-control form-control-sm"
                    placeholder="Nama pelanggan..." value="{{ request('customer_name') }}">
            </div>
            <div class="col-md-3 mb-2 d-flex align-items-end gap-1">
                <button type="submit" class="btn btn-info btn-sm mr-1">
                    <i class="fas fa-search mr-1"></i> Filter
                </button>
                <a href="{{ route('reports.daily.export', array_merge(request()->all(), ['format' => 'excel'])) }}"
                    class="btn btn-success btn-sm mr-1">
                    <i class="fas fa-file-excel mr-1"></i> Excel
                </a>
                <a href="{{ route('reports.daily.export', array_merge(request()->all(), ['format' => 'pdf'])) }}"
                    class="btn btn-danger btn-sm">
                    <i class="fas fa-file-pdf mr-1"></i> PDF
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Summary -->
<div class="row">
    <div class="col-md-3">
        <div class="info-box bg-primary">
            <span class="info-box-icon"><i class="fas fa-receipt"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Transaksi</span>
                <span class="info-box-number">{{ $totalTransactions }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box bg-success">
            <span class="info-box-icon"><i class="fas fa-money-bill-wave"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Penjualan</span>
                <span class="info-box-number">Rp {{ number_format($totalSales, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box bg-info">
            <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Lunas</span>
                <span class="info-box-number">Rp {{ number_format($totalLunas, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box bg-warning">
            <span class="info-box-icon"><i class="fas fa-clock"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Belum Lunas (Tempo)</span>
                <span class="info-box-number">Rp {{ number_format($totalTempo, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-4">
        <div class="info-box bg-secondary">
            <span class="info-box-icon"><i class="fas fa-shopping-cart"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total HPP (Modal)</span>
                <span class="info-box-number">Rp {{ number_format($totalHPP, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="info-box bg-teal">
            <span class="info-box-icon"><i class="fas fa-chart-line"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Keuntungan / Laba</span>
                <span class="info-box-number">Rp {{ number_format($totalProfit, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="info-box bg-olive">
            <span class="info-box-icon"><i class="fas fa-percent"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Margin</span>
                <span class="info-box-number">{{ $totalSales > 0 ? number_format($totalProfit / $totalSales * 100, 1) : 0 }}%</span>
            </div>
        </div>
    </div>
</div>

<!-- Detail -->
<div class="card card-primary card-outline card-outline-tabs">
    <div class="card-header p-0 border-bottom-0">
        <ul class="nav nav-tabs" id="dailyReportTab">
            <li class="nav-item">
                <a class="nav-link active" data-toggle="tab" href="#tab-transaksi">
                    <i class="fas fa-receipt mr-1"></i> Per Transaksi
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#tab-produk">
                    <i class="fas fa-box mr-1"></i> Per Produk
                </a>
            </li>
        </ul>
    </div>
    <div class="card-body p-0">
        <div class="tab-content">

            {{-- Tab: Per Transaksi --}}
            <div class="tab-pane fade show active" id="tab-transaksi">
                <div class="p-2 bg-light border-bottom">
                    <small class="text-muted">Detail Transaksi — {{ \Carbon\Carbon::parse($date)->translatedFormat('d F Y') }}</small>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>No. Transaksi</th>
                                <th>Kasir</th>
                                @if(auth()->user()->isSuperAdmin()) <th>Cabang</th> @endif
                                <th>Pelanggan</th>
                                <th>Cara Bayar</th>
                                <th>Status</th>
                                <th class="text-right">Total</th>
                                <th class="text-right">HPP</th>
                                <th class="text-right">Keuntungan</th>
                                <th class="text-center">Margin</th>
                                <th>Item</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $trx)
                                @php
                                    $trxHPP    = $trx->items->sum(fn($i) => $i->buy_price * $i->qty);
                                    $trxProfit = $trx->total - $trxHPP;
                                    $trxMargin = $trx->total > 0 ? $trxProfit / $trx->total * 100 : 0;
                                @endphp
                                <tr>
                                    <td><a href="{{ route('transactions.show', $trx) }}">{{ $trx->trx_no }}</a></td>
                                    <td>{{ $trx->user->name ?? '-' }}</td>
                                    @if(auth()->user()->isSuperAdmin()) <td>{{ $trx->branch->name ?? '-' }}</td> @endif
                                    <td>{{ $trx->customer_name ?? '-' }}</td>
                                    <td><span class="badge {{ $trx->payment_method === 'Cash' ? 'badge-success' : 'badge-warning' }}">{{ $trx->payment_method }}</span></td>
                                    <td><span class="badge {{ $trx->payment_status === 'Lunas' ? 'badge-success' : 'badge-danger' }}">{{ $trx->payment_status }}</span></td>
                                    <td class="text-right">Rp {{ number_format($trx->total, 0, ',', '.') }}</td>
                                    <td class="text-right text-secondary">Rp {{ number_format($trxHPP, 0, ',', '.') }}</td>
                                    <td class="text-right {{ $trxProfit >= 0 ? 'text-success' : 'text-danger' }} font-weight-bold">
                                        Rp {{ number_format($trxProfit, 0, ',', '.') }}
                                    </td>
                                    <td class="text-center">
                                        <span class="badge {{ $trxMargin >= 20 ? 'badge-success' : ($trxMargin >= 10 ? 'badge-warning' : 'badge-danger') }}">
                                            {{ number_format($trxMargin, 1) }}%
                                        </span>
                                    </td>
                                    <td>{{ $trx->items->count() }} item</td>
                                </tr>
                            @empty
                                <tr><td colspan="12" class="text-center text-muted py-4">Tidak ada transaksi pada tanggal ini</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Tab: Per Produk --}}
            <div class="tab-pane fade" id="tab-produk">
                <div class="p-2 bg-light border-bottom">
                    <small class="text-muted">Penjualan per Produk — {{ \Carbon\Carbon::parse($date)->translatedFormat('d F Y') }}</small>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>#</th>
                                <th>Produk</th>
                                <th>Kategori</th>
                                <th>Barcode</th>
                                <th class="text-center">Qty Terjual</th>
                                <th class="text-right">HPP</th>
                                <th class="text-right">Pendapatan</th>
                                <th class="text-right">Keuntungan</th>
                                <th class="text-center">Margin</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($productSummary as $i => $p)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $p->product_name }}</td>
                                    <td>{{ $p->category_name ?? '-' }}</td>
                                    <td><code>{{ $p->barcode ?? '-' }}</code></td>
                                    <td class="text-center">{{ $p->total_qty }} {{ $p->unit }}</td>
                                    <td class="text-right text-secondary">Rp {{ number_format($p->total_hpp, 0, ',', '.') }}</td>
                                    <td class="text-right">Rp {{ number_format($p->total_revenue, 0, ',', '.') }}</td>
                                    <td class="text-right {{ $p->total_profit >= 0 ? 'text-success' : 'text-danger' }} font-weight-bold">
                                        Rp {{ number_format($p->total_profit, 0, ',', '.') }}
                                    </td>
                                    <td class="text-center">
                                        <span class="badge {{ $p->margin >= 20 ? 'badge-success' : ($p->margin >= 10 ? 'badge-warning' : 'badge-danger') }}">
                                            {{ number_format($p->margin, 1) }}%
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="9" class="text-center text-muted py-4">Tidak ada data produk</td></tr>
                            @endforelse
                        </tbody>
                        @if($productSummary->isNotEmpty())
                        <tfoot class="thead-light">
                            <tr>
                                <th colspan="4" class="text-right">Total</th>
                                <th class="text-center">{{ $productSummary->sum('total_qty') }}</th>
                                <th class="text-right text-secondary">Rp {{ number_format($productSummary->sum('total_hpp'), 0, ',', '.') }}</th>
                                <th class="text-right">Rp {{ number_format($productSummary->sum('total_revenue'), 0, ',', '.') }}</th>
                                <th class="text-right text-success font-weight-bold">Rp {{ number_format($productSummary->sum('total_profit'), 0, ',', '.') }}</th>
                                <th></th>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
