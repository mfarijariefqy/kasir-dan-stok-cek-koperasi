@extends('layouts.app')

@section('title', 'Edit Satuan')
@section('page-title', 'Edit Satuan')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('units.index') }}">Master Satuan</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-edit mr-1"></i> Edit Satuan</h3>
            </div>
            <form action="{{ route('units.update', $unit) }}" method="POST">
                @csrf @method('PUT')
                <div class="card-body">
                    <div class="form-group">
                        <label>Nama Satuan <span class="text-danger">*</span></label>
                        <input type="text" name="name"
                            class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name', $unit->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">
                            Mengubah nama satuan akan otomatis memperbarui semua produk yang menggunakan satuan ini.
                        </small>
                    </div>
                    <div class="form-group mb-0">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="isActive"
                                name="is_active" value="1"
                                {{ old('is_active', $unit->is_active) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="isActive">Satuan Aktif</label>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-between">
                    <a href="{{ route('units.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
