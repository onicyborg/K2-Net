<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class InvoiceReportExport
{
    protected array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function download(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = ['No.', 'No. Tagihan', 'Pelanggan', 'Paket', 'Periode', 'Jumlah', 'Jatuh Tempo', 'Status', 'Lunas Pada'];
        foreach (range('A', 'I') as $i => $col) {
            $sheet->setCellValue($col . '1', $headers[$i]);
        }

        $sheet->getStyle('A1:I1')->applyFromArray([
            'font'  => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'  => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0091EA']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        $no = 1;
        foreach ($this->data as $row) {
            $r = $no + 1;
            $sheet->setCellValue('A' . $r, $no++);
            $sheet->setCellValue('B' . $r, $row['invoice_number'] ?? '');
            $sheet->setCellValue('C' . $r, $row['customer_name'] ?? '');
            $sheet->setCellValue('D' . $r, $row['package_name'] ?? '');
            $sheet->setCellValue('E' . $r, $row['billing_period'] ?? '');
            $sheet->setCellValue('F' . $r, $row['formatted_amount'] ?? '');
            $sheet->setCellValue('G' . $r, $row['due_date'] ?? '');
            $sheet->setCellValue('H' . $r, $row['status_label'] ?? '');
            $sheet->setCellValue('I' . $r, $row['paid_at'] ?? '');
        }

        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        $response = new \Symfony\Component\HttpFoundation\StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        });

        $label = \Carbon\Carbon::now()->format('Y-m-d_H-i-s');
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment; filename="Laporan_Tagihan_' . $label . '.xlsx"');

        return $response;
    }
}
