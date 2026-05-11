<?php

declare(strict_types=1);

namespace App\Observation\Application\SyncUp\Dto;

readonly class ObservationSyncDto
{
    public function __construct(
        public string  $id,
        public string  $speciesId,
        public int     $contactType,
        public int     $quantity,
        public string  $startTime,
        public string  $endTime,
        public ?string $notes,
        public ?float  $latitude,
        public ?float  $longitude,
        public ?float  $accuracy,
        public ?float  $altitude,
        public ?float  $altitudeAccuracy,
        public ?int    $locationSource,
        public float   $bearing,
        public string  $createdAt,
        public string  $updatedAt,
    )
    {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            speciesId: $data['speciesId'],
            contactType: (int)$data['contactType'],
            quantity: max(1, (int)($data['quantity'] ?? 1)),
            startTime: $data['startTime'],
            endTime: $data['endTime'],
            notes: isset($data['notes']) && $data['notes'] !== '' ? (string)$data['notes'] : null,
            latitude: isset($data['latitude']) ? (float)$data['latitude'] : null,
            longitude: isset($data['longitude']) ? (float)$data['longitude'] : null,
            accuracy: isset($data['accuracy']) ? (float)$data['accuracy'] : null,
            altitude: isset($data['altitude']) ? (float)$data['altitude'] : null,
            altitudeAccuracy: isset($data['altitudeAccuracy']) ? (float)$data['altitudeAccuracy'] : null,
            locationSource: isset($data['locationSource']) ? (int)$data['locationSource'] : null,
            bearing: isset($data['bearing']) ? (float)$data['bearing'] : 0.0,
            createdAt: $data['createdAt'],
            updatedAt: $data['updatedAt'],
        );
    }
}
