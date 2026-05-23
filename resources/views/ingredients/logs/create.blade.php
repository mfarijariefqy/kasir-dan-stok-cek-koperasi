@extends('layouts.app')

@section('title', 'Atur Stok Bahan')

@section('page-title', 'Atur Stok Bahan')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('ingredient-logs.index') }}">Log Stok</a></li>
    <li class="breadcrumb-item active">Atur Stok</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Form Atur Stok</h3>
    </div>
    <form action="{{ route('ingredient-logs.store') }}" method="POST">
        @csrf
        <div class="card-body">
            <div class="form-group">
                <label for="ingredient_id">Bahan <span class="text-danger">*</span></label>
                <select class="form-control @error('ingredient_id') is-invalid @enderror" id="ingredient_id" name="ingredient_id" required>
                    <option value="">Pilih Bahan</option>
                    @foreach($ingredients as $ingredient)
                        <option value="{{ $ingredient->id }}" data-stock="{{ $ingredient->stock }}" data-unit="{{ $ingredient->unit }}" {{ old('ingredient_id') == $ingredient->id ? 'selected' : '' }}>
                            {{ $ingredient->name }} (Stok: {{ number_format($ingredient->stock, 2) }} {{ $ingredient->unit }})
                        </option>
                    @endforeach
                </select>
                @error('ingredient_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="type">Tipe <span class="text-danger">*</span></label>
                <select class="form-control @error('type') is-invalid @enderror" id="type" name="type" required>
                    <option value="">Pilih Tipe</option>
                    <option value="IN" {{ old('type') == 'IN' ? 'selected' : '' }}>Masuk (Tambah Stok)</option>
                    <option value="OUT" {{ old('type') == 'OUT' ? 'selected' : '' }}>Keluar (Kurangi Stok)</option>
                    <option value="ADJUST" {{ old('type') == 'ADJUST' ? 'selected' : '' }}>Penyesuaian (Set Stok)</option>
                </select>
                @error('type')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="qty">Jumlah <span class="text-danger">*</span></label>
                <input type="number" class="form-control @error('qty') is-invalid @enderror" id="qty" name="qty" value="{{ old('qty') }}" min="0.01" step="0.01" required>
                @error('qty')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="form-text text-muted" id="qtyHelp">
                    Untuk tipe "Masuk" dan "Keluar", masukkan jumlah yang akan ditambah/dikurangi. Untuk "Penyesuaian", masukkan nilai stok baru.
                </small>
            </div>

            <div class="form-group">
                <label for="note">Catatan</label>
                <textarea class="form-control @error('note') is-invalid @enderror" id="note" name="note" rows="3">{{ old('note') }}</textarea>
                @error('note')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="alert alert-info" id="currentStockInfo" style="display: none;">
                <strong>Stok Saat Ini:</strong> <span id="currentStockValue">-</span>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Simpan
            </button>
            <a href="{{ route('ingredient-logs.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
$('#ingredient_id').change(function() {
    const selected = $(this).find('option:selected');
    const stock = selected.data('stock');
    const unit = selected.data('unit');
    
    if (stock !== undefined) {
        $('#currentStockValue').text(stock + ' ' + unit);
        $('#currentStockInfo').show();
    } else {
        $('#currentStockInfo').hide();
    }
});
</script>
@endpush
