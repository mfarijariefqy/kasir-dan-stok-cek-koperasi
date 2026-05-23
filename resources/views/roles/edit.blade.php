@extends('layouts.app')

@section('title', 'Edit Akses: ' . $role->name)
@section('page-title', 'Edit Akses Role')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('roles.index') }}">Role & Akses</a></li>
    <li class="breadcrumb-item active">{{ $role->name }}</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-9">

        @if($role->name === 'super-admin')
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle mr-1"></i>
            Anda sedang mengedit role <strong>super-admin</strong>. Pastikan role ini tetap memiliki akses
            <strong>manage-roles</strong> agar tidak terkunci dari halaman ini.
        </div>
        @endif

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-shield-alt mr-1"></i>
                    Akses untuk Role: <span class="badge badge-dark ml-1">{{ $role->name }}</span>
                </h3>
            </div>

            <form action="{{ route('roles.update', $role) }}" method="POST">
                @csrf @method('PUT')

                <div class="card-body">

                    {{-- Select All / Deselect All --}}
                    <div class="mb-3 d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-sm btn-outline-primary mr-2" id="btnSelectAll">
                            <i class="fas fa-check-square mr-1"></i> Pilih Semua
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="btnDeselectAll">
                            <i class="fas fa-square mr-1"></i> Hapus Semua
                        </button>
                        <span class="ml-3 text-muted small" id="selectedCount">
                            {{ count($rolePermissions) }} permission dipilih
                        </span>
                    </div>

                    <hr class="mt-1 mb-3">

                    @foreach($groups as $groupName => $perms)
                    <div class="mb-4">
                        <div class="d-flex align-items-center mb-2">
                            <h6 class="font-weight-bold text-uppercase mb-0 mr-2"
                                style="font-size:11px;letter-spacing:.8px;color:#1a3a5c;">
                                {{ $groupName }}
                            </h6>
                            <div style="flex:1;height:1px;background:#dee2e6;"></div>
                        </div>
                        <div class="row">
                            @foreach($perms as $perm => $label)
                            <div class="col-md-4 mb-2">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox"
                                        class="custom-control-input perm-check"
                                        id="perm_{{ str_replace('-', '_', $perm) }}"
                                        name="permissions[]"
                                        value="{{ $perm }}"
                                        {{ in_array($perm, $rolePermissions) ? 'checked' : '' }}>
                                    <label class="custom-control-label"
                                        for="perm_{{ str_replace('-', '_', $perm) }}">
                                        {{ $label }}
                                        <br>
                                        <code style="font-size:10px;color:#aaa;">{{ $perm }}</code>
                                    </label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach

                </div>

                <div class="card-footer d-flex justify-content-between">
                    <a href="{{ route('roles.index') }}" class="btn btn-secondary">
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

@push('scripts')
<script>
    const checks = document.querySelectorAll('.perm-check');
    const counter = document.getElementById('selectedCount');

    function updateCount() {
        const n = document.querySelectorAll('.perm-check:checked').length;
        counter.textContent = n + ' permission dipilih';
    }

    checks.forEach(c => c.addEventListener('change', updateCount));

    document.getElementById('btnSelectAll').addEventListener('click', () => {
        checks.forEach(c => c.checked = true);
        updateCount();
    });
    document.getElementById('btnDeselectAll').addEventListener('click', () => {
        checks.forEach(c => c.checked = false);
        updateCount();
    });
</script>
@endpush
