<?php

declare(strict_types=1);

namespace App\Observation\Application\GetObservationHistory;

readonly class GetObservationHistoryRequest
{
    public function __construct(
        public string $annotatorId,
        public int $offset,
        public int $limit,
        public ?\DateTimeImmutable $since = null,
    ) {
    }
}
