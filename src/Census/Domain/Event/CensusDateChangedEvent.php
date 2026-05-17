<?php

declare(strict_types=1);

namespace App\Census\Domain\Event;

final readonly class CensusDateChangedEvent
{
    public function __construct(public string $censusId) {}
}
