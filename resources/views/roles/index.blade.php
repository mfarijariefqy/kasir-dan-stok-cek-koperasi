@extends('layouts.app')

@section('title', 'Manage Role & Akses')
@section('page-title', 'Manage Role & Akses')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Role & Akses</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-shield-alt mr-1"></i> Daftar Role & Permission</h3>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered mb-0" style="font-size:12.5px;">
                <thead class="thead-dark">
                    <tr>
                        <th style="min-width:130px;">Role</th>
                        @foreach($groups as $group => $perms)
                            <th class="text-center" colspan="{{ count($perms) }}">{{ $group }}</th>
                        @endforeach
                        <th class="text-center" width="80">Aksi</th>
                    </tr>
                    <tr class="table-secondary">
                        <th></th>
                        @foreach($groups as $perms)
                            @foreach($perms as $perm => $label)
                                <th class="text-center" style="font-weight:500;white-space:nowrap;font-size:11px;padding:4px 6px;">
                                    {{ $label }}
                                </th>
                            @endforeach
                        @endforeach
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($roles as $role)
                    <tr>
                        <td>
                            <span class="badge badge-dark px-2 py-1" style="font-size:12px;">
                                {{ $role->name }}
                            </span>
                            <br>
                            <small class="text-muted">{{ $role->permissions->count() }} permission</small>
                        </td>
                        @foreach($groups as $perms)
                            @foreach($perms as $perm => $label)
                                <td class="text-center" style="vertical-align:middle;">
                                    @if($role->hasPermissionTo($perm))
                                        <i class="fas fa-check-circle text-success"></i>
                                    @else
                                        <i class="fas fa-times-circle text-secondary" style="opacity:.3;"></i>
                                    @endif
                                </td>
                            @endforeach
                        @endforeach
                        <td class="text-center" style="vertical-align:middle;">
                            <a href="{{ route('roles.edit', $role) }}" class="btn btn-warning btn-xs">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer text-muted small">
        <i class="fas fa-info-circle mr-1"></i>
        Setiap perubahan akses langsung berlaku pada sesi berikutnya. Role <strong>super-admin</strong> disarankan memiliki semua akses.
    </div>
</div>
@endsection
