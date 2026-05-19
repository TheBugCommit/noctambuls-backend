<?php

declare(strict_types=1);

namespace App\Observation\Application\SoftDeleteObservation;

final readonly class SoftDeleteObservationRequest
{
    public function __construct(
        public string $observationId,
    ) {
    }
}
