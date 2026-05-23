<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $pusat   = Branch::where('name', 'Kantor Pusat')->first();
        $cabang1 = Branch::where('name', 'Cabang 1')->first();

        // Super Admin — no branch constraint
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@koperasi.com'],
            ['name' => 'Super Admin', 'password' => Hash::make('password'), 'branch_id' => null]
        );
        $superAdmin->syncRoles('super-admin');

        // Admin Gudang Pusat
        $adminPusat = User::firstOrCreate(
            ['email' => 'admin.pusat@koperasi.com'],
            ['name' => 'Admin Gudang Pusat', 'password' => Hash::make('password'), 'branch_id' => $pusat?->id]
        );
        $adminPusat->syncRoles('admin-gudang');

        // Kasir Pusat
        $kasirPusat = User::firstOrCreate(
            ['email' => 'kasir.pusat@koperasi.com'],
            ['name' => 'Kasir Pusat', 'password' => Hash::make('password'), 'branch_id' => $pusat?->id]
        );
        $kasirPusat->syncRoles('kasir');

        // Kasir Cabang 1
        $kasirCabang1 = User::firstOrCreate(
            ['email' => 'kasir.cabang1@koperasi.com'],
            ['name' => 'Kasir Cabang 1', 'password' => Hash::make('password'), 'branch_id' => $cabang1?->id]
        );
        $kasirCabang1->syncRoles('kasir');
    }
}
