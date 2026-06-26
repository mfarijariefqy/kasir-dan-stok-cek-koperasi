<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Laporan Harian {{ $date }}</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #333; }
    .header { text-align: center; margin-bottom: 12px; border-bottom: 2px solid #2C3E50; padding-bottom: 8px; }
    .header h2 { font-size: 15px; font-weight: 700; color: #2C3E50; }
    .header p { font-size: 10px; color: #666; margin-top: 2px; }
    .summary-row { display: flex; gap: 6px; margin-bottom: 10px; }
    .summary-box { flex: 1; border: 1px solid #ddd; border-radius: 3px; padding: 5px 8px; }
    .summary-box .lbl { font-size: 8px; color: #888; text-transform: uppercase; letter-spacing: 0.3px; }
    .summary-box .val { font-size: 11px; font-weight: 700; margin-top: 1px; }
    .section-title { font-size: 11px; font-weight: 700; margin: 14px 0 4px; padding: 4px 8px;
                     background: #2C3E50; color: #fff; border-radius: 2px; }
    table { width: 100%; border-collapse: collapse; font-size: 9px; margin-bottom: 4px; }
    thead tr { background: #34495E; color: #fff; }
    th { padding: 5px 6px; text-align: left; }
    td { padding: 4px 6px; border-bottom: 1px solid #eee; }
    tr.even td { background: #f9f9f9; }
    tfoot tr td { font-weight: 700; border-top: 2px solid #2C3E50; background: #ECF0F1; }
    .tr { text-align: right; }
    .tc { text-align: center; }
    .green { color: #27AE60; }
    .red   { color: #C0392B; }
    .amber { color: #B45309; }
    .muted { color: #7F8C8D; }
    .footer { margin-top: 14px; font-size: 8px; color: #aaa; text-align: right; border-top: 1px solid #eee; padding-top: 4px; }
</style>
</head>
<body>

<div class="header">
    <h2>LAPORAN HARIAN</h2>
    <p>{{ \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }}</p>
    @if($branchName ?? null)<p>Cabang: {{ $branchName }}</p>@endif
</div>

<!-- Summary Row 1 -->
<div class="summary-row">
    <div class="summary-box">
        <div class="lbl">Total Transaksi</div>
        <div class="val">{{ $totalTransactions }}</div>
    </div>
    <div class="summary-box">
        <div class="lbl">Total Penjualan</div>
        <div class="val">Rp {{ number_format($totalSales, 0, ',', '.') }}</div>
    </div>
    <div class="summary-box">
        <div class="lbl">Lunas</div>
        <div class="val green">Rp {{ number_format($totalLunas, 0, ',', '.') }}</div>
    </div>
    <div class="summary-box">
        <div class="lbl">Belum Lunas</div>
        <div class="val red">Rp {{ number_format($totalTempo, 0, ',', '.') }}</div>
    </div>
    <div class="summary-box">
        <div class="lbl">HPP (Modal)</div>
        <div class="val muted">Rp {{ number_format($totalHPP, 0, ',', '.') }}</div>
    </div>
    <div class="summary-box">
        <div class="lbl">Keuntungan / Laba</div>
        <div class="val green">Rp {{ number_format($totalProfit, 0, ',', '.') }}</div>
    </div>
    <div class="summary-box">
        <div class="lbl">Margin</div>
        <div class="val">{{ $totalSales > 0 ? number_format($totalProfit / $totalSales * 100, 1) : 0 }}%</div>
    </div>
</div>

<!-- Per Transaksi -->
<div class="section-title">Per Transaksi</div>
<table>
    <thead>
        <tr>
            <th>No. Transaksi</th>
            <th>Kasir</th>
            @if(auth()->user()->isSuperAdmin())<th>Cabang</th>@endif
            <th>Pelanggan</th>
            <th>Cara Bayar</th>
            <th>Status</th>
            <th class="tr">Total</th>
            <th class="tr">HPP</th>
            <th class="tr">Keuntungan</th>
            <th class="tc">Margin</th>
        </tr>
    </thead>
    <tbody>
        @forelse($transactions as $idx => $trx)
            @php
                $hpp    = $trx->display_hpp;
                $profit = $trx->display_profit;
                $margin = $trx->display_total > 0 ? $profit / $trx->display_total * 100 : 0;
            @endphp
            <tr class="{{ $idx % 2 === 1 ? 'even' : '' }}">
                <td>{{ $trx->trx_no }}</td>
                <td>{{ $trx->user->name ?? '-' }}</td>
                @if(auth()->user()->isSuperAdmin())<td>{{ $trx->branches()->pluck('name')->implode(', ') ?: '-' }}</td>@endif
                <td>{{ $trx->customer_name ?? '-' }}</td>
                <td>{{ $trx->payment_method }}</td>
                <td class="{{ $trx->payment_status === 'Lunas' ? 'green' : 'red' }}">{{ $trx->payment_status }}</td>
                <td class="tr">Rp {{ number_format($trx->display_total, 0, ',', '.') }}</td>
                <td class="tr muted">Rp {{ number_format($hpp, 0, ',', '.') }}</td>
                <td class="tr {{ $profit >= 0 ? 'green' : 'red' }}">Rp {{ number_format($profit, 0, ',', '.') }}</td>
                <td class="tc">{{ number_format($margin, 1) }}%</td>
            </tr>
        @empty
            <tr><td colspan="{{ auth()->user()->isSuperAdmin() ? 11 : 10 }}" style="text-align:center;color:#aaa;padding:10px;">Tidak ada transaksi</td></tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr>
            <td colspan="{{ auth()->user()->isSuperAdmin() ? 6 : 5 }}">TOTAL — {{ $totalTransactions }} transaksi</td>
            <td class="tr">Rp {{ number_format($totalSales, 0, ',', '.') }}</td>
            <td class="tr muted">Rp {{ number_format($totalHPP, 0, ',', '.') }}</td>
            <td class="tr green">Rp {{ number_format($totalProfit, 0, ',', '.') }}</td>
            <td class="tc">{{ $totalSales > 0 ? number_format($totalProfit / $totalSales * 100, 1) : 0 }}%</td>
        </tr>
    </tfoot>
</table>

<!-- Per Produk -->
<div class="section-title">Per Produk</div>
<table>
    <thead>
        <tr>
            <th style="width:22px">#</th>
            <th>Produk</th>
            <th>Kategori</th>
            <th class="tc">Qty</th>
            <th class="tr">HPP</th>
            <th class="tr">Pendapatan (Lunas)</th>
            <th class="tr">Pendapatan (Tempo)</th>
            <th class="tr">Total Pendapatan</th>
            <th class="tr">Keuntungan</th>
            <th class="tc">Margin</th>
        </tr>
    </thead>
    <tbody>
        @forelse($productSummary as $idx => $p)
            <tr class="{{ $idx % 2 === 1 ? 'even' : '' }}">
                <td>{{ $idx + 1 }}</td>
                <td>{{ $p->product_name }}</td>
                <td>{{ $p->category_name ?? '-' }}</td>
                <td class="tc">{{ $p->total_qty }} {{ $p->unit }}</td>
                <td class="tr muted">Rp {{ number_format($p->total_hpp, 0, ',', '.') }}</td>
                <td class="tr green">Rp {{ number_format($p->revenue_lunas, 0, ',', '.') }}</td>
                <td class="tr amber">Rp {{ number_format($p->revenue_tempo, 0, ',', '.') }}</td>
                <td class="tr">Rp {{ number_format($p->total_revenue, 0, ',', '.') }}</td>
                <td class="tr {{ $p->total_profit >= 0 ? 'green' : 'red' }}">Rp {{ number_format($p->total_profit, 0, ',', '.') }}</td>
                <td class="tc">{{ number_format($p->margin, 1) }}%</td>
            </tr>
        @empty
            <tr><td colspan="10" style="text-align:center;color:#aaa;padding:10px;">Tidak ada data produk</td></tr>
        @endforelse
    </tbody>
    @if($productSummary->isNotEmpty())
    <tfoot>
        @php
            $sumHPP    = $productSummary->sum('total_hpp');
            $sumRev    = $productSummary->sum('total_revenue');
            $sumLunas  = $productSummary->sum('revenue_lunas');
            $sumTempo  = $productSummary->sum('revenue_tempo');
            $sumProfit = $productSummary->sum('total_profit');
            $sumMargin = $sumRev > 0 ? $sumProfit / $sumRev * 100 : 0;
        @endphp
        <tr>
            <td colspan="3">TOTAL — {{ $productSummary->count() }} produk</td>
            <td class="tc">{{ $productSummary->sum('total_qty') }}</td>
            <td class="tr muted">Rp {{ number_format($sumHPP, 0, ',', '.') }}</td>
            <td class="tr green">Rp {{ number_format($sumLunas, 0, ',', '.') }}</td>
            <td class="tr amber">Rp {{ number_format($sumTempo, 0, ',', '.') }}</td>
            <td class="tr">Rp {{ number_format($sumRev, 0, ',', '.') }}</td>
            <td class="tr green">Rp {{ number_format($sumProfit, 0, ',', '.') }}</td>
            <td class="tc">{{ number_format($sumMargin, 1) }}%</td>
        </tr>
    </tfoot>
    @endif
</table>

<div class="footer">Dicetak: {{ now()->format('d/m/Y H:i') }}</div>
</body>
</html>
