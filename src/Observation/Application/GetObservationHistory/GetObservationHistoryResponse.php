<?php

declare(strict_types=1);

namespace App\Observation\Application\GetObservationHistory;

readonly class GetObservationHistoryResponse
{
    public function __construct(
        public array $sessions,
        public array $meta,
        public string $serverTime,
    ) {
    }
}
