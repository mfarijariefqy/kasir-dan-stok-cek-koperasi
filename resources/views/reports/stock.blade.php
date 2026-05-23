@extends('layouts.app')

@section('title', 'Laporan Stok Barang')
@section('page-title', 'Laporan Stok Barang')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Laporan Stok</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header"><h3 class="card-title">Filter Laporan Stok</h3></div>
    <div class="card-body">
        <form method="GET" class="row align-items-end">
            <div class="col-md-2 mb-2">
                <label class="small mb-1">Dari Tanggal</label>
                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $dateFrom }}">
            </div>
            <div class="col-md-2 mb-2">
                <label class="small mb-1">Sampai Tanggal</label>
                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $dateTo }}">
            </div>
            <div class="col-md-2 mb-2">
                <label class="small mb-1">Kategori</label>
                <select name="category_id" class="form-control form-control-sm">
                    <option value="">Semua</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
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
            <div class="col-md-4 mb-2 d-flex align-items-end gap-1">
                <button type="submit" class="btn btn-info btn-sm mr-1">
                    <i class="fas fa-search mr-1"></i> Filter
                </button>
                <a href="{{ route('reports.stock.export', array_merge(request()->all(), ['format' => 'excel'])) }}"
                    class="btn btn-success btn-sm mr-1">
                    <i class="fas fa-file-excel mr-1"></i> Excel
                </a>
                <a href="{{ route('reports.stock.export', array_merge(request()->all(), ['format' => 'pdf'])) }}"
                    class="btn btn-danger btn-sm">
                    <i class="fas fa-file-pdf mr-1"></i> PDF
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Laporan Stok Periode {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}</h3>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>#</th>
                        <th>Nama Produk</th>
                        <th>Barcode</th>
                        <th>Kategori</th>
                        @if(auth()->user()->isSuperAdmin()) <th>Cabang</th> @endif
                        <th>Satuan</th>
                        <th class="text-center">Stok Awal</th>
                        <th class="text-center text-success">Masuk</th>
                        <th class="text-center text-danger">Keluar</th>
                        <th class="text-center">Stok Akhir</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $i => $product)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td><strong>{{ $product->name }}</strong></td>
                            <td><code>{{ $product->barcode ?? '-' }}</code></td>
                            <td>{{ $product->category->name ?? '-' }}</td>
                            @if(auth()->user()->isSuperAdmin()) <td>{{ $product->branch->name ?? '-' }}</td> @endif
                            <td>{{ $product->unit }}</td>
                            <td class="text-center">{{ $product->stok_awal }}</td>
                            <td class="text-center text-success font-weight-bold">+{{ $product->stok_masuk }}</td>
                            <td class="text-center text-danger font-weight-bold">-{{ $product->stok_keluar }}</td>
                            <td class="text-center">
                                <span class="badge {{ $product->stok_akhir <= 0 ? 'badge-danger' : ($product->stok_akhir <= 5 ? 'badge-warning' : 'badge-success') }} px-3">
                                    {{ $product->stok_akhir }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="11" class="text-center text-muted py-4">Tidak ada data produk</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
