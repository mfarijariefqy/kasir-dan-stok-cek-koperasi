<?php

namespace App\Exports\Sheets;

use App\Models\Transaction;
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

class MonthlyDailySheet implements FromCollection, WithHeadings, WithTitle, WithStyles, WithColumnWidths
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

        $query = Transaction::selectRaw(
            'DATE(trx_date) as date, COUNT(*) as count, SUM(total) as total,
             SUM(CASE WHEN payment_status = "Lunas" THEN total ELSE 0 END) as lunas,
             SUM(CASE WHEN payment_status = "Belum Lunas" THEN total ELSE 0 END) as tempo'
        )->whereBetween('trx_date', [$start, $end])->groupBy('date')->orderBy('date');

        if (! $user->isSuperAdmin() && $user->branch_id) {
            $query->where('branch_id', $user->branch_id);
        } elseif ($user->isSuperAdmin() && $branchId) {
            $query->where('branch_id', $branchId);
        }
        if ($userId)       $query->where('user_id', $userId);
        if ($customerName) $query->where('customer_name', 'like', '%' . $customerName . '%');

        $summary = $query->get();

        $hppByDate = DB::table('transaction_items')
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->when(! $user->isSuperAdmin() && $user->branch_id,
                fn($q) => $q->where('transactions.branch_id', $user->branch_id))
            ->when($user->isSuperAdmin() && $branchId,
                fn($q) => $q->where('transactions.branch_id', $branchId))
            ->when($userId,       fn($q) => $q->where('transactions.user_id', $userId))
            ->when($customerName, fn($q) => $q->where('transactions.customer_name', 'like', '%' . $customerName . '%'))
            ->whereBetween('transactions.trx_date', [$start, $end])
            ->selectRaw('DATE(transactions.trx_date) as date, SUM(transaction_items.buy_price * transaction_items.qty) as hpp')
            ->groupBy('date')
            ->pluck('hpp', 'date');

        $rows = $summary->map(function ($row) use ($hppByDate) {
            $hpp    = (float) ($hppByDate[$row->date] ?? 0);
            $profit = $row->total - $hpp;
            return [
                Carbon::parse($row->date)->format('d/m/Y'),
                (int) $row->count,
                (float) $row->total,
                (float) ($row->lunas ?? 0),
                (float) ($row->tempo ?? 0),
                $hpp,
                $profit,
                $row->total > 0 ? round($profit / $row->total * 100, 1) : 0,
            ];
        });

        $totalSales  = (float) $summary->sum('total');
        $totalHPP    = (float) $hppByDate->sum();
        $totalProfit = $totalSales - $totalHPP;

        $rows->push([
            'TOTAL',
            $summary->sum('count'),
            $totalSales,
            (float) $summary->sum('lunas'),
            (float) $summary->sum('tempo'),
            $totalHPP,
            $totalProfit,
            $totalSales > 0 ? round($totalProfit / $totalSales * 100, 1) : 0,
        ]);

        return $rows;
    }

    public function headings(): array
    {
        return [
            'Tanggal', 'Jml Transaksi',
            'Total Penjualan (Rp)', 'Lunas (Rp)', 'Belum Lunas (Rp)',
            'HPP (Rp)', 'Keuntungan (Rp)', 'Margin (%)',
        ];
    }

    public function title(): string { return 'Per Hari'; }

    public function columnWidths(): array
    {
        return ['A' => 14, 'B' => 16, 'C' => 22, 'D' => 16, 'E' => 18, 'F' => 16, 'G' => 18, 'H' => 12];
    }

    public function styles(Worksheet $sheet)
    {
        $last = $sheet->getHighestRow();

        $sheet->getStyle('A1:H1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2C3E50']],
        ]);
        $sheet->getStyle("A{$last}:H{$last}")->applyFromArray([
            'font' => ['bold' => true],
            'borders' => ['top' => ['borderStyle' => Border::BORDER_MEDIUM]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F2F2F2']],
        ]);
        $sheet->getStyle("B2:H{$last}")->getAlignment()->setHorizontal('right');
        $sheet->getStyle("C2:G{$last}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("H2:H{$last}")->getNumberFormat()->setFormatCode('0.0');
    }
}
