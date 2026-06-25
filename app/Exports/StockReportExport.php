<?php

namespace App\Exports;

use App\Models\Product;
use App\Models\ProductLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StockReportExport implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    public function __construct(private Request $request) {}

    public function collection()
    {
        $dateFrom = $this->request->input('date_from', now()->startOfMonth()->toDateString());
        $dateTo   = $this->request->input('date_to', today()->toDateString());

        $query = Product::with('category', 'branch');

        $user = auth()->user();
        if (! $user->isSuperAdmin() && $user->branch_id) {
            $query->where('branch_id', $user->branch_id);
        }

        return $query->orderBy('name')->get()->map(function ($p) use ($dateFrom, $dateTo) {
            $in  = ProductLog::where('product_id', $p->id)->where('type', 'IN')
                ->whereBetween('logged_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])->sum('qty');
            $out = ProductLog::where('product_id', $p->id)->where('type', 'OUT')
                ->whereBetween('logged_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])->sum('qty');
            $stokAkhir = $p->stockAt($dateTo);

            return [
                'Nama Produk'  => $p->name,
                'Barcode'      => $p->barcode ?? '-',
                'Kategori'     => $p->category->name ?? '-',
                'Cabang'       => $p->branch->name ?? '-',
                'Satuan'       => $p->unit,
                'Stok Awal'    => $stokAkhir - $in + $out,
                'Stok Masuk'   => $in,
                'Stok Keluar'  => $out,
                'Stok Akhir'   => $stokAkhir,
            ];
        });
    }

    public function headings(): array
    {
        return ['Nama Produk', 'Barcode', 'Kategori', 'Cabang', 'Satuan', 'Stok Awal', 'Stok Masuk', 'Stok Keluar', 'Stok Akhir'];
    }

    public function title(): string { return 'Laporan Stok'; }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
