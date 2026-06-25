@extends('layouts.app')

@section('title', 'Laporan Piutang')
@section('page-title', 'Laporan Piutang')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Laporan Piutang</li>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <form method="GET" class="row align-items-end">
            <div class="col-md-2 mb-2">
                <label class="small mb-1">Dari Tanggal</label>
                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2 mb-2">
                <label class="small mb-1">Sampai Tanggal</label>
                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
            </div>
            @if(auth()->user()->isSuperAdmin())
            <div class="col-md-2 mb-2">
                <label class="small mb-1">Cabang</label>
                <select name="branch_id" class="form-control form-control-sm">
                    <option value="">Semua</option>
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}" {{ request('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="col-md-2 mb-2">
                <button type="submit" class="btn btn-info btn-sm mr-1">
                    <i class="fas fa-search mr-1"></i> Filter
                </button>
            </div>
        </form>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-4">
        <div class="info-box bg-danger">
            <span class="info-box-icon"><i class="fas fa-file-invoice-dollar"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Piutang</span>
                <span class="info-box-number">Rp {{ number_format($totalPiutang, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="info-box bg-warning">
            <span class="info-box-icon"><i class="fas fa-list"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Jumlah Transaksi Belum Lunas</span>
                <span class="info-box-number">{{ $piutangList->count() }}</span>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>No. Transaksi</th>
                        <th>Tanggal</th>
                        <th>Pelanggan</th>
                        <th>Kasir</th>
                        @if(auth()->user()->isSuperAdmin()) <th>Cabang</th> @endif
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($piutangList as $trx)
                        <tr>
                            <td><a href="{{ route('transactions.show', $trx) }}">{{ $trx->trx_no }}</a></td>
                            <td>{{ $trx->trx_date->format('d/m/Y') }}</td>
                            <td>{{ $trx->customer_name ?? '-' }}</td>
                            <td>{{ $trx->user->name ?? '-' }}</td>
                            @if(auth()->user()->isSuperAdmin()) <td>@include('partials._branch_badges', ['branches' => $trx->branches()])</td> @endif
                            <td class="text-right text-danger font-weight-bold">Rp {{ number_format($trx->branch_total ?? $trx->total, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">Tidak ada piutang</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
