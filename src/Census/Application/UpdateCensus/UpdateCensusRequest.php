<?php

declare(strict_types=1);

namespace App\Census\Application\UpdateCensus;

readonly class UpdateCensusRequest
{
    public function __construct(
        public string $id,
        public string $title,
        public string $date,
        public string $time,
        public string $locationName,
        public ?string $description,
    ) {
    }
}
