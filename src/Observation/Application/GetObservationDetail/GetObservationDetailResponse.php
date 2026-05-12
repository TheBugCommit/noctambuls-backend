<?php

declare(strict_types=1);

namespace App\Observation\Application\GetObservationDetail;

final readonly class GetObservationDetailResponse
{
    public function __construct(
        public array $observation,
        public array $birds,
    ) {
    }
}
