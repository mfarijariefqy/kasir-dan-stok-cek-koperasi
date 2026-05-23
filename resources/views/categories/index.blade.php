@extends('layouts.app')

@section('title', 'Manajemen Kategori')

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Tambah Kategori</h3>
            </div>
            <form action="{{ route('categories.store') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="form-group mb-0">
                        <label>Nama Kategori <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name') }}" placeholder="Contoh: Sembako" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-plus mr-1"></i> Tambah Kategori
                    </button>
                </div>
            </form>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Daftar Kategori</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th width="50">#</th>
                            <th>Nama Kategori</th>
                            <th width="80">Produk</th>
                            <th width="120">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $category)
                            <tr>
                                <td>{{ $categories->firstItem() + $loop->index }}</td>
                                <td>{{ $category->name }}</td>
                                <td><span class="badge badge-info">{{ $category->products_count }}</span></td>
                                <td>
                                    <button class="btn btn-warning btn-xs btn-edit"
                                        data-id="{{ $category->id }}"
                                        data-name="{{ $category->name }}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="{{ route('categories.destroy', $category) }}" method="POST"
                                        class="d-inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-xs btn-delete"
                                            data-name="{{ $category->name }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">Belum ada kategori</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($categories->hasPages())
                <div class="card-footer">{{ $categories->links() }}</div>
            @endif
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Kategori</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="editForm" method="POST">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="form-group mb-0">
                        <label>Nama Kategori</label>
                        <input type="text" name="name" id="editName" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).on('click', '.btn-edit', function () {
        const id   = $(this).data('id');
        const name = $(this).data('name');
        $('#editName').val(name);
        $('#editForm').attr('action', `/categories/${id}`);
        $('#editModal').modal('show');
    });

    $(document).on('click', '.btn-delete', function (e) {
        e.preventDefault();
        const form = $(this).closest('form');
        const name = $(this).data('name');
        Swal.fire({
            title: 'Hapus Kategori?',
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
