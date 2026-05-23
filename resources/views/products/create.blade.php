@extends('layouts.app')

@section('title', 'Tambah Produk')
@section('page-title', 'Tambah Produk')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Produk</a></li>
    <li class="breadcrumb-item active">Tambah</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Form Tambah Produk</h3>
            </div>
            <form action="{{ route('products.store') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="form-group">
                        <label>Barcode</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                            </div>
                            <input type="text" name="barcode" id="barcode"
                                class="form-control @error('barcode') is-invalid @enderror"
                                value="{{ old('barcode') }}"
                                placeholder="Scan barcode atau input manual (opsional)"
                                autocomplete="off">
                            @error('barcode') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <small class="text-muted">Arahkan kursor ke field ini lalu scan barcode dengan scanner USB</small>
                    </div>

                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label>Nama Produk <span class="text-danger">*</span></label>
                                <input type="text" name="name"
                                    class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name') }}" placeholder="Nama produk" required>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Satuan <span class="text-danger">*</span></label>
                                <select name="unit" class="form-control @error('unit') is-invalid @enderror" required>
                                    <option value="">-- Pilih Satuan --</option>
                                    @foreach($units as $u)
                                        <option value="{{ $u->name }}" {{ old('unit') === $u->name ? 'selected' : '' }}>
                                            {{ strtoupper($u->name) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('unit') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Kategori</label>
                                <select name="category_id" class="form-control @error('category_id') is-invalid @enderror">
                                    <option value="">-- Pilih Kategori --</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                            {{ $cat->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Cabang <span class="text-danger">*</span></label>
                                @if(auth()->user()->isSuperAdmin())
                                    <select name="branch_id" class="form-control @error('branch_id') is-invalid @enderror">
                                        <option value="">-- Pilih Cabang --</option>
                                        @foreach($branches as $branch)
                                            <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                                {{ $branch->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                @else
                                    <input type="text" class="form-control" value="{{ auth()->user()->branch->name ?? '-' }}" readonly>
                                    <input type="hidden" name="branch_id" value="{{ auth()->user()->branch_id }}">
                                @endif
                                @error('branch_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Harga Beli <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text">Rp</span></div>
                                    <input type="number" name="buy_price"
                                        class="form-control @error('buy_price') is-invalid @enderror"
                                        value="{{ old('buy_price', 0) }}" min="0" step="100" required>
                                    @error('buy_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Harga Jual <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text">Rp</span></div>
                                    <input type="number" name="sell_price"
                                        class="form-control @error('sell_price') is-invalid @enderror"
                                        value="{{ old('sell_price', 0) }}" min="0" step="100" required>
                                    @error('sell_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Stok Awal <span class="text-danger">*</span></label>
                                <input type="number" name="stock_qty"
                                    class="form-control @error('stock_qty') is-invalid @enderror"
                                    value="{{ old('stock_qty', 0) }}" min="0" required>
                                @error('stock_qty') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" class="custom-control-input" id="is_active"
                                name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="is_active">Produk Aktif</label>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary mr-2">
                        <i class="fas fa-save mr-1"></i> Simpan Produk
                    </button>
                    <a href="{{ route('products.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
