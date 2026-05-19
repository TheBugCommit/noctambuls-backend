<?php

declare(strict_types=1);

namespace App\Observation\Application\UpdateObservation;

final readonly class UpdateObservationRequest
{
    public function __construct(
        public string $observationId,
        public string $speciesId,
        public int $contactType,
        public int $quantity,
        public ?string $notes,
        public float $bearing,
        public ?float $latitude = null,
        public ?float $longitude = null,
        public ?float $accuracy = null,
        public ?int $locationSource = null,
    ) {
    }
}
