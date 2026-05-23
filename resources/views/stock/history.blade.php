@extends('layouts.app')

@section('title', 'Riwayat Pergerakan Stok')
@section('page-title', 'Riwayat Pergerakan Stok')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('stock.index') }}">Stok Barang</a></li>
    <li class="breadcrumb-item active">Riwayat</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header"><h3 class="card-title">Riwayat Pergerakan Stok</h3></div>
    <div class="card-body border-bottom">
        <form method="GET" class="row align-items-end">
            <div class="col-md-3 mb-2">
                <label class="small mb-1">Produk</label>
                <select name="product_id" class="form-control form-control-sm">
                    <option value="">Semua Produk</option>
                    @foreach($productList as $p)
                        <option value="{{ $p->id }}" {{ request('product_id') == $p->id ? 'selected' : '' }}>
                            {{ $p->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <label class="small mb-1">Tipe</label>
                <select name="type" class="form-control form-control-sm">
                    <option value="">Semua</option>
                    <option value="IN" {{ request('type') === 'IN' ? 'selected' : '' }}>Masuk</option>
                    <option value="OUT" {{ request('type') === 'OUT' ? 'selected' : '' }}>Keluar</option>
                </select>
            </div>
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
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="col-md-2 mb-2">
                <label class="small mb-1">Petugas</label>
                <select name="user_id" class="form-control form-control-sm">
                    <option value="">Semua</option>
                    @foreach($userList as $u)
                        <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
                            {{ $u->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1 mb-2">
                <button type="submit" class="btn btn-info btn-sm btn-block">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Tanggal & Jam</th>
                        <th>Produk</th>
                        <th>Barcode</th>
                        @if(auth()->user()->isSuperAdmin())
                            <th>Cabang</th>
                        @endif
                        <th class="text-center">Tipe</th>
                        <th class="text-center">Qty</th>
                        <th>Keterangan</th>
                        <th>User</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td>{{ $log->logged_at->format('d/m/Y H:i') }}</td>
                            <td>{{ $log->product->name ?? '-' }}</td>
                            <td><code>{{ $log->product->barcode ?? '-' }}</code></td>
                            @if(auth()->user()->isSuperAdmin())
                                <td>{{ $log->product->branch->name ?? '-' }}</td>
                            @endif
                            <td class="text-center">
                                <span class="badge {{ $log->type === 'IN' ? 'badge-success' : 'badge-danger' }} badge-pill px-3">
                                    {{ $log->type === 'IN' ? '▲ Masuk' : '▼ Keluar' }}
                                </span>
                            </td>
                            <td class="text-center"><strong>{{ $log->qty }}</strong></td>
                            <td>{{ $log->note ?? '-' }}</td>
                            <td>{{ $log->user->name ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">Tidak ada riwayat</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($logs->hasPages())
        <div class="card-footer">{{ $logs->links() }}</div>
    @endif
</div>
@endsection
