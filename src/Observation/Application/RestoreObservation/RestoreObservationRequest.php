<?php

declare(strict_types=1);

namespace App\Observation\Application\RestoreObservation;

final readonly class RestoreObservationRequest
{
    public function __construct(
        public string $observationId,
    ) {
    }
}
