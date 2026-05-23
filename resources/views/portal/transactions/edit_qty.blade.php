@extends('portal.layout')

@section('title', 'Ubah Qty — ' . $transaction->trx_no)
@section('page-title', 'Ubah Qty Pesanan')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('portal.transactions.index') }}">Transaksi Saya</a></li>
    <li class="breadcrumb-item">
        <a href="{{ route('portal.transactions.show', $transaction) }}">{{ $transaction->trx_no }}</a>
    </li>
    <li class="breadcrumb-item active">Ubah Qty</li>
@endsection

@section('content')

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-circle mr-1"></i> {{ $errors->first() }}
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
@endif

<div class="alert alert-warning">
    <i class="fas fa-info-circle mr-1"></i>
    Anda hanya dapat mengubah <strong>jumlah (qty)</strong> barang.
    Harga dan jenis barang tidak dapat diubah. Total dihitung ulang otomatis setelah disimpan.
</div>

<form action="{{ route('portal.transactions.updateQty', $transaction) }}" method="POST" id="editQtyForm">
    @csrf @method('PATCH')

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-edit mr-1"></i> {{ $transaction->trx_no }}
            </h3>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th>Produk</th>
                        <th width="130" class="text-right">Harga Satuan</th>
                        <th width="120" class="text-center">Qty</th>
                        <th width="140" class="text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transaction->items as $item)
                    <tr>
                        <td>
                            <strong>{{ $item->product->name ?? '-' }}</strong>
                            @if($item->product->unit ?? false)
                                <br><small class="text-muted">{{ $item->product->unit }}</small>
                            @endif
                        </td>
                        <td class="text-right">
                            Rp {{ number_format($item->sell_price, 0, ',', '.') }}
                        </td>
                        <td class="text-center">
                            <input type="number"
                                name="quantities[{{ $item->id }}]"
                                value="{{ old('quantities.' . $item->id, $item->qty) }}"
                                min="1"
                                class="form-control form-control-sm qty-input text-center"
                                style="width:80px; margin:0 auto;"
                                data-price="{{ $item->sell_price }}"
                                data-item-id="{{ $item->id }}">
                        </td>
                        <td class="text-right font-weight-bold subtotal-cell" id="sub-{{ $item->id }}">
                            Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer d-flex justify-content-between align-items-center">
            <div>
                <span class="text-muted">Total Baru:</span>
                <strong class="ml-1" style="font-size:17px; color:#1a3a5c;" id="grandTotal">
                    Rp {{ number_format($transaction->total, 0, ',', '.') }}
                </strong>
            </div>
            <div>
                <a href="{{ route('portal.transactions.show', $transaction) }}" class="btn btn-secondary mr-2">
                    <i class="fas fa-times mr-1"></i> Batal
                </a>
                <button type="submit" class="btn btn-primary" id="saveBtn">
                    <i class="fas fa-save mr-1"></i> Simpan Perubahan
                </button>
            </div>
        </div>
    </div>
</form>

@endsection

@push('scripts')
<script>
    function formatRp(num) {
        return 'Rp ' + Math.round(num).toLocaleString('id-ID');
    }

    function recalculate() {
        let grand = 0;
        document.querySelectorAll('.qty-input').forEach(function (input) {
            const qty      = parseInt(input.value) || 0;
            const price    = parseFloat(input.dataset.price) || 0;
            const itemId   = input.dataset.itemId;
            const subtotal = qty * price;
            grand += subtotal;
            const cell = document.getElementById('sub-' + itemId);
            if (cell) cell.textContent = formatRp(subtotal);
        });
        document.getElementById('grandTotal').textContent = formatRp(grand);
    }

    document.querySelectorAll('.qty-input').forEach(function (input) {
        input.addEventListener('input', recalculate);
    });
</script>
@endpush
