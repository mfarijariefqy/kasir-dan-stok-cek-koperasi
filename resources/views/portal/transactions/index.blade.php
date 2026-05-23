@extends('portal.layout')

@section('title', 'Transaksi Saya')
@section('page-title', 'Transaksi Saya')

@section('breadcrumb')
    <li class="breadcrumb-item active">Transaksi Saya</li>
@endsection

@section('content')

<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-receipt mr-2"></i>
            Halo, <strong>{{ $customer->name }}</strong> — daftar transaksi Anda
        </h3>
    </div>
    <div class="card-body p-0">
        @forelse($transactions as $trx)
            <div class="p-3 border-bottom">
                <div class="d-flex justify-content-between align-items-start flex-wrap" style="gap:8px;">
                    <div>
                        <div class="font-weight-bold text-primary" style="font-size:15px;">
                            {{ $trx->trx_no }}
                        </div>
                        <div class="text-muted small mt-1">
                            {{ $trx->trx_date->format('d/m/Y') }}
                            @if($trx->branch)
                                &nbsp;&middot;&nbsp; {{ $trx->branch->name }}
                            @endif
                            &nbsp;&middot;&nbsp; {{ $trx->items->count() }} item
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="font-weight-bold" style="font-size:15px;">
                            Rp {{ number_format($trx->total, 0, ',', '.') }}
                        </div>
                        <div class="mt-1">
                            <span class="badge {{ $trx->payment_method === 'Cash' ? 'badge-info' : 'badge-warning' }}">
                                {{ $trx->payment_method }}
                            </span>
                            <span class="badge {{ $trx->payment_status === 'Lunas' ? 'badge-success' : 'badge-danger' }}">
                                {{ $trx->payment_status }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="mt-2">
                    <a href="{{ route('portal.transactions.show', $trx) }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-eye mr-1"></i> Detail
                    </a>
                    @if($trx->isBelumLunas())
                        <a href="{{ route('portal.transactions.editQty', $trx) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit mr-1"></i> Ubah Qty
                        </a>
                    @endif
                </div>
            </div>
        @empty
            <div class="text-center text-muted py-5">
                <i class="fas fa-receipt fa-3x mb-3 d-block" style="color:#dee2e6;"></i>
                Belum ada transaksi
            </div>
        @endforelse
    </div>
    @if($transactions->hasPages())
        <div class="card-footer">
            {{ $transactions->links() }}
        </div>
    @endif
</div>

@endsection
