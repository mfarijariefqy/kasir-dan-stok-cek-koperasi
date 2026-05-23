<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bukti Transaksi - {{ $transaction->trx_no }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 13px;
            color: #333;
            background: #e9ecef;
        }

        .page {
            width: 210mm;
            min-height: 297mm;
            background: #fff;
            margin: 24px auto;
            padding: 18mm 20mm 16mm;
            box-shadow: 0 4px 16px rgba(0,0,0,.18);
        }

        /* ── Header ─────────────────────────────────────────── */
        .header {
            display: flex;
            align-items: center;
            border-top: 4px solid #1b6b1b;
            border-bottom: 2px solid #1b6b1b;
            padding: 10px 0 12px;
            margin-bottom: 18px;
            gap: 12px;
        }
        .kop-logo {
            width: 88px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .kop-logo img {
            width: 82px;
            height: 82px;
            object-fit: contain;
        }
        .kop-center {
            flex: 1;
            text-align: center;
        }
        .kop-name {
            font-size: 21px;
            font-weight: 800;
            color: #1b6b1b;
            text-transform: uppercase;
            letter-spacing: .8px;
            line-height: 1.2;
        }
        .kop-tagline {
            font-size: 13px;
            font-weight: 700;
            color: #1b6b1b;
            margin-top: 2px;
        }
        .kop-detail {
            font-size: 11px;
            color: #1b6b1b;
            margin-top: 2px;
            line-height: 1.55;
        }
        .kop-right {
            width: 175px;
            flex-shrink: 0;
            font-size: 11.5px;
        }
        .kop-right table { border-collapse: collapse; width: 100%; }
        .kop-right td { padding: 4px 2px; vertical-align: bottom; white-space: nowrap; }
        .kop-right .field-val {
            border-bottom: 1px solid #333;
            width: 100%;
            display: block;
            min-height: 14px;
            padding-bottom: 1px;
        }
        .kop-right .field-blank {
            border-bottom: 1px solid #333;
            display: block;
            min-height: 14px;
            margin-top: 4px;
        }

        /* ── Info Row ────────────────────────────────────────── */
        .info-row {
            display: flex;
            justify-content: space-between;
            background: #f4f6f9;
            border-radius: 6px;
            padding: 11px 16px;
            margin-bottom: 20px;
            gap: 20px;
        }
        .info-col table { border-collapse: collapse; }
        .info-col td {
            padding: 3px 10px 3px 0;
            font-size: 12px;
            vertical-align: top;
        }
        .info-col td:first-child {
            color: #888;
            white-space: nowrap;
            width: 90px;
        }
        .info-col td strong { color: #222; }
        .badge {
            display: inline-block;
            padding: 2px 12px;
            border-radius: 20px;
            font-size: 11.5px;
            font-weight: 600;
        }
        .badge-success { background: #d4edda; color: #155724; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-danger  { background: #f8d7da; color: #721c24; }

        /* ── Items Table ─────────────────────────────────────── */
        .items-table {
            width: 100%;
            border-collapse: collapse;
        }
        .items-table thead tr {
            background: #1a3a5c;
            color: #fff;
        }
        .items-table thead th {
            padding: 9px 11px;
            font-size: 12px;
            font-weight: 600;
        }
        .items-table tbody tr:nth-child(even) { background: #f8f9fb; }
        .items-table tbody td {
            padding: 8px 11px;
            font-size: 12.5px;
            border-bottom: 1px solid #e9ecef;
            vertical-align: middle;
        }
        .text-right  { text-align: right; }
        .text-center { text-align: center; }

        /* ── Total Box ───────────────────────────────────────── */
        .total-wrapper {
            display: flex;
            justify-content: flex-end;
            margin-top: 2px;
        }
        .total-box {
            min-width: 270px;
            border: 1px solid #dee2e6;
            border-radius: 0 0 6px 6px;
            overflow: hidden;
        }
        .total-box table { width: 100%; border-collapse: collapse; }
        .total-box .row-subtotal td {
            padding: 7px 13px;
            font-size: 12px;
            border-bottom: 1px solid #e9ecef;
            color: #555;
        }
        .total-box .row-grand td {
            padding: 10px 13px;
            font-size: 14px;
            font-weight: 700;
            background: #1a3a5c;
            color: #fff;
        }

        /* ── Notes / stamp area ──────────────────────────────── */
        .bottom-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-top: 24px;
            gap: 20px;
        }
        .notes-col {
            flex: 1;
        }
        .notes-col .label {
            font-size: 11px;
            color: #aaa;
            text-transform: uppercase;
            letter-spacing: .5px;
            margin-bottom: 4px;
        }
        .notes-col .value {
            font-size: 12.5px;
            color: #444;
        }
        .stamp-col {
            text-align: center;
            min-width: 130px;
        }
        .stamp-col .stamp-label {
            font-size: 11px;
            color: #888;
            margin-bottom: 50px;
        }
        .stamp-col .stamp-sign {
            font-size: 11px;
            color: #888;
            border-top: 1px solid #bbb;
            padding-top: 4px;
        }

        /* ── Footer ──────────────────────────────────────────── */
        .footer {
            margin-top: 28px;
            padding-top: 12px;
            border-top: 1px dashed #ccc;
            text-align: center;
            font-size: 11.5px;
            color: #999;
            line-height: 1.8;
        }
        .footer .thank-you {
            font-size: 13px;
            font-weight: 600;
            color: #1a3a5c;
        }

        /* ── Print Actions (screen only) ─────────────────────── */
        .print-actions {
            display: flex;
            justify-content: center;
            gap: 10px;
            padding: 16px 0 10px;
        }
        .btn {
            padding: 9px 30px;
            border: none;
            border-radius: 5px;
            font-size: 13px;
            cursor: pointer;
        }
        .btn-primary { background: #1a3a5c; color: #fff; }
        .btn-secondary { background: #6c757d; color: #fff; }

        /* ── Print Media ─────────────────────────────────────── */
        @media print {
            body { background: #fff; }
            .page { margin: 0; padding: 12mm 15mm 12mm; box-shadow: none; }
            .print-actions { display: none; }
            @page { size: A4 portrait; margin: 0; }
        }
    </style>
</head>
<body>

<div class="page">

    {{-- ── Header ───────────────────────────────────────────── --}}
    <div class="header">

        {{-- Logo --}}
        <div class="kop-logo">
            @php $logoPath = public_path('images/logo-koperasi.png'); @endphp
            @if(file_exists($logoPath))
                <img src="{{ asset('images/logo-koperasi.png') }}" alt="Logo">
            @endif
        </div>

        {{-- Info Koperasi (tengah) --}}
        <div class="kop-center">
            <div class="kop-name">{{ config('koperasi.name') }}</div>
            <div class="kop-tagline">{{ config('koperasi.tagline') }}</div>
            <div class="kop-detail">NIK . {{ config('koperasi.nik') }} / {{ config('koperasi.ahu') }}</div>
            <div class="kop-detail">WA. {{ config('koperasi.wa') }}</div>
            <div class="kop-detail"><strong>Alamat : {{ config('koperasi.alamat') }}</strong></div>
        </div>

        {{-- Tanggal & Kepada (kanan) --}}
        <div class="kop-right">
            <table>
                <tr>
                    <td>Tanggal</td>
                    <td>&nbsp;:&nbsp;</td>
                    <td style="width:100%">
                        <span class="field-val">
                            {{ $transaction->trx_date->format('d') }} &nbsp;/&nbsp;
                            {{ $transaction->trx_date->format('m') }} &nbsp;/&nbsp;
                            {{ $transaction->trx_date->format('Y') }} .
                        </span>
                    </td>
                </tr>
                <tr>
                    <td>Kepada. yth</td>
                    <td>&nbsp;:&nbsp;</td>
                    <td>
                        <span class="field-val">{{ $transaction->customer_name ?? '' }}</span>
                    </td>
                </tr>
                <tr>
                    <td colspan="3">
                        <span class="field-blank"></span>
                    </td>
                </tr>
            </table>
        </div>

    </div>

    {{-- ── Info Row ──────────────────────────────────────────── --}}
    <div class="info-row">
        <div class="info-col">
            <table>
                <tr>
                    <td>Kasir</td>
                    <td><strong>{{ $transaction->user->name ?? '-' }}</strong></td>
                </tr>
                @if($transaction->customer_name)
                <tr>
                    <td>Pelanggan</td>
                    <td><strong>{{ $transaction->customer_name }}</strong></td>
                </tr>
                @endif
                @if($transaction->branch)
                <tr>
                    <td>Cabang</td>
                    <td><strong>{{ $transaction->branch->name }}</strong></td>
                </tr>
                @endif
            </table>
        </div>
        <div class="info-col">
            <table>
                <tr>
                    <td>Cara Bayar</td>
                    <td>
                        <span class="badge {{ $transaction->payment_method === 'Cash' ? 'badge-success' : 'badge-warning' }}">
                            {{ $transaction->payment_method }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <td>Status</td>
                    <td>
                        <span class="badge {{ $transaction->payment_status === 'Lunas' ? 'badge-success' : 'badge-danger' }}">
                            {{ $transaction->payment_status }}
                        </span>
                    </td>
                </tr>
                @if($transaction->paid_at)
                <tr>
                    <td>Dilunasi</td>
                    <td><strong>{{ $transaction->paid_at->format('d/m/Y') }}</strong></td>
                </tr>
                @endif
            </table>
        </div>
    </div>

    {{-- ── Items Table ───────────────────────────────────────── --}}
    <table class="items-table">
        <thead>
            <tr>
                <th width="32" class="text-center">#</th>
                <th>Nama Produk</th>
                <th width="90" class="text-center">Qty</th>
                <th width="140" class="text-right">Harga Satuan</th>
                <th width="140" class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transaction->items as $i => $item)
            <tr>
                <td class="text-center">{{ $i + 1 }}</td>
                <td>{{ $item->product->name ?? '-' }}</td>
                <td class="text-center">{{ $item->qty }} {{ $item->product->unit ?? '' }}</td>
                <td class="text-right">Rp {{ number_format($item->sell_price, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- ── Total ─────────────────────────────────────────────── --}}
    <div class="total-wrapper">
        <div class="total-box">
            <table>
                <tr class="row-grand">
                    <td>TOTAL PEMBAYARAN</td>
                    <td class="text-right">Rp {{ number_format($transaction->total, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>
    </div>

    {{-- ── Bottom: ttd area ──────────────────────────────────── --}}
    <div class="bottom-row">
        <div class="notes-col">
            <div class="label">Keterangan</div>
            <div class="value">
                @if($transaction->payment_status === 'Belum Lunas')
                    Transaksi ini belum dilunasi. Mohon segera melakukan pembayaran.
                @else
                    Pembayaran telah diterima dengan lengkap. Terima kasih.
                @endif
            </div>
        </div>
        <div class="stamp-col">
            <div class="stamp-label">Kasir / Penerima</div>
            <div class="stamp-sign">( {{ $transaction->user->name ?? '.....................' }} )</div>
        </div>
    </div>

    {{-- ── Footer ────────────────────────────────────────────── --}}
    <div class="footer">
        <div class="thank-you">Terima kasih atas kepercayaan Anda kepada {{ config('app.name') }}</div>
        <div>Dokumen ini dicetak pada {{ now()->format('d/m/Y H:i') }}</div>
    </div>

</div>

{{-- Print Actions (screen only) --}}
<div class="print-actions">
    <button class="btn btn-primary" onclick="window.print()">&#128424; Cetak</button>
    <button class="btn btn-secondary" onclick="window.close()">&#10005; Tutup</button>
</div>

<script>
    window.addEventListener('load', () => window.print());
</script>
</body>
</html>
