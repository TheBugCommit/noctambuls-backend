<?php

declare(strict_types=1);

namespace App\Census\Domain\Event;

final readonly class CensusCancelledEvent
{
    public function __construct(public string $censusId) {}
}
