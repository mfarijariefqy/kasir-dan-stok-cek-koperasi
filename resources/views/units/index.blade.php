@extends('layouts.app')

@section('title', 'Master Satuan')
@section('page-title', 'Master Satuan')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Master Satuan</li>
@endsection

@section('content')
<div class="row">
    {{-- Form Tambah --}}
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-plus-circle mr-1"></i> Tambah Satuan</h3>
            </div>
            <form action="{{ route('units.store') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="form-group mb-0">
                        <label>Nama Satuan <span class="text-danger">*</span></label>
                        <input type="text" name="name"
                            class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name') }}"
                            placeholder="cth: pcs, kg, liter..."
                            autocomplete="off">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Akan disimpan dalam huruf kecil (lowercase)</small>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-plus mr-1"></i> Tambah Satuan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabel --}}
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-ruler mr-1"></i> Daftar Satuan</h3>
                <div class="card-tools">
                    <form method="GET" class="d-flex">
                        <input type="text" name="q" class="form-control form-control-sm mr-1"
                            placeholder="Cari satuan..." value="{{ request('q') }}" style="width:160px;">
                        <button type="submit" class="btn btn-info btn-sm mr-1">
                            <i class="fas fa-search"></i>
                        </button>
                        @if(request('q'))
                            <a href="{{ route('units.index') }}" class="btn btn-secondary btn-sm">
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
                            <th>Nama Satuan</th>
                            <th width="100" class="text-center">Status</th>
                            <th width="100">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($units as $unit)
                            <tr>
                                <td>{{ $units->firstItem() + $loop->index }}</td>
                                <td><strong>{{ $unit->name }}</strong></td>
                                <td class="text-center">
                                    <span class="badge {{ $unit->is_active ? 'badge-success' : 'badge-secondary' }}">
                                        {{ $unit->is_active ? 'Aktif' : 'Non-aktif' }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('units.edit', $unit) }}"
                                        class="btn btn-warning btn-xs">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('units.destroy', $unit) }}" method="POST" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-xs btn-delete"
                                            data-name="{{ $unit->name }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    <i class="fas fa-ruler fa-2x d-block mb-2"></i>
                                    Belum ada satuan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($units->hasPages())
                <div class="card-footer">{{ $units->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).on('click', '.btn-delete', function(e) {
        e.preventDefault();
        const form = $(this).closest('form');
        const name = $(this).data('name');
        Swal.fire({
            title: 'Hapus Satuan?',
            text: `Satuan "${name}" akan dihapus permanen.`,
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
