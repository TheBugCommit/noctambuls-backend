<?php

declare(strict_types=1);

namespace App\Observation\Domain\Filter;

use App\Observation\Domain\ValueObject\SessionType;

final readonly class SessionFilter
{
    public function __construct(
        public ?string $sessionId = null,
        public ?SessionType $sessionType = null,
        public ?\DateTimeImmutable $dateFrom = null,
        public ?\DateTimeImmutable $dateTo = null,
        public ?bool $hasObservations = null,
        public ?string $censusId = null,
        public bool $isDeleted = false,
        public ?string $ornithologistCode = null,
        public ?int $listeningPoint = null,
        public ?string $censusTitle = null,
    ) {}

    public function isEmpty(): bool
    {
        return $this->sessionId === null
            && $this->sessionType === null
            && $this->dateFrom === null
            && $this->dateTo === null
            && $this->hasObservations === null
            && $this->censusId === null
            && $this->ornithologistCode === null
            && $this->listeningPoint === null
            && $this->censusTitle === null;
    }
}
