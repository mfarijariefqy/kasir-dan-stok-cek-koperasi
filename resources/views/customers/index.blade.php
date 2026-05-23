@extends('layouts.app')

@section('title', 'Pelanggan Tetap')
@section('page-title', 'Pelanggan Tetap')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Pelanggan Tetap</li>
@endsection

@section('content')
<div class="row">
    {{-- Form Tambah --}}
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-user-plus mr-1"></i> Tambah Pelanggan</h3>
            </div>
            <form action="{{ route('customers.store') }}" method="POST" id="addCustomerForm">
                @csrf
                <div class="card-body">
                    <div class="form-group">
                        <label>Nama <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name') }}" placeholder="Nama lengkap pelanggan" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <label>No. HP / Telepon</label>
                        <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                            value="{{ old('phone') }}" placeholder="Contoh: 0812xxxx">
                        @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <label>Alamat</label>
                        <textarea name="address" class="form-control @error('address') is-invalid @enderror"
                            rows="2" placeholder="Alamat (opsional)">{{ old('address') }}</textarea>
                        @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <label>Catatan</label>
                        <textarea name="notes" class="form-control @error('notes') is-invalid @enderror"
                            rows="2" placeholder="Catatan tambahan (opsional)">{{ old('notes') }}</textarea>
                        @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <hr class="my-3">

                    {{-- Akun Login --}}
                    <div class="custom-control custom-switch mb-2">
                        <input type="checkbox" class="custom-control-input" id="createAccount"
                            name="create_account" value="1" {{ old('create_account') ? 'checked' : '' }}>
                        <label class="custom-control-label font-weight-bold" for="createAccount">
                            <i class="fas fa-key mr-1 text-warning"></i> Buat Akun Login
                        </label>
                    </div>
                    <div id="accountFields" style="{{ old('create_account') ? '' : 'display:none;' }}">
                        <div class="form-group">
                            <label>Username <span class="text-danger">*</span></label>
                            <input type="text" name="username" class="form-control @error('username') is-invalid @enderror"
                                value="{{ old('username') }}" placeholder="min. 4 karakter, huruf & angka">
                            @error('username') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <small class="text-muted">Digunakan pelanggan untuk login ke portal.</small>
                        </div>
                        <div class="form-group">
                            <label>Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                                placeholder="min. 6 karakter">
                            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-group mb-0">
                            <label>Konfirmasi Password <span class="text-danger">*</span></label>
                            <input type="password" name="password_confirmation" class="form-control"
                                placeholder="Ulangi password">
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-plus mr-1"></i> Tambah Pelanggan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabel --}}
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-users mr-1"></i> Daftar Pelanggan Tetap</h3>
                <div class="card-tools">
                    <form method="GET" class="d-flex">
                        <input type="text" name="q" class="form-control form-control-sm mr-1"
                            placeholder="Cari nama / HP..." value="{{ request('q') }}" style="width:180px;">
                        <button type="submit" class="btn btn-info btn-sm mr-1">
                            <i class="fas fa-search"></i>
                        </button>
                        @if(request('q'))
                            <a href="{{ route('customers.index') }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-times"></i>
                            </a>
                        @endif
                    </form>
                </div>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th width="40">#</th>
                            <th>Nama</th>
                            <th>No. HP</th>
                            <th width="90" class="text-center">Akun</th>
                            <th width="80" class="text-center">Status</th>
                            <th width="100">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customers as $c)
                            <tr>
                                <td>{{ $customers->firstItem() + $loop->index }}</td>
                                <td>
                                    <strong>{{ $c->name }}</strong>
                                    @if($c->notes)
                                        <br><small class="text-muted">{{ Str::limit($c->notes, 40) }}</small>
                                    @endif
                                </td>
                                <td>{{ $c->phone ?? '-' }}</td>
                                <td class="text-center">
                                    @if($c->hasLoginAccount())
                                        <span class="badge badge-success" title="Username: {{ $c->user->username }}">
                                            <i class="fas fa-check-circle mr-1"></i>Ada
                                        </span>
                                    @else
                                        <span class="badge badge-light text-muted">
                                            <i class="fas fa-times-circle mr-1"></i>Belum
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge {{ $c->is_active ? 'badge-success' : 'badge-secondary' }}">
                                        {{ $c->is_active ? 'Aktif' : 'Non-aktif' }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('customers.edit', $c) }}"
                                        class="btn btn-warning btn-xs">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('customers.destroy', $c) }}" method="POST" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-xs btn-delete"
                                            data-name="{{ $c->name }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="fas fa-users fa-2x d-block mb-2"></i>
                                    Belum ada pelanggan tetap
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($customers->hasPages())
                <div class="card-footer">{{ $customers->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Toggle form akun login
    $('#createAccount').on('change', function () {
        $('#accountFields').toggle(this.checked);
    });

    $(document).on('click', '.btn-delete', function (e) {
        e.preventDefault();
        const form = $(this).closest('form');
        const name = $(this).data('name');
        Swal.fire({
            title: 'Hapus Pelanggan?',
            text: `"${name}" beserta akun loginnya akan dihapus permanen.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then(r => { if (r.isConfirmed) form.submit(); });
    });
</script>
@endpush
