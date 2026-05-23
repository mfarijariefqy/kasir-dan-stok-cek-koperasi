@extends('layouts.app')

@section('title', 'Laporan Bulanan')
@section('page-title', 'Laporan Bulanan')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Laporan Bulanan</li>
@endsection

@section('content')
<!-- Filter -->
<div class="card">
    <div class="card-body">
        <form method="GET" class="row align-items-end">
            <div class="col-md-2 mb-2">
                <label class="small mb-1">Bulan</label>
                <input type="month" name="month" class="form-control form-control-sm" value="{{ $month }}">
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
                        <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
                            {{ $u->name }}
                        </option>
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
                <a href="{{ route('reports.monthly.export', array_merge(request()->all(), ['format' => 'excel'])) }}"
                    class="btn btn-success btn-sm mr-1">
                    <i class="fas fa-file-excel mr-1"></i> Excel
                </a>
                <a href="{{ route('reports.monthly.export', array_merge(request()->all(), ['format' => 'pdf'])) }}"
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
        <div class="info-box bg-primary"><span class="info-box-icon"><i class="fas fa-receipt"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Transaksi</span>
                <span class="info-box-number">{{ number_format($totalTransactions) }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box bg-success"><span class="info-box-icon"><i class="fas fa-money-bill-wave"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Penjualan</span>
                <span class="info-box-number">Rp {{ number_format($totalSales, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box bg-danger"><span class="info-box-icon"><i class="fas fa-file-invoice-dollar"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Piutang</span>
                <span class="info-box-number">Rp {{ number_format($totalPiutang, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box bg-secondary"><span class="info-box-icon"><i class="fas fa-shopping-cart"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total HPP (Modal)</span>
                <span class="info-box-number">Rp {{ number_format($totalHPP, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="info-box bg-teal"><span class="info-box-icon"><i class="fas fa-chart-line"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Keuntungan / Laba</span>
                <span class="info-box-number">Rp {{ number_format($totalProfit, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="info-box bg-olive"><span class="info-box-icon"><i class="fas fa-percent"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Margin</span>
                <span class="info-box-number">{{ $totalSales > 0 ? number_format($totalProfit / $totalSales * 100, 1) : 0 }}%</span>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Grafik -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Grafik Penjualan Harian</h3></div>
            <div class="card-body">
                <canvas id="salesChart" height="120"></canvas>
            </div>
        </div>
    </div>

    <!-- Top Produk -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Top 10 Produk</h3></div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($topProducts as $i => $p)
                        <li class="list-group-item d-flex justify-content-between">
                            <span>{{ $i+1 }}. {{ $p->name }}</span>
                            <span class="text-primary">Rp {{ number_format($p->total_sales, 0, ',', '.') }}</span>
                        </li>
                    @empty
                        <li class="list-group-item text-center text-muted">Tidak ada data</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Detail -->
<div class="card card-primary card-outline card-outline-tabs">
    <div class="card-header p-0 border-bottom-0">
        <ul class="nav nav-tabs" id="monthlyReportTab">
            <li class="nav-item">
                <a class="nav-link active" data-toggle="tab" href="#tab-harian">
                    <i class="fas fa-calendar-day mr-1"></i> Per Hari
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#tab-produk-bulanan">
                    <i class="fas fa-box mr-1"></i> Per Produk
                </a>
            </li>
        </ul>
    </div>
    <div class="card-body p-0">
        <div class="tab-content">

            {{-- Tab: Rekap Per Hari --}}
            <div class="tab-pane fade show active" id="tab-harian">
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Tanggal</th>
                                <th class="text-center">Jml Transaksi</th>
                                <th class="text-right">Total Penjualan</th>
                                <th class="text-right">Lunas</th>
                                <th class="text-right">Belum Lunas</th>
                                <th class="text-right">HPP</th>
                                <th class="text-right">Keuntungan</th>
                                <th class="text-center">Margin</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($dailySummary as $row)
                                @php $rowMargin = $row->total > 0 ? $row->profit / $row->total * 100 : 0; @endphp
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($row->date)->translatedFormat('d F Y') }}</td>
                                    <td class="text-center">{{ $row->count }}</td>
                                    <td class="text-right">Rp {{ number_format($row->total, 0, ',', '.') }}</td>
                                    <td class="text-right">Rp {{ number_format($row->lunas ?? 0, 0, ',', '.') }}</td>
                                    <td class="text-right text-danger">Rp {{ number_format($row->tempo ?? 0, 0, ',', '.') }}</td>
                                    <td class="text-right text-secondary">Rp {{ number_format($row->hpp, 0, ',', '.') }}</td>
                                    <td class="text-right {{ $row->profit >= 0 ? 'text-success' : 'text-danger' }} font-weight-bold">
                                        Rp {{ number_format($row->profit, 0, ',', '.') }}
                                    </td>
                                    <td class="text-center">
                                        <span class="badge {{ $rowMargin >= 20 ? 'badge-success' : ($rowMargin >= 10 ? 'badge-warning' : 'badge-danger') }}">
                                            {{ number_format($rowMargin, 1) }}%
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center text-muted py-4">Tidak ada data</td></tr>
                            @endforelse
                        </tbody>
                        @if($dailySummary->isNotEmpty())
                        <tfoot class="thead-light font-weight-bold">
                            <tr>
                                <th>Total</th>
                                <th class="text-center">{{ $dailySummary->sum('count') }}</th>
                                <th class="text-right">Rp {{ number_format($totalSales, 0, ',', '.') }}</th>
                                <th class="text-right">Rp {{ number_format($dailySummary->sum('lunas'), 0, ',', '.') }}</th>
                                <th class="text-right text-danger">Rp {{ number_format($totalPiutang, 0, ',', '.') }}</th>
                                <th class="text-right text-secondary">Rp {{ number_format($totalHPP, 0, ',', '.') }}</th>
                                <th class="text-right text-success">Rp {{ number_format($totalProfit, 0, ',', '.') }}</th>
                                <th class="text-center">{{ $totalSales > 0 ? number_format($totalProfit / $totalSales * 100, 1) : 0 }}%</th>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>

            {{-- Tab: Per Produk --}}
            <div class="tab-pane fade" id="tab-produk-bulanan">
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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
const ctx = document.getElementById('salesChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: {!! $chartLabels !!},
        datasets: [{
            label: 'Penjualan (Rp)',
            data: {!! $chartData !!},
            backgroundColor: 'rgba(60,141,188,0.8)',
            borderColor: 'rgba(60,141,188,1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: {
            callback: v => 'Rp ' + v.toLocaleString('id-ID')
        }}}
    }
});
</script>
@endpush
