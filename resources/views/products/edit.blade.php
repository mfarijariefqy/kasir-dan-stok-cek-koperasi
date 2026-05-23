@extends('layouts.app')

@section('title', 'Edit Produk')
@section('page-title', 'Edit Produk')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Produk</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Edit Produk: {{ $product->name }}</h3>
            </div>
            <form action="{{ route('products.update', $product) }}" method="POST">
                @csrf @method('PUT')
                <div class="card-body">
                    <div class="form-group">
                        <label>Barcode</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                            </div>
                            <input type="text" name="barcode" id="barcode"
                                class="form-control @error('barcode') is-invalid @enderror"
                                value="{{ old('barcode', $product->barcode) }}"
                                placeholder="Scan barcode atau input manual (opsional)"
                                autocomplete="off">
                            @error('barcode') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <small class="text-muted">Kosongkan jika tidak ada barcode</small>
                    </div>

                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label>Nama Produk <span class="text-danger">*</span></label>
                                <input type="text" name="name"
                                    class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name', $product->name) }}" required>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Satuan <span class="text-danger">*</span></label>
                                <select name="unit" class="form-control @error('unit') is-invalid @enderror" required>
                                    <option value="">-- Pilih Satuan --</option>
                                    @foreach($units as $u)
                                        <option value="{{ $u->name }}" {{ old('unit', $product->unit) === $u->name ? 'selected' : '' }}>
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
                                        <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>
                                            {{ $cat->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Cabang</label>
                                @if(auth()->user()->isSuperAdmin())
                                    <select name="branch_id" class="form-control @error('branch_id') is-invalid @enderror">
                                        <option value="">-- Pilih Cabang --</option>
                                        @foreach($branches as $branch)
                                            <option value="{{ $branch->id }}" {{ old('branch_id', $product->branch_id) == $branch->id ? 'selected' : '' }}>
                                                {{ $branch->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                @else
                                    <input type="text" class="form-control" value="{{ auth()->user()->branch->name ?? '-' }}" readonly>
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
                                        value="{{ old('buy_price', $product->buy_price) }}" min="0" step="100" required>
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
                                        value="{{ old('sell_price', $product->sell_price) }}" min="0" step="100" required>
                                    @error('sell_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Stok Saat Ini</label>
                                <input type="text" class="form-control"
                                    value="{{ $product->stock_qty }} {{ $product->unit }}" readonly>
                                <small class="text-muted">Ubah stok via menu Stok Masuk</small>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" class="custom-control-input" id="is_active"
                                name="is_active" value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="is_active">Produk Aktif</label>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-between">
                    <a href="{{ route('products.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Update Produk
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
