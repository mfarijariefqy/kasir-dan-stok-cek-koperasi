<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Transaksi</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            padding: 20px;
            max-width: 300px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 1px dashed #000;
            padding-bottom: 10px;
        }
        .header h2 { font-size: 18px; margin-bottom: 5px; }

        .info {
            margin-bottom: 10px;
            font-size: 11px;
        }
        .info p { margin: 3px 0; }

        .separator {
            border-top: 1px dashed #000;
            margin: 10px 0;
        }

        /* Per-transaksi block */
        .trx-block { margin-bottom: 4px; }

        table {
            width: 100%;
            margin-bottom: 4px;
        }
        table td { padding: 2px 0; }

        .item-name  { width: 55%; }
        .item-qty   { width: 15%; text-align: center; }
        .item-price { width: 30%; text-align: right; }

        .trx-summary {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            margin: 2px 0;
        }
        .trx-total {
            display: flex;
            justify-content: space-between;
            font-weight: bold;
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            padding: 3px 0;
            margin: 4px 0;
            font-size: 12px;
        }
        .trx-meta {
            font-size: 10px;
            color: #444;
            margin-bottom: 2px;
        }

        .grand-total-section {
            border-top: 2px solid #000;
            padding-top: 8px;
            margin-top: 4px;
        }
        .grand-row {
            display: flex;
            justify-content: space-between;
            margin: 4px 0;
            font-size: 12px;
        }
        .grand-row.main {
            font-size: 14px;
            font-weight: bold;
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            padding: 5px 0;
            margin-top: 6px;
        }

        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 11px;
            border-top: 1px dashed #000;
            padding-top: 10px;
        }

        .no-print {
            text-align: center;
            margin-top: 20px;
        }
        .no-print button {
            padding: 8px 18px;
            cursor: pointer;
            margin: 0 4px;
            font-size: 13px;
        }

        @media print {
            body { padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <div class="header">
        <h2>Cerita Coffee</h2>
        <p>Mijen, Wonosari, Kec. Pegandon,</p>
        <p>Kab. Kendal, Jawa Tengah</p>
        <p>Telp: 0899-9877-667</p>
    </div>

    <div class="info">
        <p>Dicetak : {{ now()->format('d/m/Y H:i') }}</p>
        <p>Periode :
            @if($dateFrom || $dateTo)
                {{ $dateFrom ? \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') : '...' }}
                s/d
                {{ $dateTo ? \Carbon\Carbon::parse($dateTo)->format('d/m/Y') : '...' }}
            @else
                Semua
            @endif
        </p>
        @if($paymentMethod)
        <p>Metode  : {{ $paymentMethod }}</p>
        @endif
    </div>

    <div class="separator"></div>

    @if($transactions->isEmpty())
        <p style="text-align:center;padding:16px 0;">Tidak ada data transaksi.</p>
    @else
        @foreach($transactions as $transaction)
        <div class="trx-block">
            <div class="trx-meta">
                Kasir: {{ $transaction->user->name }}
                @if($transaction->customer_name)
                 | {{ $transaction->customer_name }}
                @endif
                | {{ $transaction->payment_method }}
            </div>

            <table>
                <tbody>
                    @foreach($transaction->items as $item)
                    <tr>
                        <td class="item-name">{{ $item->product->name }}</td>
                        <td class="item-qty">{{ $item->qty }}x</td>
                        <td class="item-price">{{ number_format($item->subtotal, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="trx-total">
                <span>TOTAL</span>
                <span>Rp {{ number_format($transaction->total, 0, ',', '.') }}</span>
            </div>
            <div class="trx-summary">
                <span>Bayar</span>
                <span>Rp {{ number_format($transaction->paid, 0, ',', '.') }}</span>
            </div>
            <div class="trx-summary">
                <span>Kembalian</span>
                <span>Rp {{ number_format($transaction->change, 0, ',', '.') }}</span>
            </div>
        </div>
        @if(!$loop->last)
        <div class="separator"></div>
        @endif
        @endforeach

        <div class="grand-total-section">
            <div class="grand-row">
                <span>Jumlah Transaksi</span>
                <span>{{ $transactions->count() }}</span>
            </div>
            <div class="grand-row main">
                <span>GRAND TOTAL</span>
                <span>Rp {{ number_format($grandTotal, 0, ',', '.') }}</span>
            </div>
        </div>
    @endif

    <div class="footer">
        <p>Terima Kasih</p>
        <p>Atas Kunjungan Anda</p>
    </div>

    <div class="no-print">
        <button onclick="window.print()">🖨️ Cetak</button>
        <button onclick="window.close()">✕ Tutup</button>
    </div>

</body>
</html>
