<?php

declare(strict_types=1);

namespace App\Observation\Infrastructure\Export;

use App\Observation\Application\ExportSessions\ExportSessionsResponse;
use App\Observation\Application\ExportSessions\SessionFileExporterInterface;

final class CsvSessionExporter implements SessionFileExporterInterface
{
    public function export(ExportSessionsResponse $response): string
    {
        $path = tempnam(sys_get_temp_dir(), 'noctambuls_csv_');
        $handle = fopen($path, 'w');

        fwrite($handle, "\xEF\xBB\xBF");
        fputcsv($handle, $response->headers, ';');

        foreach ($response->rows as $row) {
            fputcsv($handle, array_values($row), ';');
        }

        fclose($handle);

        return $path;
    }
}
