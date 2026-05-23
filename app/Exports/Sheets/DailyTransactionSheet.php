<?php

namespace App\Exports\Sheets;

use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DailyTransactionSheet implements FromCollection, WithHeadings, WithTitle, WithStyles, WithColumnWidths
{
    public function __construct(private Request $request) {}

    public function collection()
    {
        $user         = auth()->user();
        $date         = $this->request->input('date', today()->toDateString());
        $branchId     = $this->request->input('branch_id');
        $userId       = $this->request->input('user_id');
        $customerName = $this->request->input('customer_name');

        $query = Transaction::with(['items', 'user', 'branch'])->whereDate('trx_date', $date);
        if (! $user->isSuperAdmin() && $user->branch_id) {
            $query->where('branch_id', $user->branch_id);
        } elseif ($user->isSuperAdmin() && $branchId) {
            $query->where('branch_id', $branchId);
        }
        if ($userId)       $query->where('user_id', $userId);
        if ($customerName) $query->where('customer_name', 'like', '%' . $customerName . '%');

        $transactions = $query->get();

        $rows = $transactions->map(function ($trx) {
            $hpp    = $trx->items->sum(fn($i) => $i->buy_price * $i->qty);
            $profit = $trx->total - $hpp;
            return [
                $trx->trx_no,
                $trx->trx_date->format('d/m/Y'),
                $trx->customer_name ?? '-',
                $trx->user->name ?? '-',
                $trx->branch->name ?? '-',
                $trx->payment_method,
                $trx->payment_status,
                (float) $trx->total,
                (float) $hpp,
                (float) $profit,
                $trx->total > 0 ? round($profit / $trx->total * 100, 1) : 0,
            ];
        });

        $totalSales  = (float) $transactions->sum('total');
        $totalHPP    = (float) $transactions->sum(fn($t) => $t->items->sum(fn($i) => $i->buy_price * $i->qty));
        $totalProfit = $totalSales - $totalHPP;

        $rows->push([
            'TOTAL',
            Carbon::parse($date)->format('d/m/Y'),
            '', '', '', '',
            $transactions->count() . ' transaksi',
            $totalSales,
            $totalHPP,
            $totalProfit,
            $totalSales > 0 ? round($totalProfit / $totalSales * 100, 1) : 0,
        ]);

        return $rows;
    }

    public function headings(): array
    {
        return [
            'No. Transaksi', 'Tanggal', 'Pelanggan', 'Kasir', 'Cabang',
            'Cara Bayar', 'Status',
            'Total (Rp)', 'HPP (Rp)', 'Keuntungan (Rp)', 'Margin (%)',
        ];
    }

    public function title(): string { return 'Per Transaksi'; }

    public function columnWidths(): array
    {
        return [
            'A' => 18, 'B' => 12, 'C' => 20, 'D' => 16, 'E' => 16,
            'F' => 12, 'G' => 14, 'H' => 16, 'I' => 16, 'J' => 16, 'K' => 12,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $last = $sheet->getHighestRow();

        $sheet->getStyle('A1:K1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2C3E50']],
        ]);
        $sheet->getStyle("A{$last}:K{$last}")->applyFromArray([
            'font' => ['bold' => true],
            'borders' => ['top' => ['borderStyle' => Border::BORDER_MEDIUM]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F2F2F2']],
        ]);
        $sheet->getStyle("H2:K{$last}")->getAlignment()->setHorizontal('right');
        $sheet->getStyle("H2:J{$last}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("K2:K{$last}")->getNumberFormat()->setFormatCode('0.0');
    }
}
