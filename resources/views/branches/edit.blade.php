@extends('layouts.app')

@section('title', 'Edit Cabang')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Edit Cabang: {{ $branch->name }}</h3>
            </div>
            <form action="{{ route('branches.update', $branch) }}" method="POST">
                @csrf @method('PUT')
                <div class="card-body">
                    <div class="form-group">
                        <label>Nama Cabang <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name', $branch->name) }}" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <label>Alamat</label>
                        <textarea name="address" class="form-control @error('address') is-invalid @enderror"
                            rows="3">{{ old('address', $branch->address) }}</textarea>
                        @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <label>Telepon</label>
                        <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                            value="{{ old('phone', $branch->phone) }}">
                        @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" class="custom-control-input" id="is_active"
                                name="is_active" value="1" {{ old('is_active', $branch->is_active) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="is_active">Cabang Aktif</label>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-between">
                    <a href="{{ route('branches.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
