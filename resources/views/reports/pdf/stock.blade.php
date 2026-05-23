<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Laporan Stok</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #333; }
    .header { text-align: center; margin-bottom: 16px; border-bottom: 2px solid #333; padding-bottom: 10px; }
    .header h2 { font-size: 16px; font-weight: 700; }
    .header p { font-size: 10px; color: #666; margin-top: 2px; }
    table { width: 100%; border-collapse: collapse; font-size: 9px; }
    thead tr { background: #333; color: #fff; }
    th { padding: 5px 6px; text-align: left; }
    td { padding: 4px 6px; border-bottom: 1px solid #eee; }
    tr:nth-child(even) { background: #f9f9f9; }
    .text-right { text-align: right; }
    .text-center { text-align: center; }
    .badge-ok { color: #2E7D32; font-weight: 700; }
    .badge-warn { color: #E65100; font-weight: 700; }
    .badge-danger { color: #C62828; font-weight: 700; }
    .footer { margin-top: 14px; font-size: 9px; color: #aaa; text-align: right; }
</style>
</head>
<body>
<div class="header">
    <h2>LAPORAN STOK BARANG</h2>
    <p>Periode {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}</p>
    @if(isset($branchName))<p>Cabang: {{ $branchName }}</p>@endif
</div>

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Nama Produk</th>
            <th>Barcode</th>
            <th>Kategori</th>
            <th>Satuan</th>
            <th class="text-center">Stok Awal</th>
            <th class="text-center">Masuk</th>
            <th class="text-center">Keluar</th>
            <th class="text-center">Stok Akhir</th>
        </tr>
    </thead>
    <tbody>
        @forelse($products as $i => $product)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $product->name }}</td>
            <td>{{ $product->barcode ?? '-' }}</td>
            <td>{{ $product->category->name ?? '-' }}</td>
            <td>{{ $product->unit }}</td>
            <td class="text-center">{{ $product->stok_awal }}</td>
            <td class="text-center" style="color:#2E7D32; font-weight:700;">+{{ $product->stok_masuk }}</td>
            <td class="text-center" style="color:#C62828; font-weight:700;">-{{ $product->stok_keluar }}</td>
            <td class="text-center {{ $product->stok_akhir <= 0 ? 'badge-danger' : ($product->stok_akhir <= 5 ? 'badge-warn' : 'badge-ok') }}">
                {{ $product->stok_akhir }}
            </td>
        </tr>
        @empty
        <tr><td colspan="9" style="text-align:center; color:#aaa; padding:16px;">Tidak ada data produk</td></tr>
        @endforelse
    </tbody>
</table>

<div class="footer">Dicetak: {{ now()->format('d/m/Y H:i') }}</div>
</body>
</html>
