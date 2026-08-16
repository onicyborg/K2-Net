<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

abstract class BaseReportExport implements FromCollection, WithHeadings, WithStyles
{
    protected array $reportData = [];

    public function collection(): Collection
    {
        return collect($this->reportData);
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0091EA']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ],
            'A:Z' => [
                'font'      => ['size' => 11],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ],
        ];
    }

    protected function styleRow(Worksheet $sheet, int $rowCount): void
    {
        $sheet->setAutoSize(['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H']);
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getStyle('A2:A' . ($rowCount + 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }
}
