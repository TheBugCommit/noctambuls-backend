<?php

declare(strict_types=1);

namespace App\Observation\Application\SyncUp\Dto;

readonly class ParticipantSyncDto
{
    public function __construct(
        public string $ornithologistCode
    ) {}

    public static function fromArray(array $data): self
    {
        if (!isset($data['ornithologistCode']) || !is_string($data['ornithologistCode'])) {
            throw new \InvalidArgumentException("Invalid or missing 'ornithologistCode' in participant payload.");
        }

        return new self(
            ornithologistCode: $data['ornithologistCode']
        );
    }
}
