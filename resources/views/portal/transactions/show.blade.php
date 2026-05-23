@extends('portal.layout')

@section('title', 'Detail ' . $transaction->trx_no)
@section('page-title', $transaction->trx_no)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('portal.transactions.index') }}">Transaksi Saya</a></li>
    <li class="breadcrumb-item active">Detail</li>
@endsection

@section('content')

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-info-circle mr-1"></i> Informasi Transaksi</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted pl-3" style="width:110px;">No. Transaksi</td>
                        <td><strong>{{ $transaction->trx_no }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted pl-3">Tanggal</td>
                        <td><strong>{{ $transaction->trx_date->format('d/m/Y') }}</strong></td>
                    </tr>
                    @if($transaction->branch)
                    <tr>
                        <td class="text-muted pl-3">Cabang</td>
                        <td><strong>{{ $transaction->branch->name }}</strong></td>
                    </tr>
                    @endif
                    <tr>
                        <td class="text-muted pl-3">Cara Bayar</td>
                        <td>
                            <span class="badge {{ $transaction->payment_method === 'Cash' ? 'badge-info' : 'badge-warning' }}">
                                {{ $transaction->payment_method }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted pl-3">Status</td>
                        <td>
                            <span class="badge {{ $transaction->payment_status === 'Lunas' ? 'badge-success' : 'badge-danger' }}">
                                {{ $transaction->payment_status }}
                            </span>
                        </td>
                    </tr>
                    @if($transaction->paid_at)
                    <tr>
                        <td class="text-muted pl-3">Dilunasi</td>
                        <td><strong>{{ $transaction->paid_at->format('d/m/Y') }}</strong></td>
                    </tr>
                    @endif
                </table>
            </div>
            <div class="card-footer">
                <a href="{{ route('portal.transactions.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali
                </a>
                @if($transaction->isBelumLunas())
                    <a href="{{ route('portal.transactions.editQty', $transaction) }}" class="btn btn-warning btn-sm ml-1">
                        <i class="fas fa-edit mr-1"></i> Ubah Qty
                    </a>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-boxes mr-1"></i> Daftar Barang</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped table-hover mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th width="36" class="text-center">#</th>
                            <th>Produk</th>
                            <th width="80" class="text-center">Qty</th>
                            <th width="130" class="text-right">Harga</th>
                            <th width="130" class="text-right">Subtotal</th>
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
            </div>
            <div class="card-footer text-right">
                <strong style="font-size:16px;">
                    Total: Rp {{ number_format($transaction->total, 0, ',', '.') }}
                </strong>
            </div>
        </div>
    </div>
</div>

@endsection
