<?php

namespace App\Exports;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Unit;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class ProductImportTemplateExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new ProductImportDataSheet(),
            new ProductImportReferenceSheet(),
        ];
    }
}

class ProductImportDataSheet implements FromArray, WithTitle, WithColumnWidths, WithEvents
{
    public function title(): string
    {
        return 'Template Import';
    }

    public function array(): array
    {
        return [
            ['barcode', 'nama_produk', 'kategori', 'satuan', 'harga_beli', 'harga_jual', 'stok_awal', 'status', 'cabang'],
            ['8991101010101', 'Aqua Botol 600ml', 'Minuman', 'pcs', 2500, 4000, 0, 'aktif', 'Nama Cabang'],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 22, 'B' => 32, 'C' => 22,
            'D' => 14, 'E' => 16, 'F' => 16,
            'G' => 14, 'H' => 18, 'I' => 26,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (\Maatwebsite\Excel\Events\AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Header row — dark blue background, white bold text
                $sheet->getStyle('A1:I1')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF1F497D']],
                ]);

                // Example row — yellow background
                $sheet->getStyle('A2:I2')->applyFromArray([
                    'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FFFFF2CC']],
                ]);

                // Freeze header row
                $sheet->freezePane('A2');

                // Add a note on the example row (row 2)
                $sheet->getComment('A2')->getText()->createTextRun('Ini baris contoh. Hapus sebelum upload.');
            },
        ];
    }
}

class ProductImportReferenceSheet implements FromArray, WithTitle, WithColumnWidths, WithEvents
{
    public function title(): string
    {
        return 'Referensi';
    }

    public function array(): array
    {
        $categories = Category::orderBy('name')->pluck('name')->toArray();
        $units      = Unit::active()->orderBy('name')->pluck('name')->toArray();
        $branches   = Branch::where('is_active', true)->orderBy('name')->pluck('name')->toArray();

        $maxRows = max(count($categories), count($units), count($branches), 1);

        $rows = [['KATEGORI', 'SATUAN', 'CABANG']];
        for ($i = 0; $i < $maxRows; $i++) {
            $rows[] = [
                $categories[$i] ?? '',
                $units[$i]      ?? '',
                $branches[$i]   ?? '',
            ];
        }

        return $rows;
    }

    public function columnWidths(): array
    {
        return ['A' => 28, 'B' => 20, 'C' => 28];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (\Maatwebsite\Excel\Events\AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $sheet->getStyle('A1:C1')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF375623']],
                ]);

                $sheet->freezePane('A2');
            },
        ];
    }
}
