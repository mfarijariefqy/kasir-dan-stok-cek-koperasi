<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Laporan Bulanan {{ $month }}</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #333; }
    .header { text-align: center; margin-bottom: 12px; border-bottom: 2px solid #2C3E50; padding-bottom: 8px; }
    .header h2 { font-size: 15px; font-weight: 700; color: #2C3E50; }
    .header p { font-size: 10px; color: #666; margin-top: 2px; }
    .summary-row { display: flex; gap: 6px; margin-bottom: 8px; }
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
    .muted { color: #7F8C8D; }
    .footer { margin-top: 14px; font-size: 8px; color: #aaa; text-align: right; border-top: 1px solid #eee; padding-top: 4px; }
</style>
</head>
<body>

<div class="header">
    <h2>LAPORAN BULANAN</h2>
    <p>{{ \Carbon\Carbon::parse($month . '-01')->translatedFormat('F Y') }}</p>
    @if($branchName ?? null)<p>Cabang: {{ $branchName }}</p>@endif
</div>

<!-- Summary Row 1 -->
<div class="summary-row">
    <div class="summary-box">
        <div class="lbl">Total Transaksi</div>
        <div class="val">{{ number_format($totalTransactions) }}</div>
    </div>
    <div class="summary-box">
        <div class="lbl">Total Penjualan</div>
        <div class="val">Rp {{ number_format($totalSales, 0, ',', '.') }}</div>
    </div>
    <div class="summary-box">
        <div class="lbl">Total Piutang</div>
        <div class="val red">Rp {{ number_format($totalPiutang, 0, ',', '.') }}</div>
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

<!-- Rekap Per Hari -->
<div class="section-title">Rekap Per Hari</div>
<table>
    <thead>
        <tr>
            <th>Tanggal</th>
            <th class="tc">Jml Transaksi</th>
            <th class="tr">Total Penjualan</th>
            <th class="tr">Lunas</th>
            <th class="tr">Belum Lunas</th>
            <th class="tr">HPP</th>
            <th class="tr">Keuntungan</th>
            <th class="tc">Margin</th>
        </tr>
    </thead>
    <tbody>
        @forelse($dailySummary as $idx => $row)
            @php $rowMargin = $row->total > 0 ? $row->profit / $row->total * 100 : 0; @endphp
            <tr class="{{ $idx % 2 === 1 ? 'even' : '' }}">
                <td>{{ \Carbon\Carbon::parse($row->date)->translatedFormat('d F Y') }}</td>
                <td class="tc">{{ $row->count }}</td>
                <td class="tr">Rp {{ number_format($row->total, 0, ',', '.') }}</td>
                <td class="tr green">Rp {{ number_format($row->lunas ?? 0, 0, ',', '.') }}</td>
                <td class="tr red">Rp {{ number_format($row->tempo ?? 0, 0, ',', '.') }}</td>
                <td class="tr muted">Rp {{ number_format($row->hpp, 0, ',', '.') }}</td>
                <td class="tr {{ $row->profit >= 0 ? 'green' : 'red' }}">Rp {{ number_format($row->profit, 0, ',', '.') }}</td>
                <td class="tc">{{ number_format($rowMargin, 1) }}%</td>
            </tr>
        @empty
            <tr><td colspan="8" style="text-align:center;color:#aaa;padding:10px;">Tidak ada data</td></tr>
        @endforelse
    </tbody>
    @if($dailySummary->isNotEmpty())
    <tfoot>
        <tr>
            <td>TOTAL</td>
            <td class="tc">{{ $dailySummary->sum('count') }}</td>
            <td class="tr">Rp {{ number_format($totalSales, 0, ',', '.') }}</td>
            <td class="tr green">Rp {{ number_format($dailySummary->sum('lunas'), 0, ',', '.') }}</td>
            <td class="tr red">Rp {{ number_format($totalPiutang, 0, ',', '.') }}</td>
            <td class="tr muted">Rp {{ number_format($totalHPP, 0, ',', '.') }}</td>
            <td class="tr green">Rp {{ number_format($totalProfit, 0, ',', '.') }}</td>
            <td class="tc">{{ $totalSales > 0 ? number_format($totalProfit / $totalSales * 100, 1) : 0 }}%</td>
        </tr>
    </tfoot>
    @endif
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
            <th class="tr">Pendapatan</th>
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
                <td class="tr">Rp {{ number_format($p->total_revenue, 0, ',', '.') }}</td>
                <td class="tr {{ $p->total_profit >= 0 ? 'green' : 'red' }}">Rp {{ number_format($p->total_profit, 0, ',', '.') }}</td>
                <td class="tc">{{ number_format($p->margin, 1) }}%</td>
            </tr>
        @empty
            <tr><td colspan="8" style="text-align:center;color:#aaa;padding:10px;">Tidak ada data produk</td></tr>
        @endforelse
    </tbody>
    @if($productSummary->isNotEmpty())
    <tfoot>
        @php
            $sumHPP    = $productSummary->sum('total_hpp');
            $sumRev    = $productSummary->sum('total_revenue');
            $sumProfit = $productSummary->sum('total_profit');
            $sumMargin = $sumRev > 0 ? $sumProfit / $sumRev * 100 : 0;
        @endphp
        <tr>
            <td colspan="3">TOTAL — {{ $productSummary->count() }} produk</td>
            <td class="tc">{{ $productSummary->sum('total_qty') }}</td>
            <td class="tr muted">Rp {{ number_format($sumHPP, 0, ',', '.') }}</td>
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
