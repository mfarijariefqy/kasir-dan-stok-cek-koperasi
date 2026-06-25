<?php

namespace App\Exports\Sheets;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MonthlyProductSheet implements FromCollection, WithHeadings, WithTitle, WithStyles, WithColumnWidths
{
    public function __construct(private Request $request) {}

    public function collection()
    {
        $user         = auth()->user();
        $month        = $this->request->input('month', now()->format('Y-m'));
        $branchId     = $this->request->input('branch_id');
        $userId       = $this->request->input('user_id');
        $customerName = $this->request->input('customer_name');
        $start        = Carbon::parse($month . '-01')->startOfMonth();
        $end          = Carbon::parse($month . '-01')->endOfMonth();

        $products = DB::table('transaction_items')
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->join('products', 'transaction_items.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->whereBetween('transactions.trx_date', [$start, $end])
            ->when(! $user->isSuperAdmin() && $user->branch_id,
                fn($q) => $q->where('transaction_items.branch_id', $user->branch_id))
            ->when($user->isSuperAdmin() && $branchId,
                fn($q) => $q->where('transaction_items.branch_id', $branchId))
            ->when($userId,       fn($q) => $q->where('transactions.user_id', $userId))
            ->when($customerName, fn($q) => $q->where('transactions.customer_name', 'like', '%' . $customerName . '%'))
            ->selectRaw('products.name as product_name, products.barcode, products.unit,
                categories.name as category_name,
                SUM(transaction_items.qty) as total_qty,
                SUM(transaction_items.buy_price * transaction_items.qty) as total_hpp,
                SUM(transaction_items.subtotal) as total_revenue')
            ->groupBy('products.id', 'products.name', 'products.barcode', 'products.unit', 'categories.name')
            ->orderByDesc('total_revenue')
            ->get();

        $rows = $products->map(function ($p, $i) {
            $profit = $p->total_revenue - $p->total_hpp;
            return [
                $i + 1,
                $p->product_name,
                $p->category_name ?? '-',
                $p->barcode ?? '-',
                $p->total_qty . ($p->unit ? ' ' . $p->unit : ''),
                (float) $p->total_hpp,
                (float) $p->total_revenue,
                (float) $profit,
                $p->total_revenue > 0 ? round($profit / $p->total_revenue * 100, 1) : 0,
            ];
        });

        $totalHPP     = (float) $products->sum('total_hpp');
        $totalRevenue = (float) $products->sum('total_revenue');
        $totalProfit  = $totalRevenue - $totalHPP;

        $rows->push([
            '', 'TOTAL', '', '',
            $products->sum('total_qty'),
            $totalHPP, $totalRevenue, $totalProfit,
            $totalRevenue > 0 ? round($totalProfit / $totalRevenue * 100, 1) : 0,
        ]);

        return $rows;
    }

    public function headings(): array
    {
        return ['#', 'Produk', 'Kategori', 'Barcode', 'Qty Terjual', 'HPP (Rp)', 'Pendapatan (Rp)', 'Keuntungan (Rp)', 'Margin (%)'];
    }

    public function title(): string { return 'Per Produk'; }

    public function columnWidths(): array
    {
        return ['A' => 5, 'B' => 28, 'C' => 16, 'D' => 14, 'E' => 14, 'F' => 16, 'G' => 18, 'H' => 18, 'I' => 12];
    }

    public function styles(Worksheet $sheet)
    {
        $last = $sheet->getHighestRow();

        $sheet->getStyle('A1:I1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2C3E50']],
        ]);
        $sheet->getStyle("A{$last}:I{$last}")->applyFromArray([
            'font' => ['bold' => true],
            'borders' => ['top' => ['borderStyle' => Border::BORDER_MEDIUM]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F2F2F2']],
        ]);
        $sheet->getStyle("F2:I{$last}")->getAlignment()->setHorizontal('right');
        $sheet->getStyle("F2:H{$last}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("I2:I{$last}")->getNumberFormat()->setFormatCode('0.0');
    }
}
