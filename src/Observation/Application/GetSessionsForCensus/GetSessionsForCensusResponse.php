<?php

declare(strict_types=1);

namespace App\Observation\Application\GetSessionsForCensus;

final readonly class GetSessionsForCensusResponse
{
    /** @param array<int, array<string, mixed>> $sessions */
    public function __construct(
        public array $sessions,
        public int $total,
    ) {
    }
}
