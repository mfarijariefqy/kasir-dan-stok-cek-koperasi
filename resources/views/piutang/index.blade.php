@extends('layouts.app')

@section('title', 'Daftar Piutang')
@section('page-title', 'Daftar Piutang')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Piutang</li>
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
                <label class="small mb-1">Kasir</label>
                <select name="user_id" class="form-control form-control-sm">
                    <option value="">Semua</option>
                    @foreach($kasirList as $u)
                        <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            @if(auth()->user()->isSuperAdmin())
            <div class="col-md-2 mb-2">
                <label class="small mb-1">Cabang</label>
                <select name="branch_id" class="form-control form-control-sm">
                    <option value="">Semua</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="col-md-2 mb-2 d-flex">
                <button type="submit" class="btn btn-info btn-sm mr-1">
                    <i class="fas fa-search mr-1"></i> Filter
                </button>
                <a href="{{ route('piutang.index') }}" class="btn btn-secondary btn-sm">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Total Piutang -->
<div class="row">
    <div class="col-md-4">
        <div class="info-box bg-danger">
            <span class="info-box-icon"><i class="fas fa-file-invoice-dollar"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Piutang Belum Lunas</span>
                <span class="info-box-number">Rp {{ number_format($totalPiutang, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="info-box bg-warning">
            <span class="info-box-icon"><i class="fas fa-list"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Jumlah Transaksi Tempo</span>
                <span class="info-box-number">{{ $piutangList->total() }}</span>
            </div>
        </div>
    </div>
</div>

<!-- Tabel -->
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
                        <th class="text-right">Total</th>
                        <th width="120">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($piutangList as $trx)
                        <tr>
                            <td>
                                <a href="{{ route('transactions.show', $trx) }}">{{ $trx->trx_no }}</a>
                                <br><small class="text-muted">Tempo</small>
                            </td>
                            <td>{{ $trx->trx_date->format('d/m/Y') }}</td>
                            <td>{{ $trx->customer_name ?? '-' }}</td>
                            <td>{{ $trx->user->name ?? '-' }}</td>
                            @if(auth()->user()->isSuperAdmin())
                                <td>{{ $trx->branch->name ?? '-' }}</td>
                            @endif
                            <td class="text-right text-danger font-weight-bold">
                                Rp {{ number_format($trx->total, 0, ',', '.') }}
                            </td>
                            <td>
                                <a href="{{ route('transactions.show', $trx) }}" class="btn btn-info btn-xs">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @can('manage-piutang')
                                    <form action="{{ route('piutang.lunas', $trx) }}" method="POST" class="d-inline">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="paid_at" class="paid-at-input">
                                        <button type="button" class="btn btn-success btn-xs btn-lunas"
                                            data-no="{{ $trx->trx_no }}">
                                            <i class="fas fa-check mr-1"></i>Lunas
                                        </button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-check-circle fa-2x text-success mb-2 d-block"></i>
                                Tidak ada piutang yang belum lunas
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($piutangList->hasPages())
        <div class="card-footer">{{ $piutangList->links() }}</div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    $(document).on('click', '.btn-lunas', function() {
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
