<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        $branches = [
            ['name' => 'Kantor Pusat', 'address' => 'Jl. Utama No. 1', 'phone' => '021-0000001', 'is_active' => true],
            ['name' => 'Cabang 1',     'address' => 'Jl. Cabang No. 1', 'phone' => '021-0000002', 'is_active' => true],
        ];

        foreach ($branches as $branch) {
            Branch::firstOrCreate(['name' => $branch['name']], $branch);
        }
    }
}
