<?php

declare(strict_types=1);

namespace App\Observation\Application\GetSessionsForBackoffice;

final readonly class GetSessionsForBackofficeRequest
{
    public function __construct(
        public int $page,
        public int $limit,
        public ?string $sessionId = null,
        public ?int $sessionType = null,
        public ?\DateTimeImmutable $dateFrom = null,
        public ?\DateTimeImmutable $dateTo = null,
        public ?bool $hasObservations = null,
        public ?string $censusId = null,
    ) {
    }
}
