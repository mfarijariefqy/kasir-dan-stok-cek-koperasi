@extends('layouts.app')

@section('title', 'Kasir - Input Transaksi')
@section('page-title', 'Kasir')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Input Transaksi</li>
@endsection

@section('content')
<form id="trxForm" action="{{ route('transactions.store') }}" method="POST">
@csrf

{{-- branch_id = cabang tempat transaksi dicatat (bukan cabang asal barang) --}}
<input type="hidden" name="branch_id"          id="trxBranchId"    value="{{ $defaultBranchId ?? '' }}">
<input type="hidden" name="save_default_branch" id="hdnSaveDefault" value="0">

{{-- ── Top Bar: Cabang Transaksi + Default ─────────────────────────────── --}}
<div class="card mb-3">
    <div class="card-body py-2">
        <div class="d-flex flex-wrap align-items-center" style="gap:14px;">
            <span class="font-weight-bold text-nowrap">
                <i class="fas fa-store mr-1 text-primary"></i> Cabang Transaksi:
            </span>

            @if(auth()->user()->isSuperAdmin())
                <select id="trxBranchSelect" class="form-control form-control-sm" style="max-width:220px;">
                    <option value="">-- Pilih Cabang --</option>
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}" {{ $defaultBranchId == $b->id ? 'selected' : '' }}>
                            {{ $b->name }}
                        </option>
                    @endforeach
                </select>
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" id="chkSaveDefault"
                        {{ $defaultBranchId ? 'checked' : '' }}>
                    <label class="custom-control-label small" for="chkSaveDefault">
                        Simpan sebagai default
                    </label>
                </div>
            @else
                {{-- Non-super-admin: cabang terkunci ke cabang user --}}
                <span class="badge badge-primary px-3 py-2">
                    <i class="fas fa-map-marker-alt mr-1"></i>
                    {{ auth()->user()->branch->name ?? 'Belum ada cabang' }}
                </span>
            @endif

            <span id="trxBranchBadge" class="badge px-3 py-2
                {{ $defaultBranchId ? 'badge-success' : 'badge-secondary' }} ml-auto">
                @if($defaultBranchId)
                    @php $sel = $branches->firstWhere('id', $defaultBranchId) @endphp
                    <i class="fas fa-check-circle mr-1"></i> Transaksi: {{ $sel->name ?? '-' }}
                @else
                    <i class="fas fa-exclamation-circle mr-1"></i> Belum pilih cabang transaksi
                @endif
            </span>
        </div>
    </div>
</div>

<div class="row">
    {{-- ── Kiri: Scan / Cari Produk ──────────────────────────────────────── --}}
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-barcode mr-2"></i>Scan / Cari Produk</h3>
            </div>
            <div class="card-body">

                {{-- Branch selector pencarian — selalu tampil, bebas diganti --}}
                <div class="form-group">
                    <label class="small font-weight-bold mb-1">
                        <i class="fas fa-search-location mr-1 text-info"></i>
                        Cari dari Cabang
                    </label>
                    <select id="searchBranchSelect" class="form-control form-control-sm">
                        <option value="">-- Semua Cabang --</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" {{ $defaultBranchId == $b->id ? 'selected' : '' }}>
                                {{ $b->name }}
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">Ganti cabang kapan saja — item di keranjang tidak terhapus</small>
                </div>

                <div class="input-group mb-3">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                    </div>
                    <input type="text" id="barcodeInput" class="form-control form-control-lg"
                        placeholder="Scan barcode atau ketik nama produk..." autocomplete="off" autofocus>
                    <div class="input-group-append">
                        <button type="button" class="btn btn-primary" id="btnSearchProduct">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>

                <div id="searchResults" class="list-group mb-3"
                    style="display:none;max-height:200px;overflow-y:auto;"></div>

                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="thead-light">
                            <tr>
                                <th>Produk</th>
                                <th width="90">Cabang</th>
                                <th width="80">Barcode</th>
                                <th width="100">Harga</th>
                                <th width="90">Qty</th>
                                <th width="110" class="text-right">Subtotal</th>
                                <th width="40"></th>
                            </tr>
                        </thead>
                        <tbody id="cartBody">
                            <tr>
                                <td colspan="7" class="text-center text-muted py-3">
                                    <i class="fas fa-shopping-cart mr-1"></i> Keranjang kosong
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Kanan: Info Transaksi ─────────────────────────────────────────── --}}
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-primary">
                <h3 class="card-title text-white">
                    <i class="fas fa-cash-register mr-2"></i>Info Transaksi
                </h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label>Nama Pelanggan</label>
                    <input type="hidden" name="customer_id" id="customerIdInput" value="">
                    <div class="input-group">
                        <input type="text" id="customerNameInput" name="customer_name"
                            class="form-control" placeholder="Ketik nama atau pilih dari daftar..."
                            autocomplete="off">
                        <div class="input-group-append">
                            <button type="button" class="btn btn-outline-secondary btn-sm"
                                id="btnClearCustomer" title="Hapus" style="display:none;">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    {{-- Dropdown autocomplete pelanggan tetap --}}
                    <div id="customerDropdown" class="list-group shadow-sm"
                        style="position:absolute;z-index:1050;display:none;max-height:220px;overflow-y:auto;width:calc(100% - 30px);">
                    </div>
                    <small class="text-muted">Pilih dari daftar pelanggan tetap atau ketik bebas</small>
                    <div id="customerLinked" class="mt-1" style="display:none;">
                        <span class="badge badge-success"><i class="fas fa-link mr-1"></i> Terhubung ke akun pelanggan</span>
                    </div>
                </div>
                <div class="form-group">
                    <label>Cara Bayar <span class="text-danger">*</span></label>
                    <div class="d-flex gap-2">
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" name="payment_method" id="pmCash" value="Cash"
                                class="custom-control-input" checked>
                            <label class="custom-control-label" for="pmCash">
                                <span class="badge badge-success badge-pill px-3 py-2">💵 Cash</span>
                            </label>
                        </div>
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" name="payment_method" id="pmTempo" value="Tempo"
                                class="custom-control-input">
                            <label class="custom-control-label" for="pmTempo">
                                <span class="badge badge-warning badge-pill px-3 py-2">🕐 Tempo</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div id="statusInfo" class="alert alert-success py-2 mb-3">
                    <i class="fas fa-check-circle mr-1"></i> Status: <strong>Lunas</strong>
                </div>

                <hr>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h5 class="mb-0">TOTAL</h5>
                    <h4 class="mb-0 text-primary" id="totalDisplay">Rp 0</h4>
                </div>
                <input type="hidden" name="total" id="totalInput" value="0">
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary btn-block btn-lg"
                    id="btnSimpan" disabled>
                    <i class="fas fa-save mr-2"></i> Simpan Transaksi
                </button>
                <a href="{{ route('transactions.index') }}" class="btn btn-secondary btn-block mt-2">
                    <i class="fas fa-times mr-1"></i> Batal
                </a>
            </div>
        </div>
    </div>
</div>
</form>
@endsection

@push('scripts')
<script>
let cart = [];
const formatRp = v => 'Rp ' + parseInt(v).toLocaleString('id-ID');

// ── Top bar: cabang transaksi (super-admin only) ────────────────────────────
@if(auth()->user()->isSuperAdmin())
const trxBranchSelect = document.getElementById('trxBranchSelect');
const chkSaveDefault  = document.getElementById('chkSaveDefault');

function syncTrxBranch() {
    const val  = trxBranchSelect.value;
    const text = trxBranchSelect.options[trxBranchSelect.selectedIndex]?.text ?? '';
    document.getElementById('trxBranchId').value = val;
    document.getElementById('hdnSaveDefault').value = chkSaveDefault.checked ? '1' : '0';

    const badge = document.getElementById('trxBranchBadge');
    if (val) {
        badge.className = 'badge badge-success px-3 py-2 ml-auto';
        badge.innerHTML = `<i class="fas fa-check-circle mr-1"></i> Transaksi: ${text}`;
        // Pre-fill search branch jika belum dipilih
        if (!document.getElementById('searchBranchSelect').value) {
            document.getElementById('searchBranchSelect').value = val;
        }
    } else {
        badge.className = 'badge badge-secondary px-3 py-2 ml-auto';
        badge.innerHTML = `<i class="fas fa-exclamation-circle mr-1"></i> Belum pilih cabang transaksi`;
    }
}

trxBranchSelect.addEventListener('change', syncTrxBranch);
chkSaveDefault.addEventListener('change', () => {
    document.getElementById('hdnSaveDefault').value = chkSaveDefault.checked ? '1' : '0';
});
@else
// Non-super-admin: branch_id sudah di-set dari backend, tidak bisa diubah
@endif

// ── Search branch: bebas diganti tanpa pengaruh ke cart ───────────────────
// (tidak ada event listener yang hapus cart)

// ── Product search ─────────────────────────────────────────────────────────
function getSearchBranchId() {
    return document.getElementById('searchBranchSelect').value;
}

function buildSearchUrl(term, isBarcode) {
    const params = new URLSearchParams();
    const b = getSearchBranchId();
    if (b) params.set('branch_id', b);
    if (isBarcode) params.set('barcode', term);
    else           params.set('q', term);
    return `/api/products/search?${params}`;
}

async function searchProduct(term, isBarcode = false) {
    const res  = await fetch(buildSearchUrl(term, isBarcode));
    const data = await res.json();

    if (isBarcode && data.length === 1) {
        addToCart(data[0]);
        document.getElementById('barcodeInput').value = '';
        document.getElementById('searchResults').style.display = 'none';
        return;
    }

    const sr = document.getElementById('searchResults');
    sr.innerHTML = data.length === 0
        ? '<a class="list-group-item disabled">Produk tidak ditemukan</a>'
        : data.map(p => `
            <a href="#" class="list-group-item list-group-item-action search-item"
                data-product='${JSON.stringify(p)}'>
                <div class="d-flex justify-content-between align-items-start">
                    <span>
                        <strong>${p.name}</strong>
                        <span class="badge badge-info ml-1">${p.unit}</span>
                        ${p.barcode ? `<code class="ml-1" style="font-size:0.78rem;">${p.barcode}</code>` : ''}
                        <small class="ml-1 text-muted">[${p.branch_name}]</small>
                    </span>
                    <span class="text-primary font-weight-bold ml-2 text-nowrap">${formatRp(p.sell_price)}</span>
                </div>
                <small class="text-muted">Stok: <strong>${p.stock_qty}</strong> ${p.unit}</small>
            </a>`).join('');
    sr.style.display = 'block';
}

// ── Cart ───────────────────────────────────────────────────────────────────
function renderCart() {
    const tbody = document.getElementById('cartBody');
    tbody.innerHTML = '';

    if (cart.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-3">'
            + '<i class="fas fa-shopping-cart mr-1"></i> Keranjang kosong</td></tr>';
        document.getElementById('btnSimpan').disabled = true;
        return;
    }

    let total = 0;
    cart.forEach((item, i) => {
        const sub = item.sell_price * item.qty;
        total += sub;
        tbody.innerHTML += `
            <tr>
                <td>
                    <strong>${item.name}</strong>
                    <br><small class="text-muted">${item.unit}</small>
                </td>
                <td>
                    <span class="badge badge-light border" style="font-size:0.75rem;">
                        ${item.branch_name}
                    </span>
                </td>
                <td><code>${item.barcode || '-'}</code></td>
                <td>${formatRp(item.sell_price)}</td>
                <td>
                    <input type="number" class="form-control form-control-sm qty-input"
                        data-index="${i}" value="${item.qty}"
                        min="1" max="${item.stock_qty}" style="width:70px">
                </td>
                <td class="text-right">${formatRp(sub)}</td>
                <td>
                    <button type="button" class="btn btn-danger btn-xs btn-remove" data-index="${i}">
                        <i class="fas fa-times"></i>
                    </button>
                </td>
            </tr>
            <input type="hidden" name="items[${i}][product_id]" value="${item.id}">
            <input type="hidden" name="items[${i}][qty]" class="hidden-qty" data-index="${i}" value="${item.qty}">`;
    });

    document.getElementById('totalDisplay').textContent = formatRp(total);
    document.getElementById('totalInput').value = total;
    document.getElementById('btnSimpan').disabled = false;
}

function addToCart(product) {
    const existing = cart.find(i => i.id === product.id);
    if (existing) {
        if (existing.qty < product.stock_qty) { existing.qty++; }
        else {
            Swal.fire('Stok Habis', `Stok ${product.name} hanya ${product.stock_qty}`, 'warning');
            return;
        }
    } else {
        if (product.stock_qty < 1) {
            Swal.fire('Stok Habis', `${product.name} sudah habis`, 'warning');
            return;
        }
        cart.push({ ...product, qty: 1 });
    }
    renderCart();
}

// ── Event listeners ────────────────────────────────────────────────────────
document.getElementById('barcodeInput').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        const v = this.value.trim();
        if (v) searchProduct(v, true);
    }
});

document.getElementById('btnSearchProduct').addEventListener('click', function() {
    const v = document.getElementById('barcodeInput').value.trim();
    if (v) searchProduct(v, false);
});

document.getElementById('barcodeInput').addEventListener('input', function() {
    if (this.value.length >= 3) searchProduct(this.value, false);
    else document.getElementById('searchResults').style.display = 'none';
});

document.addEventListener('click', function(e) {
    if (e.target.closest('.search-item')) {
        e.preventDefault();
        addToCart(JSON.parse(e.target.closest('.search-item').dataset.product));
        document.getElementById('barcodeInput').value = '';
        document.getElementById('searchResults').style.display = 'none';
        return;
    }
    if (e.target.closest('.btn-remove')) {
        cart.splice(parseInt(e.target.closest('.btn-remove').dataset.index), 1);
        renderCart();
        return;
    }
    if (!e.target.closest('#searchResults,#barcodeInput,#btnSearchProduct')) {
        document.getElementById('searchResults').style.display = 'none';
    }
});

document.addEventListener('change', function(e) {
    if (e.target.classList.contains('qty-input')) {
        const i = parseInt(e.target.dataset.index);
        cart[i].qty = Math.min(parseInt(e.target.value) || 1, cart[i].stock_qty);
        renderCart();
    }
});

$('input[name="payment_method"]').on('change', function() {
    const isTempo = this.value === 'Tempo';
    const info = document.getElementById('statusInfo');
    info.className = isTempo ? 'alert alert-warning py-2 mb-3' : 'alert alert-success py-2 mb-3';
    info.innerHTML = isTempo
        ? '<i class="fas fa-clock mr-1"></i> Status: <strong>Belum Lunas</strong> (Piutang)'
        : '<i class="fas fa-check-circle mr-1"></i> Status: <strong>Lunas</strong>';
});

// ── Customer autocomplete ──────────────────────────────────────────────────
const customerInput    = document.getElementById('customerNameInput');
const customerIdInput  = document.getElementById('customerIdInput');
const btnClearCust     = document.getElementById('btnClearCustomer');
const customerDropdown = document.getElementById('customerDropdown');
const customerLinked   = document.getElementById('customerLinked');
const customersData    = @json($customers);

let customerDebounce = null;

function clearCustomer() {
    customerIdInput.value = '';
    customerInput.value   = '';
    btnClearCust.style.display    = 'none';
    customerLinked.style.display  = 'none';
    customerDropdown.style.display = 'none';
}

customerInput.addEventListener('input', function () {
    btnClearCust.style.display = this.value ? 'inline-flex' : 'none';
    customerIdInput.value = '';
    customerLinked.style.display = 'none';

    clearTimeout(customerDebounce);
    const q = this.value.trim().toLowerCase();
    if (q.length < 1) { customerDropdown.style.display = 'none'; return; }

    customerDebounce = setTimeout(() => {
        const matches = customersData.filter(c =>
            c.name.toLowerCase().includes(q) || (c.phone && c.phone.includes(q))
        ).slice(0, 8);

        if (matches.length === 0) { customerDropdown.style.display = 'none'; return; }

        customerDropdown.innerHTML = matches.map(c => `
            <a href="#" class="list-group-item list-group-item-action py-2 px-3"
                data-id="${c.id}" data-name="${c.name}" style="font-size:13.5px;">
                <strong>${c.name}</strong>
                ${c.phone ? `<small class="text-muted ml-2">${c.phone}</small>` : ''}
            </a>`).join('');
        customerDropdown.style.display = 'block';
    }, 180);
});

customerDropdown.addEventListener('click', function (e) {
    e.preventDefault();
    const item = e.target.closest('[data-id]');
    if (! item) return;
    customerInput.value           = item.dataset.name;
    customerIdInput.value         = item.dataset.id;
    customerLinked.style.display  = 'block';
    btnClearCust.style.display    = 'inline-flex';
    customerDropdown.style.display = 'none';
});

document.addEventListener('click', function (e) {
    if (! customerInput.contains(e.target) && ! customerDropdown.contains(e.target)) {
        customerDropdown.style.display = 'none';
    }
});

btnClearCust.addEventListener('click', function () {
    clearCustomer();
    customerInput.focus();
});

</script>
@endpush
