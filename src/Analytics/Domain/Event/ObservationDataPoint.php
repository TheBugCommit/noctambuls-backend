<?php

declare(strict_types=1);

namespace App\Analytics\Domain\Event;

final readonly class ObservationDataPoint
{
    public function __construct(
        public string $speciesId,
        public float  $lat,
        public float  $lon,
        public int    $year,
        public int    $month,
        public int    $delta = 1,
    ) {}
}
