<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    private function permissionGroups(): array
    {
        return [
            'Dashboard' => [
                'view-dashboard'      => 'Lihat Dashboard',
            ],
            'Transaksi' => [
                'manage-transactions' => 'Input Transaksi (Kasir)',
                'view-transactions'   => 'Lihat Riwayat Transaksi',
                'manage-piutang'      => 'Tandai Lunas Piutang',
                'view-piutang'        => 'Lihat Daftar Piutang',
            ],
            'Stok Barang' => [
                'manage-stock'        => 'Input Stok Masuk',
                'view-stock'          => 'Lihat Stok Barang',
            ],
            'Master Data' => [
                'manage-products'     => 'Kelola Produk',
                'manage-categories'   => 'Kelola Kategori',
                'manage-units'        => 'Kelola Satuan',
                'manage-customers'    => 'Kelola Pelanggan Tetap',
            ],
            'Laporan' => [
                'view-reports'        => 'Lihat Laporan',
            ],
            'Pengaturan' => [
                'manage-branches'     => 'Kelola Cabang',
            ],
            'User & Akses' => [
                'manage-users'        => 'Kelola Manajemen User',
                'manage-roles'        => 'Kelola Role & Akses',
            ],
        ];
    }

    public function index()
    {
        $roles  = Role::with('permissions')->orderBy('name')->get();
        $groups = $this->permissionGroups();

        return view('roles.index', compact('roles', 'groups'));
    }

    public function edit(Role $role)
    {
        $groups          = $this->permissionGroups();
        $rolePermissions = $role->permissions->pluck('name')->toArray();

        return view('roles.edit', compact('role', 'groups', 'rolePermissions'));
    }

    public function update(Request $request, Role $role)
    {
        $allPerms = collect($this->permissionGroups())
            ->flatMap(fn($perms) => array_keys($perms))
            ->toArray();

        $selected = array_intersect($request->input('permissions', []), $allPerms);

        // Ensure all selected permissions exist in DB
        foreach ($selected as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        $role->syncPermissions($selected);

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->route('roles.index')
            ->with('success', "Akses role <strong>{$role->name}</strong> berhasil diperbarui.");
    }
}
