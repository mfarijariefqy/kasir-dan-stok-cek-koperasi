@extends('layouts.app')

@section('title', 'Stok Masuk')
@section('page-title', 'Input Stok Masuk')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('stock.index') }}">Stok Barang</a></li>
    <li class="breadcrumb-item active">Stok Masuk</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-plus-circle mr-2 text-success"></i>Form Stok Masuk</h3>
            </div>
            <div class="card-body">
                <!-- Barcode Scan / Nama Produk -->
                <div class="form-group">
                    <label>Scan Barcode atau Ketik Nama Produk</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                        <input type="text" id="productSearch" class="form-control"
                            placeholder="Scan barcode (Enter) atau ketik nama produk..." autocomplete="off" autofocus>
                        <div class="input-group-append">
                            <button type="button" class="btn btn-primary" id="btnSearch">
                                <i class="fas fa-search"></i> Cari
                            </button>
                        </div>
                    </div>
                    <small class="text-muted">
                        <i class="fas fa-barcode mr-1"></i> Scan barcode → pilih otomatis &nbsp;|&nbsp;
                        <i class="fas fa-keyboard mr-1"></i> Ketik 2 huruf → muncul daftar pilihan
                    </small>
                    <!-- Dropdown hasil pencarian nama -->
                    <div id="searchDropdown" class="list-group mt-1"
                        style="display:none; position:absolute; z-index:999; width:calc(100% - 30px); max-height:220px; overflow-y:auto;"></div>
                </div>
            </div>

            <form action="{{ route('stock.store') }}" method="POST" id="stockForm">
                @csrf
                <input type="hidden" name="product_id" id="productId" value="{{ $product?->id }}">

                <div class="card-body pt-0">
                    @if($product)
                    <div class="alert alert-info" id="productDisplay">
                        <strong id="productName">{{ $product->name }}</strong><br>
                        Stok saat ini: <strong id="productStock">{{ $product->stock_qty }} {{ $product->unit }}</strong>
                    </div>
                    @else
                    <div class="alert alert-secondary" id="productDisplay">
                        <i class="fas fa-info-circle mr-1"></i> Scan produk terlebih dahulu
                    </div>
                    @endif

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Jumlah Masuk <span class="text-danger">*</span></label>
                                <input type="number" name="qty" id="qty"
                                    class="form-control @error('qty') is-invalid @enderror"
                                    value="{{ old('qty', 1) }}" min="1" required>
                                @error('qty') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group">
                                <label>Tanggal & Jam Masuk <span class="text-danger">*</span></label>
                                <input type="datetime-local" name="logged_at"
                                    class="form-control @error('logged_at') is-invalid @enderror"
                                    value="{{ old('logged_at', now()->format('Y-m-d\TH:i')) }}" required>
                                @error('logged_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Keterangan</label>
                        <input type="text" name="note" class="form-control"
                            value="{{ old('note') }}" placeholder="Contoh: Barang dari supplier, dll">
                    </div>
                </div>

                <div class="card-footer d-flex justify-content-between">
                    <a href="{{ route('stock.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali
                    </a>
                    <button type="submit" class="btn btn-success" id="btnSimpan" {{ $product ? '' : 'disabled' }}>
                        <i class="fas fa-save mr-1"></i> Simpan Stok Masuk
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const formatRp = v => 'Rp ' + parseInt(v).toLocaleString('id-ID');

function selectProduct(p) {
    document.getElementById('productId').value = p.id;
    document.getElementById('productSearch').value = p.name;
    document.getElementById('productDisplay').className = 'alert alert-info';
    document.getElementById('productDisplay').innerHTML =
        `<i class="fas fa-box mr-1"></i> <strong>${p.name}</strong>` +
        `<br><small>Barcode: <code>${p.barcode || '-'}</code> &nbsp;|&nbsp; ` +
        `Harga Beli: ${formatRp(p.buy_price)} &nbsp;|&nbsp; ` +
        `Stok saat ini: <strong>${p.stock_qty} ${p.unit}</strong></small>`;
    document.getElementById('btnSimpan').disabled = false;
    document.getElementById('searchDropdown').style.display = 'none';
    document.getElementById('qty').focus();
}

function clearProduct() {
    document.getElementById('productId').value = '';
    document.getElementById('productDisplay').className = 'alert alert-secondary';
    document.getElementById('productDisplay').innerHTML =
        '<i class="fas fa-info-circle mr-1"></i> Scan atau cari produk terlebih dahulu';
    document.getElementById('btnSimpan').disabled = true;
}

async function searchByBarcode(barcode) {
    const res  = await fetch(`/api/products/search?barcode=${encodeURIComponent(barcode)}`);
    const data = await res.json();

    if (data.length === 1) {
        selectProduct(data[0]);
    } else if (data.length > 1) {
        showDropdown(data);
    } else {
        clearProduct();
        document.getElementById('productDisplay').className = 'alert alert-warning';
        document.getElementById('productDisplay').innerHTML =
            `<i class="fas fa-exclamation-triangle mr-1"></i> Produk dengan barcode "<strong>${barcode}</strong>" tidak ditemukan`;
    }
}

async function searchByName(term) {
    if (term.length < 2) { document.getElementById('searchDropdown').style.display = 'none'; return; }
    const res  = await fetch(`/api/products/search?q=${encodeURIComponent(term)}`);
    const data = await res.json();
    showDropdown(data);
}

function showDropdown(data) {
    const dd = document.getElementById('searchDropdown');
    if (data.length === 0) {
        dd.innerHTML = '<a class="list-group-item list-group-item-action disabled text-muted">'
            + '<i class="fas fa-times mr-1"></i> Produk tidak ditemukan</a>';
    } else {
        dd.innerHTML = data.map(p => `
            <a href="#" class="list-group-item list-group-item-action search-result-item"
                data-product='${JSON.stringify(p)}'>
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong>${p.name}</strong>
                        ${p.barcode ? `<code class="ml-2 small">${p.barcode}</code>` : ''}
                        <small class="text-muted ml-1">[${p.branch_name}]</small>
                    </div>
                    <small class="text-muted">Stok: <strong>${p.stock_qty}</strong> ${p.unit}</small>
                </div>
            </a>`).join('');
    }
    dd.style.display = 'block';
}

// Enter = barcode scan (exact match)
document.getElementById('productSearch').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        const v = this.value.trim();
        if (v) { document.getElementById('searchDropdown').style.display = 'none'; searchByBarcode(v); }
    }
});

// Ketik = live search by name
document.getElementById('productSearch').addEventListener('input', function() {
    clearProduct();
    searchByName(this.value.trim());
});

// Tombol Cari = coba barcode dulu, kalau tidak ada cari by name
document.getElementById('btnSearch').addEventListener('click', function() {
    const v = document.getElementById('productSearch').value.trim();
    if (v) searchByBarcode(v);
});

// Pre-fill jika produk sudah di-load dari server (via URL ?barcode=)
@if($product)
document.getElementById('productSearch').value = '{{ $product->name }}';
@endif

// Klik item di dropdown
document.addEventListener('click', function(e) {
    const item = e.target.closest('.search-result-item');
    if (item) {
        e.preventDefault();
        selectProduct(JSON.parse(item.dataset.product));
        return;
    }
    // Tutup dropdown klik di luar
    if (!e.target.closest('#productSearch') && !e.target.closest('#searchDropdown')) {
        document.getElementById('searchDropdown').style.display = 'none';
    }
});
</script>
@endpush
