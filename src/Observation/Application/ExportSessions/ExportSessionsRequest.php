<?php

declare(strict_types=1);

namespace App\Observation\Application\ExportSessions;

final readonly class ExportSessionsRequest
{
    public function __construct(
        public string $format,
        public ?string $sessionId = null,
        public ?int $sessionType = null,
        public ?\DateTimeImmutable $dateFrom = null,
        public ?\DateTimeImmutable $dateTo = null,
        public ?bool $hasObservations = null,
        public ?string $censusId = null,
    ) {
    }
}
