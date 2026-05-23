<?php

namespace App\Exports;

use App\Exports\Sheets\MonthlyDailySheet;
use App\Exports\Sheets\MonthlyProductSheet;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MonthlyReportExport implements WithMultipleSheets
{
    public function __construct(private Request $request) {}

    public function sheets(): array
    {
        return [
            new MonthlyDailySheet($this->request),
            new MonthlyProductSheet($this->request),
        ];
    }
}
