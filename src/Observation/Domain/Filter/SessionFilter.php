<?php

declare(strict_types=1);

namespace App\Observation\Domain\Filter;

use App\Census\Domain\ValueObject\CensusId;
use App\Observation\Domain\ValueObject\SessionId;
use App\Observation\Domain\ValueObject\SessionType;

final readonly class SessionFilter
{
    public function __construct(
        public ?SessionId $sessionId = null,
        public ?SessionType $sessionType = null,
        public ?\DateTimeImmutable $dateFrom = null,
        public ?\DateTimeImmutable $dateTo = null,
        public ?bool $hasObservations = null,
        public ?CensusId $censusId = null,
        public bool $isDeleted = false,
    ) {}

    public function isEmpty(): bool
    {
        return $this->sessionId === null
            && $this->sessionType === null
            && $this->dateFrom === null
            && $this->dateTo === null
            && $this->hasObservations === null
            && $this->censusId === null;
    }
}
