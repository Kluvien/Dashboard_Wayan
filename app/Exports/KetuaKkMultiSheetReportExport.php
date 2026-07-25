<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class KetuaKkMultiSheetReportExport implements WithMultipleSheets
{
    public function __construct(private readonly array $sheets)
    {
    }

    public function sheets(): array
    {
        return array_map(
            fn (array $sheet) => new KetuaKkReportArraySheet(
                $sheet['title'],
                $sheet['headings'],
                $sheet['rows']
            ),
            $this->sheets
        );
    }
}

class KetuaKkReportArraySheet implements FromArray, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    public function __construct(
        private readonly string $title,
        private readonly array $headings,
        private readonly array $rows
    ) {
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function styles(Worksheet $sheet): array
    {
        $lastColumn = Coordinate::stringFromColumnIndex(max(count($this->headings), 1));
        $lastRow = max(count($this->rows) + 1, 1);

        $sheet->freezePane('A2');
        $sheet->getStyle('A1:' . $lastColumn . '1')->getFont()->setBold(true);
        $sheet->getStyle('A1:' . $lastColumn . '1')->getFont()->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('A1:' . $lastColumn . '1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('477EF7');
        $sheet->getStyle('A1:' . $lastColumn . $lastRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A1:' . $lastColumn . '1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        return [];
    }
}