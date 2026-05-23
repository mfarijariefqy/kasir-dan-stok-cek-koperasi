@extends('layouts.app')

@section('title', 'Tambah Cabang')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Tambah Cabang Baru</h3>
            </div>
            <form action="{{ route('branches.store') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="form-group">
                        <label>Nama Cabang <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name') }}" placeholder="Contoh: Kantor Pusat" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <label>Alamat</label>
                        <textarea name="address" class="form-control @error('address') is-invalid @enderror"
                            rows="3" placeholder="Alamat lengkap cabang">{{ old('address') }}</textarea>
                        @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <label>Telepon</label>
                        <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                            value="{{ old('phone') }}" placeholder="Contoh: 021-12345678">
                        @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" class="custom-control-input" id="is_active"
                                name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="is_active">Cabang Aktif</label>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary mr-2">
                        <i class="fas fa-save mr-1"></i> Simpan
                    </button>
                    <a href="{{ route('branches.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
