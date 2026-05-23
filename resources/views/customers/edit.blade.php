@extends('layouts.app')

@section('title', 'Edit Pelanggan')
@section('page-title', 'Edit Pelanggan')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('customers.index') }}">Pelanggan Tetap</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-7">
        <form action="{{ route('customers.update', $customer) }}" method="POST" id="editForm">
            @csrf @method('PUT')

            {{-- Data Pelanggan --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-user-edit mr-1"></i> Data Pelanggan</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Nama <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name', $customer->name) }}" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <label>No. HP / Telepon</label>
                        <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                            value="{{ old('phone', $customer->phone) }}" placeholder="Contoh: 0812xxxx">
                        @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <label>Alamat</label>
                        <textarea name="address" class="form-control @error('address') is-invalid @enderror"
                            rows="3">{{ old('address', $customer->address) }}</textarea>
                        @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <label>Catatan</label>
                        <textarea name="notes" class="form-control @error('notes') is-invalid @enderror"
                            rows="2">{{ old('notes', $customer->notes) }}</textarea>
                        @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group mb-0">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="isActive"
                                name="is_active" value="1"
                                {{ old('is_active', $customer->is_active) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="isActive">Pelanggan Aktif</label>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Akun Login --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-key mr-1 text-warning"></i> Akun Login Portal</h3>
                </div>
                <div class="card-body">

                    @if($customer->hasLoginAccount())
                        {{-- Sudah punya akun — tampil opsi update/hapus --}}
                        <div class="alert alert-success py-2 mb-3">
                            <i class="fas fa-check-circle mr-1"></i>
                            Akun aktif — username: <strong>{{ $customer->user->username }}</strong>
                            @if($customer->user->username)
                                &nbsp;|&nbsp; Login di:
                                <a href="{{ route('login') }}" target="_blank" class="alert-link">Halaman Login</a>
                            @endif
                        </div>

                        <div class="mb-2">
                            <div class="custom-control custom-radio custom-control-inline">
                                <input type="radio" id="actionNone" name="account_action" value="none"
                                    class="custom-control-input" checked>
                                <label class="custom-control-label" for="actionNone">Tidak ubah akun</label>
                            </div>
                            <div class="custom-control custom-radio custom-control-inline">
                                <input type="radio" id="actionUpdate" name="account_action" value="update"
                                    class="custom-control-input" {{ old('account_action') === 'update' ? 'checked' : '' }}>
                                <label class="custom-control-label" for="actionUpdate">Ubah username/password</label>
                            </div>
                            <div class="custom-control custom-radio custom-control-inline">
                                <input type="radio" id="actionRemove" name="account_action" value="remove"
                                    class="custom-control-input" {{ old('account_action') === 'remove' ? 'checked' : '' }}>
                                <label class="custom-control-label text-danger" for="actionRemove">Hapus akun</label>
                            </div>
                        </div>

                        <div id="updateFields" style="{{ old('account_action') === 'update' ? '' : 'display:none;' }}">
                            <div class="form-group mt-3">
                                <label>Username <span class="text-danger">*</span></label>
                                <input type="text" name="username" class="form-control @error('username') is-invalid @enderror"
                                    value="{{ old('username', $customer->user->username) }}">
                                @error('username') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="form-group">
                                <label>Password Baru</label>
                                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                                    placeholder="Kosongkan jika tidak ingin mengubah">
                                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="form-group mb-0">
                                <label>Konfirmasi Password Baru</label>
                                <input type="password" name="password_confirmation" class="form-control"
                                    placeholder="Ulangi password baru">
                            </div>
                        </div>

                        <div id="removeWarning" style="{{ old('account_action') === 'remove' ? '' : 'display:none;' }}">
                            <div class="alert alert-warning mt-3 mb-0 py-2">
                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                Akun login pelanggan ini akan <strong>dihapus permanen</strong>. Pelanggan tidak bisa login lagi.
                            </div>
                        </div>

                    @else
                        {{-- Belum punya akun — tampil opsi buat akun --}}
                        <input type="hidden" name="account_action" id="hiddenAction" value="none">

                        <div class="custom-control custom-switch mb-2">
                            <input type="checkbox" class="custom-control-input" id="createAccountEdit"
                                {{ old('account_action') === 'create' ? 'checked' : '' }}>
                            <label class="custom-control-label" for="createAccountEdit">
                                Buat akun login untuk pelanggan ini
                            </label>
                        </div>

                        <div id="createFields" style="{{ old('account_action') === 'create' ? '' : 'display:none;' }}">
                            <div class="form-group">
                                <label>Username <span class="text-danger">*</span></label>
                                <input type="text" name="username" class="form-control @error('username') is-invalid @enderror"
                                    value="{{ old('username') }}" placeholder="min. 4 karakter, huruf & angka">
                                @error('username') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="form-group">
                                <label>Password <span class="text-danger">*</span></label>
                                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                                    placeholder="min. 6 karakter">
                                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="form-group mb-0">
                                <label>Konfirmasi Password <span class="text-danger">*</span></label>
                                <input type="password" name="password_confirmation" class="form-control">
                            </div>
                        </div>
                    @endif

                </div>
            </div>

            <div class="card-footer bg-transparent border-0 px-0">
                <button type="submit" class="btn btn-primary mr-2">
                    <i class="fas fa-save mr-1"></i> Simpan Perubahan
                </button>
                <a href="{{ route('customers.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali
                </a>
            </div>

        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Toggle create fields (for customers without account)
    $('#createAccountEdit').on('change', function () {
        const checked = this.checked;
        $('#createFields').toggle(checked);
        $('#hiddenAction').val(checked ? 'create' : 'none');
    });

    // Toggle update/remove fields (for customers with account)
    $('input[name="account_action"]').on('change', function () {
        const val = $(this).val();
        $('#updateFields').toggle(val === 'update');
        $('#removeWarning').toggle(val === 'remove');
    });
</script>
@endpush
