@extends('layouts.app')

@section('title', 'Import Produk')
@section('page-title', 'Import Produk dari Excel')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Produk</a></li>
    <li class="breadcrumb-item active">Import Excel</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">

        {{-- Panduan --}}
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-info-circle mr-2"></i>Panduan Import</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <ol class="mb-2">
                    <li>Download template Excel terlebih dahulu menggunakan tombol di bawah.</li>
                    <li>Isi data produk pada sheet <strong>Template Import</strong>. <strong>Hapus baris contoh (baris 2)</strong> sebelum upload.</li>
                    <li>Gunakan sheet <strong>Referensi</strong> untuk melihat daftar kategori, satuan, dan cabang yang valid.</li>
                    <li>Kolom wajib: <code>nama_produk</code>, <code>satuan</code>, <code>harga_beli</code>, <code>harga_jual</code>.</li>
                    <li>Kolom <code>barcode</code> bersifat opsional. Jika diisi, harus unik per cabang.</li>
                    <li>Kolom <code>status</code>: isi <code>aktif</code> atau <code>nonaktif</code> (default: aktif jika kosong).</li>
                    <li>Kolom <code>stok_awal</code>: isi angka atau kosongkan (default: 0). Update stok gunakan menu <strong>Stok Masuk</strong>.</li>
                    @if(auth()->user()->isSuperAdmin())
                        <li>Kolom <code>cabang</code> <strong>wajib diisi</strong> sesuai nama di sheet Referensi.</li>
                    @else
                        <li>Kolom <code>cabang</code> diabaikan — semua produk otomatis masuk ke cabang Anda: <strong>{{ auth()->user()->branch->name ?? '-' }}</strong>.</li>
                    @endif
                </ol>
                <div class="alert alert-warning mb-0 py-2">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    Satuan yang belum ada di master akan <strong>dibuat otomatis</strong>. Kategori yang tidak ditemukan akan <strong>dilaporkan sebagai error</strong> (baris dilewati).
                </div>
            </div>
        </div>

        {{-- Form Upload --}}
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Upload File Excel</h3>
                <a href="{{ route('products.import.template') }}" class="btn btn-success btn-sm">
                    <i class="fas fa-download mr-1"></i> Download Template
                </a>
            </div>
            <form action="{{ route('products.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="card-body">
                    @error('file')
                        <div class="alert alert-danger py-2">{{ $message }}</div>
                    @enderror

                    <div class="form-group mb-0">
                        <label>File Excel <span class="text-danger">*</span></label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="importFile"
                                name="file" accept=".xlsx,.xls,.csv" required>
                            <label class="custom-file-label" for="importFile">Pilih file (.xlsx, .xls, .csv)...</label>
                        </div>
                        <small class="text-muted">Maksimal 5 MB</small>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-between align-items-center">
                    <a href="{{ route('products.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali
                    </a>
                    <button type="submit" class="btn btn-primary" id="btnImport">
                        <i class="fas fa-upload mr-1"></i> Proses Import
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
    $('#importFile').on('change', function () {
        const name = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').text(name || 'Pilih file (.xlsx, .xls, .csv)...');
    });

    $('form').on('submit', function () {
        $('#btnImport').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Memproses...');
    });
</script>
@endpush
