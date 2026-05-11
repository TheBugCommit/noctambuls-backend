<?php

declare(strict_types=1);

namespace App\Observation\Application\SyncUp\Dto;

readonly class SessionSyncDto
{
    public function __construct(
        public string $id,
        public ?string $censusId,
        public int $sessionType,
        public ?string $startTime,
        public ?string $endTime,
        public int $temperature,
        public int $wind,
        public int $sky,
        public int $rain,
        public int $visibility,
        public int $moon,
        public ?int $listeningPoint,
        public ?float $latitude,
        public ?float $longitude,
        public ?float $accuracy,
        public ?float $altitude,
        public ?float $altitudeAccuracy,
        public ?int $locationSource,
        public string $createdAt,
        public string $updatedAt,
        /** @var ObservationSyncDto[] */
        public array $observations,
        /** @var ParticipantSyncDto[] */
        public array $participants,
    ) {}

    public static function fromArray(array $data): self
    {
        $observations = array_map(
            fn(array $obs) => ObservationSyncDto::fromArray($obs),
            $data['observations'] ?? []
        );

        $participants = array_map(
            fn(array $part) => ParticipantSyncDto::fromArray($part),
            $data['participants'] ?? []
        );

        return new self(
            id: $data['id'],
            censusId: $data['censusId'] ?? null,
            sessionType: (int) $data['sessionType'],
            startTime: $data['startTime'] ?? null,
            endTime: $data['endTime'] ?? null,
            temperature: (int) $data['temperature'],
            wind: (int) $data['wind'],
            sky: (int) $data['sky'],
            rain: (int) $data['rain'],
            visibility: (int) $data['visibility'],
            moon: (int) $data['moon'],
            listeningPoint: isset($data['listeningPoint']) ? (int) $data['listeningPoint'] : null,
            latitude: isset($data['latitude']) ? (float) $data['latitude'] : null,
            longitude: isset($data['longitude']) ? (float) $data['longitude'] : null,
            accuracy: isset($data['accuracy']) ? (float) $data['accuracy'] : null,
            altitude: isset($data['altitude']) ? (float) $data['altitude'] : null,
            altitudeAccuracy: isset($data['altitudeAccuracy']) ? (float) $data['altitudeAccuracy'] : null,
            locationSource: isset($data['locationSource']) ? (int) $data['locationSource'] : null,
            createdAt: $data['createdAt'],
            updatedAt: $data['updatedAt'],
            observations: $observations,
            participants: $participants
        );
    }
}
