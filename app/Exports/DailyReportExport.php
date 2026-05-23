<?php

namespace App\Exports;

use App\Exports\Sheets\DailyProductSheet;
use App\Exports\Sheets\DailyTransactionSheet;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class DailyReportExport implements WithMultipleSheets
{
    public function __construct(private Request $request) {}

    public function sheets(): array
    {
        return [
            new DailyTransactionSheet($this->request),
            new DailyProductSheet($this->request),
        ];
    }
}
