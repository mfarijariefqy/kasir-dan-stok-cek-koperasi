@extends('layouts.app')

@section('title', 'Riwayat Transaksi')
@section('page-title', 'Riwayat Transaksi')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Riwayat Transaksi</li>
@endsection

@section('content')
<!-- Filter -->
<div class="card">
    <div class="card-body">
        <form method="GET" class="row align-items-end">
            <div class="col-md-2 mb-2">
                <label class="small mb-1">Tanggal Dari</label>
                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2 mb-2">
                <label class="small mb-1">Tanggal Sampai</label>
                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-2 mb-2">
                <label class="small mb-1">Cara Bayar</label>
                <select name="payment_method" class="form-control form-control-sm">
                    <option value="">Semua</option>
                    <option value="Cash" {{ request('payment_method') === 'Cash' ? 'selected' : '' }}>Cash</option>
                    <option value="Tempo" {{ request('payment_method') === 'Tempo' ? 'selected' : '' }}>Tempo</option>
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <label class="small mb-1">Status Bayar</label>
                <select name="payment_status" class="form-control form-control-sm">
                    <option value="">Semua</option>
                    <option value="Lunas" {{ request('payment_status') === 'Lunas' ? 'selected' : '' }}>Lunas</option>
                    <option value="Belum Lunas" {{ request('payment_status') === 'Belum Lunas' ? 'selected' : '' }}>Belum Lunas</option>
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <label class="small mb-1">Kasir</label>
                <select name="user_id" class="form-control form-control-sm">
                    <option value="">Semua</option>
                    @foreach($kasirList as $u)
                        <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 mb-2 d-flex">
                <button type="submit" class="btn btn-info btn-sm mr-1">
                    <i class="fas fa-search mr-1"></i> Filter
                </button>
                <a href="{{ route('transactions.index') }}" class="btn btn-secondary btn-sm">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Summary -->
<div class="row">
    <div class="col-md-4">
        <div class="info-box bg-info">
            <span class="info-box-icon"><i class="fas fa-receipt"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Transaksi</span>
                <span class="info-box-number">{{ number_format($totalCount) }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="info-box bg-success">
            <span class="info-box-icon"><i class="fas fa-money-bill-wave"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Penjualan</span>
                <span class="info-box-number">Rp {{ number_format($grandTotal, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>
</div>

<!-- Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>No. Transaksi</th>
                        <th>Tanggal</th>
                        <th>Pelanggan</th>
                        <th>Kasir</th>
                        @if(auth()->user()->isSuperAdmin())
                            <th>Cabang</th>
                        @endif
                        <th>Cara Bayar</th>
                        <th>Status</th>
                        <th class="text-right">Total</th>
                        <th width="120">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $trx)
                        <tr>
                            <td><a href="{{ route('transactions.show', $trx) }}">{{ $trx->trx_no }}</a></td>
                            <td>{{ $trx->trx_date->format('d/m/Y') }}</td>
                            <td>{{ $trx->customer_name ?? '-' }}</td>
                            <td>{{ $trx->user->name ?? '-' }}</td>
                            @if(auth()->user()->isSuperAdmin())
                                <td>{{ $trx->branch->name ?? '-' }}</td>
                            @endif
                            <td>
                                <span class="badge {{ $trx->payment_method === 'Cash' ? 'badge-success' : 'badge-warning' }}">
                                    {{ $trx->payment_method }}
                                </span>
                            </td>
                            <td>
                                <span class="badge {{ $trx->payment_status === 'Lunas' ? 'badge-success' : 'badge-danger' }}">
                                    {{ $trx->payment_status }}
                                </span>
                            </td>
                            <td class="text-right">Rp {{ number_format($trx->total, 0, ',', '.') }}</td>
                            <td>
                                <a href="{{ route('transactions.show', $trx) }}" class="btn btn-info btn-xs">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('transactions.receipt', $trx) }}" class="btn btn-secondary btn-xs" target="_blank">
                                    <i class="fas fa-print"></i>
                                </a>
                                @if($trx->payment_status === 'Belum Lunas')
                                    @can('manage-piutang')
                                        <form action="{{ route('transactions.bayar', $trx) }}" method="POST" class="d-inline">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="paid_at" class="paid-at-input">
                                            <button type="button" class="btn btn-success btn-xs btn-bayar"
                                                data-no="{{ $trx->trx_no }}">
                                                <i class="fas fa-check"></i> Lunas
                                            </button>
                                        </form>
                                    @endcan
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">Tidak ada transaksi</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($transactions->hasPages())
        <div class="card-footer">{{ $transactions->links() }}</div>
    @endif
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
