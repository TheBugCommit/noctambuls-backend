<?php

declare(strict_types=1);

namespace App\Observation\Application\ExportSessions;

interface SessionFileExporterInterface
{
    public function export(ExportSessionsResponse $response): string;
}
