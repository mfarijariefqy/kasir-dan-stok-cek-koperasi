<?php

namespace App\Imports;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductsImport implements ToCollection, WithHeadingRow
{
    private User $user;
    private int $imported = 0;
    private array $errors = [];

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $rowNum = $index + 2;

            $namaProduct = trim($row['nama_produk'] ?? '');
            if ($namaProduct === '') {
                continue; // skip blank rows
            }

            // Resolve branch
            if ($this->user->isSuperAdmin()) {
                $branchName = trim($row['cabang'] ?? '');
                if ($branchName === '') {
                    $this->errors[] = "Baris {$rowNum}: Kolom 'cabang' wajib diisi untuk super admin.";
                    continue;
                }
                $branch = Branch::where('name', $branchName)->first();
                if (! $branch) {
                    $this->errors[] = "Baris {$rowNum}: Cabang '{$branchName}' tidak ditemukan.";
                    continue;
                }
                $branchId = $branch->id;
            } else {
                $branchId = $this->user->branch_id;
            }

            // Resolve category
            $categoryId = null;
            $kategori   = trim($row['kategori'] ?? '');
            if ($kategori !== '') {
                $category = Category::where('name', $kategori)->first();
                if (! $category) {
                    $this->errors[] = "Baris {$rowNum}: Kategori '{$kategori}' tidak ditemukan, dilewati.";
                    continue;
                }
                $categoryId = $category->id;
            }

            // Resolve unit — auto-create if not yet in master
            $unitName = strtolower(trim($row['satuan'] ?? 'pcs'));
            if ($unitName === '') {
                $unitName = 'pcs';
            }
            Unit::firstOrCreate(['name' => $unitName], ['is_active' => true]);

            // Barcode duplicate check (per branch)
            $barcode = ! empty($row['barcode']) ? trim((string) $row['barcode']) : null;
            if ($barcode !== null) {
                $exists = Product::where('barcode', $barcode)
                    ->where('branch_id', $branchId)
                    ->where('unit', $unitName)
                    ->exists();
                if ($exists) {
                    $this->errors[] = "Baris {$rowNum}: Barcode '{$barcode}' dengan satuan '{$unitName}' sudah ada di cabang tersebut, dilewati.";
                    continue;
                }
            }

            Product::create([
                'barcode'     => $barcode,
                'name'        => $namaProduct,
                'category_id' => $categoryId,
                'unit'        => $unitName,
                'buy_price'   => is_numeric($row['harga_beli'] ?? null) ? (float) $row['harga_beli'] : 0,
                'sell_price'  => is_numeric($row['harga_jual'] ?? null) ? (float) $row['harga_jual'] : 0,
                'stock_qty'   => is_numeric($row['stok_awal'] ?? null) ? (int) $row['stok_awal'] : 0,
                'branch_id'   => $branchId,
                'is_active'   => strtolower(trim($row['status'] ?? 'aktif')) !== 'nonaktif',
            ]);

            $this->imported++;
        }
    }

    public function getImportedCount(): int
    {
        return $this->imported;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
