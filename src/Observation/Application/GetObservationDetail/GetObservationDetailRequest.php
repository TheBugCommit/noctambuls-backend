<?php

declare(strict_types=1);

namespace App\Observation\Application\GetObservationDetail;

final readonly class GetObservationDetailRequest
{
    public function __construct(
        public string $observationId,
    ) {
    }
}
