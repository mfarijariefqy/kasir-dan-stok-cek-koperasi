@extends('layouts.app')

@section('title', 'Daftar User')

@section('page-title', 'Daftar User')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Users</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-users mr-2 text-muted"></i>Daftar User</h3>
            <div class="card-tools">
                <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-user-plus mr-1"></i> Tambah User
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th style="width:5%">No</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Cabang</th>
                        <th class="text-center" style="width:15%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td class="text-muted">{{ $loop->iteration + ($users->currentPage() - 1) * $users->perPage() }}</td>
                            <td>
                                <div class="d-flex align-items-center" style="gap:10px;">
                                    <div style="width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,#6F4E37,#A1887F);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:0.85rem;flex-shrink:0;">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <span><strong>{{ $user->name }}</strong></span>
                                </div>
                            </td>
                            <td class="text-muted">{{ $user->email }}</td>
                            <td>
                                @foreach($user->roles as $role)
                                    @php
                                        $roleColors = ['super-admin' => 'danger', 'admin-gudang' => 'warning', 'kasir' => 'success'];
                                        $rc = $roleColors[$role->name] ?? 'secondary';
                                    @endphp
                                    <span class="badge badge-{{ $rc }}">{{ ucfirst($role->name) }}</span>
                                @endforeach
                            </td>
                            <td>{{ $user->branch->name ?? '<span class="text-muted">Semua</span>' }}</td>
                            <td class="text-center">
                                <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-warning" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @if($user->id !== auth()->id())
                                    <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-sm btn-danger btn-delete"
                                            data-name="{{ $user->name }}" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @else
                                    <button class="btn btn-sm btn-secondary" disabled title="Tidak bisa hapus akun sendiri">
                                        <i class="fas fa-lock"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <i class="fas fa-users fa-3x d-block mb-3" style="opacity:0.2"></i>
                                <span class="text-muted">Belum ada data user</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
        <div class="card-footer">
            {{ $users->links() }}
        </div>
        @endif
    </div>
@endsection

@push('scripts')
<script>
    $(document).on('click', '.btn-delete', function () {
        const form = $(this).closest('form');
        const name = $(this).data('name');
        Swal.fire({
            title: 'Hapus User?',
            html: 'User <strong>"' + name + '"</strong> akan dihapus permanen.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#C62828',
            cancelButtonColor: '#546E7A',
            confirmButtonText: '<i class="fas fa-trash mr-1"></i> Ya, Hapus',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
</script>
@endpush
