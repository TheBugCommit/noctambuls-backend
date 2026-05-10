<?php

declare(strict_types=1);

namespace App\Census\Application\CreateCensus;

readonly class CreateCensusRequest
{
    public function __construct(
        public string $title,
        public string $date,
        public string $time,
        public string $locationName,
        public ?string $description,
    ) {
    }
}
