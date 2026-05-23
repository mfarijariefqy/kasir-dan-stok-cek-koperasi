@extends('layouts.app')

@section('title', 'Daftar Stok Barang')
@section('page-title', 'Daftar Stok Barang')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Stok Barang</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Stok Semua Produk</h3>
        @can('manage-stock')
            <a href="{{ route('stock.in') }}" class="btn btn-success btn-sm">
                <i class="fas fa-plus mr-1"></i> Stok Masuk
            </a>
        @endcan
    </div>
    <div class="card-body border-bottom">
        <form method="GET" class="row align-items-end">
            <div class="col-md-4 mb-2">
                <label class="small mb-1">Cari Nama / Barcode</label>
                <input type="text" name="search" class="form-control form-control-sm"
                    value="{{ request('search') }}" placeholder="Cari produk...">
            </div>
            <div class="col-md-2 mb-2">
                <label class="small mb-1">Kategori</label>
                <select name="category_id" class="form-control form-control-sm">
                    <option value="">Semua</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
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
                <button type="submit" class="btn btn-info btn-sm mr-1">
                    <i class="fas fa-search mr-1"></i> Filter
                </button>
                <a href="{{ route('stock.index') }}" class="btn btn-secondary btn-sm">Reset</a>
            </div>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>#</th>
                        <th>Barcode</th>
                        <th>Nama Produk</th>
                        <th>Kategori</th>
                        <th>Satuan</th>
                        @if(auth()->user()->isSuperAdmin())
                            <th>Cabang</th>
                        @endif
                        <th class="text-center">Stok</th>
                        <th>Status</th>
                        @can('manage-stock')
                            <th width="80">Aksi</th>
                        @endcan
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td>{{ $products->firstItem() + $loop->index }}</td>
                            <td><code>{{ $product->barcode ?? '-' }}</code></td>
                            <td><strong>{{ $product->name }}</strong></td>
                            <td>{{ $product->category->name ?? '-' }}</td>
                            <td>{{ $product->unit }}</td>
                            @if(auth()->user()->isSuperAdmin())
                                <td>{{ $product->branch->name ?? '-' }}</td>
                            @endif
                            <td class="text-center">
                                <span class="badge badge-pill {{ $product->stock_qty <= 0 ? 'badge-danger' : ($product->stock_qty <= 5 ? 'badge-warning' : 'badge-success') }} px-3">
                                    {{ $product->stock_qty }}
                                </span>
                            </td>
                            <td>
                                <span class="badge {{ $product->is_active ? 'badge-success' : 'badge-secondary' }}">
                                    {{ $product->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            @can('manage-stock')
                                <td>
                                    <a href="{{ route('stock.in', ['barcode' => $product->barcode]) }}"
                                        class="btn btn-success btn-xs" title="Tambah Stok">
                                        <i class="fas fa-plus"></i>
                                    </a>
                                </td>
                            @endcan
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">Tidak ada produk ditemukan</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($products->hasPages())
        <div class="card-footer">{{ $products->links() }}</div>
    @endif
</div>
@endsection
