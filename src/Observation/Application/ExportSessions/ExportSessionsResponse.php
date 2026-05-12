<?php

declare(strict_types=1);

namespace App\Observation\Application\ExportSessions;

final readonly class ExportSessionsResponse
{
    /**
     * @param string[] $headers
     * @param array<int, array<string, string|null>> $rows
     */
    public function __construct(
        public array $headers,
        public array $rows,
        public string $filename,
        public string $format,
    ) {
    }
}
