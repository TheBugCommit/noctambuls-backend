<?php

declare(strict_types=1);

namespace App\Observation\Application\GetParticipatedSessions;

readonly class GetParticipatedSessionsRequest
{
    public function __construct(
        public string $ornithologistCode,
        public int $offset,
        public int $limit,
        public ?\DateTimeImmutable $since = null,
    ) {
    }
}
