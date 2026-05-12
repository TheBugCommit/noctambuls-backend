<?php

declare(strict_types=1);

namespace App\Observation\Infrastructure\Export;

use App\Observation\Application\ExportSessions\ExportSessionsResponse;
use App\Observation\Application\ExportSessions\SessionFileExporterInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

final class PhpSpreadsheetSessionExporter implements SessionFileExporterInterface
{
    public function export(ExportSessionsResponse $response): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->fromArray([$response->headers], null, 'A1');

        $dataRows = array_map(fn(array $row) => array_values($row), $response->rows);
        if (!empty($dataRows)) {
            $sheet->fromArray($dataRows, null, 'A2');
        }

        $path = tempnam(sys_get_temp_dir(), 'noctambuls_xlsx_') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($path);

        return $path;
    }
}
