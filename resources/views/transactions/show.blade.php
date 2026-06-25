@extends('layouts.app')

@section('title', 'Detail Transaksi')
@section('page-title', 'Detail Transaksi')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('transactions.index') }}">Transaksi</a></li>
    <li class="breadcrumb-item active">{{ $transaction->trx_no }}</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">{{ $transaction->trx_no }}</h3>
                <div class="card-tools">
                    <a href="{{ route('transactions.receipt', $transaction) }}" class="btn btn-secondary btn-sm mr-1" target="_blank">
                        <i class="fas fa-print mr-1"></i> Cetak Struk
                    </a>
                    <a href="{{ route('transactions.index') }}" class="btn btn-default btn-sm">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr><th width="130">No. Transaksi</th><td>{{ $transaction->trx_no }}</td></tr>
                            <tr><th>Tanggal</th><td>{{ $transaction->trx_date->format('d F Y') }}</td></tr>
                            <tr><th>Kasir</th><td>{{ $transaction->user->name ?? '-' }}</td></tr>
                            <tr><th>Cabang</th><td>@include('partials._branch_badges', ['branches' => $transaction->branches()])</td></tr>
                            <tr><th>Pelanggan</th><td>{{ $transaction->customer_name ?? '-' }}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <th width="130">Cara Bayar</th>
                                <td>
                                    <span class="badge {{ $transaction->payment_method === 'Cash' ? 'badge-success' : 'badge-warning' }} badge-pill px-3">
                                        {{ $transaction->payment_method }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>
                                    <span class="badge {{ $transaction->payment_status === 'Lunas' ? 'badge-success' : 'badge-danger' }} badge-pill px-3">
                                        {{ $transaction->payment_status }}
                                    </span>
                                </td>
                            </tr>
                            @if($transaction->paid_at)
                            <tr>
                                <th>Dilunasi</th>
                                <td>{{ $transaction->paid_at->format('d/m/Y H:i') }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>

                <table class="table table-bordered">
                    <thead class="thead-light">
                        <tr>
                            <th>#</th>
                            <th>Produk</th>
                            <th>Cabang</th>
                            <th>Barcode</th>
                            <th class="text-right">Harga</th>
                            <th class="text-center">Qty</th>
                            <th class="text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transaction->items as $i => $item)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $item->product->name ?? '-' }}</td>
                                <td>
                                    <span class="badge badge-light border">
                                        {{ $item->branch->name ?? '-' }}
                                    </span>
                                </td>
                                <td><code>{{ $item->product->barcode ?? '-' }}</code></td>
                                <td class="text-right">Rp {{ number_format($item->sell_price, 0, ',', '.') }}</td>
                                <td class="text-center">{{ $item->qty }} {{ $item->product->unit ?? '' }}</td>
                                <td class="text-right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="6" class="text-right"><strong>TOTAL</strong></td>
                            <td class="text-right"><strong>Rp {{ number_format($transaction->total, 0, ',', '.') }}</strong></td>
                        </tr>
                    </tfoot>
                </table>

                @if($transaction->payment_status === 'Belum Lunas')
                    @can('manage-piutang')
                        <div class="alert alert-warning">
                            <i class="fas fa-clock mr-1"></i>
                            Transaksi ini belum dibayar.
                            <form action="{{ route('transactions.bayar', $transaction) }}" method="POST" class="d-inline ml-2">
                                @csrf @method('PATCH')
                                <input type="hidden" name="paid_at" class="paid-at-input">
                                <button type="button" class="btn btn-success btn-sm btn-bayar"
                                    data-no="{{ $transaction->trx_no }}">
                                    <i class="fas fa-check mr-1"></i> Tandai Lunas
                                </button>
                            </form>
                        </div>
                    @endcan
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).on('click', '.btn-bayar', function() {
        const form  = $(this).closest('form');
        const no    = $(this).data('no');
        const today = new Date().toISOString().split('T')[0];
        Swal.fire({
            title: 'Tandai Lunas?',
            html: `<p class="mb-2">Transaksi <strong>${no}</strong> akan ditandai sebagai <strong>LUNAS</strong>.</p>
                   <label class="d-block text-left small mb-1">Tanggal Pelunasan</label>
                   <input type="date" id="swal-paid-at" class="swal2-input" value="${today}" max="${today}">`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            confirmButtonText: 'Ya, Lunas',
            cancelButtonText: 'Batal',
            preConfirm: () => {
                const date = document.getElementById('swal-paid-at').value;
                if (!date) {
                    Swal.showValidationMessage('Tanggal pelunasan wajib diisi');
                    return false;
                }
                return date;
            }
        }).then(r => {
            if (r.isConfirmed) {
                form.find('.paid-at-input').val(r.value);
                form.submit();
            }
        });
    });
</script>
@endpush
