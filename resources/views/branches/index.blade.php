@extends('layouts.app')

@section('title', 'Manajemen Cabang')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Daftar Cabang</h3>
                <div class="card-tools">
                    <a href="{{ route('branches.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus mr-1"></i> Tambah Cabang
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th width="50">#</th>
                            <th>Nama Cabang</th>
                            <th>Alamat</th>
                            <th>Telepon</th>
                            <th width="80">User</th>
                            <th width="80">Status</th>
                            <th width="120">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($branches as $branch)
                            <tr>
                                <td>{{ $branches->firstItem() + $loop->index }}</td>
                                <td><strong>{{ $branch->name }}</strong></td>
                                <td>{{ $branch->address ?? '-' }}</td>
                                <td>{{ $branch->phone ?? '-' }}</td>
                                <td>
                                    <span class="badge badge-info">{{ $branch->users_count }}</span>
                                </td>
                                <td>
                                    @if($branch->is_active)
                                        <span class="badge badge-success">Aktif</span>
                                    @else
                                        <span class="badge badge-secondary">Nonaktif</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('branches.edit', $branch) }}"
                                        class="btn btn-warning btn-xs">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('branches.destroy', $branch) }}" method="POST"
                                        class="d-inline delete-form">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-xs btn-delete"
                                            data-name="{{ $branch->name }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">Belum ada cabang</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($branches->hasPages())
                <div class="card-footer">
                    {{ $branches->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).on('click', '.btn-delete', function (e) {
        e.preventDefault();
        const form = $(this).closest('form');
        const name = $(this).data('name');
        Swal.fire({
            title: 'Hapus Cabang?',
            text: `"${name}" akan dihapus permanen.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) form.submit();
        });
    });
</script>
@endpush
