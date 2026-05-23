<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'view-dashboard',
            'manage-branches',
            'manage-categories',
            'manage-users',
            'manage-products',
            'manage-stock',
            'view-stock',
            'manage-transactions',
            'view-transactions',
            'manage-piutang',
            'view-piutang',
            'view-reports',
            'manage-customers',
            'manage-roles',
            'manage-units',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // Super Admin — full access
        $superAdmin = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions($permissions);

        // Admin Gudang — stok barang & master data saja
        $adminGudang = Role::firstOrCreate(['name' => 'admin-gudang', 'guard_name' => 'web']);
        $adminGudang->syncPermissions([
            'view-dashboard',
            'manage-products',
            'manage-stock',
            'view-stock',
            'manage-customers',
            'manage-units',
        ]);

        // Kasir — transaction input
        $kasir = Role::firstOrCreate(['name' => 'kasir', 'guard_name' => 'web']);
        $kasir->syncPermissions([
            'view-dashboard',
            'manage-transactions',
            'view-transactions',
            'view-stock',
        ]);

        // Pelanggan — portal pribadi: lihat & edit qty transaksi sendiri yg belum lunas
        Role::firstOrCreate(['name' => 'pelanggan', 'guard_name' => 'web']);
    }
}
