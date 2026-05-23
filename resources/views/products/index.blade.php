@extends('layouts.app')

@section('title', 'Data Produk')
@section('page-title', 'Data Produk')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Produk</li>
@endsection

@section('content')

{{-- Import result flash messages --}}
@if(session('import_count') !== null)
    <div class="alert alert-success alert-dismissible fade show">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <i class="fas fa-check-circle mr-1"></i>
        <strong>Import selesai.</strong> {{ session('import_count') }} produk berhasil diimport.
        @if(session('import_errors') && count(session('import_errors')) > 0)
            <hr class="my-2">
            <strong>{{ count(session('import_errors')) }} baris dilewati:</strong>
            <ul class="mb-0 mt-1">
                @foreach(session('import_errors') as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        @endif
    </div>
@endif

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Daftar Produk</h3>
                <div class="card-tools">
                    <a href="{{ route('products.import.form') }}" class="btn btn-success btn-sm mr-1">
                        <i class="fas fa-file-excel mr-1"></i> Import Excel
                    </a>
                    <a href="{{ route('products.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus mr-1"></i> Tambah Produk
                    </a>
                </div>
            </div>
            <div class="card-body border-bottom">
                <form method="GET" class="row align-items-end">
                    <div class="col-md-4 mb-2">
                        <label class="small mb-1">Cari Nama / Barcode</label>
                        <input type="text" name="search" class="form-control form-control-sm"
                            value="{{ request('search') }}" placeholder="Nama produk atau barcode...">
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
                        <label class="small mb-1">Status</label>
                        <select name="status" class="form-control form-control-sm">
                            <option value="">Semua</option>
                            <option value="aktif" {{ request('status') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                            <option value="nonaktif" {{ request('status') === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <button type="submit" class="btn btn-info btn-sm mr-1">
                            <i class="fas fa-search mr-1"></i> Filter
                        </button>
                        <a href="{{ route('products.index') }}" class="btn btn-secondary btn-sm">Reset</a>
                    </div>
                </form>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th width="50">#</th>
                                <th>Barcode</th>
                                <th>Nama Produk</th>
                                <th>Kategori</th>
                                <th>Satuan</th>
                                <th class="text-right">Harga Beli</th>
                                <th class="text-right">Harga Jual</th>
                                <th class="text-center">Stok</th>
                                @if(auth()->user()->isSuperAdmin())
                                    <th>Cabang</th>
                                @endif
                                <th width="70">Status</th>
                                <th width="100">Aksi</th>
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
                                    <td class="text-right">Rp {{ number_format($product->buy_price, 0, ',', '.') }}</td>
                                    <td class="text-right">Rp {{ number_format($product->sell_price, 0, ',', '.') }}</td>
                                    <td class="text-center">
                                        <span class="badge {{ $product->stock_qty <= 5 ? 'badge-danger' : ($product->stock_qty <= 10 ? 'badge-warning' : 'badge-success') }}">
                                            {{ $product->stock_qty }}
                                        </span>
                                    </td>
                                    @if(auth()->user()->isSuperAdmin())
                                        <td>{{ $product->branch->name ?? '-' }}</td>
                                    @endif
                                    <td>
                                        <span class="badge {{ $product->is_active ? 'badge-success' : 'badge-secondary' }}">
                                            {{ $product->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('products.edit', $product) }}" class="btn btn-warning btn-xs">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('products.destroy', $product) }}" method="POST" class="d-inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-xs btn-delete"
                                                data-name="{{ $product->name }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="12" class="text-center text-muted py-4">
                                        <i class="fas fa-box fa-2x mb-2 d-block"></i>
                                        Tidak ada produk ditemukan
                                    </td>
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
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).on('click', '.btn-delete', function (e) {
        e.preventDefault();
        const form = $(this).closest('form');
        const name = $(this).data('name');
        Swal.fire({
            title: 'Hapus Produk?',
            text: `"${name}" akan dihapus permanen.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then(result => { if (result.isConfirmed) form.submit(); });
    });
</script>
@endpush
