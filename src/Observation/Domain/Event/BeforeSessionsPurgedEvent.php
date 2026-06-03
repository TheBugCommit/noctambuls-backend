<?php

declare(strict_types=1);

namespace App\Observation\Domain\Event;

final class BeforeSessionsPurgedEvent
{
    public function __construct(
        public readonly ?\DateTimeImmutable $cutoff = null,
    ) {}
}
