<?php

declare(strict_types=1);

namespace App\Census\Application\GetCensus;

readonly class GetCensusResponse
{
    public function __construct(
        public string $id,
        public string $title,
        public string $date,
        public string $time,
        public float $latitude,
        public float $longitude,
        public string $locationName,
        public ?string $description,
        public string $status,
        public string $createdAt,
        public string $updatedAt,
        public ?string $publishedAt,
        public bool $isEditable,
        public string $deepLink,
        public string $qrBase64,
    ) {
    }
}
